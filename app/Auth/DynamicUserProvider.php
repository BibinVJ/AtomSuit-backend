<?php

namespace App\Auth;

use App\Models\CentralUser;
use App\Models\Passport\ContextAwareToken;
use App\Models\User;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Support\Arrayable;

class DynamicUserProvider extends EloquentUserProvider
{
    /**
     * Get the user model class based on current context
     */
    protected function getUserModelClass(): string
    {
        return tenant() ? User::class : CentralUser::class;
    }

    /**
     * Create a new instance of the model.
     */
    public function createModel()
    {
        $class = $this->getUserModelClass();

        return new $class;
    }

    /**
     * Retrieve a user by their unique identifier.
     */
    public function retrieveById($identifier)
    {
        // Check if this is called during token authentication
        $tokenContext = $this->getTokenContextFromRequest();

        if ($tokenContext) {
            // Use the token's original context to determine the model
            $model = $this->createModelFromTokenContext($tokenContext);
        } else {
            // Fallback to current context (for regular authentication)
            $model = $this->createModel();
        }

        $user = $this->newModelQuery($model)
            ->where($model->getAuthIdentifierName(), $identifier)
            ->first();

        // Additional validation: ensure the returned user matches the expected context
        if ($user && $tokenContext) {
            $expectedModelClass = $tokenContext === 'central' ? CentralUser::class : User::class;
            if (! is_a($user, $expectedModelClass)) {
                return null; // Block authentication
            }

            // Additional check: validate the user actually belongs to the right context
            if ($tokenContext !== 'central' && ! str_starts_with($tokenContext, 'tenant:')) {
                return null;
            }

            // Extra security: If current context doesn't match token context, block access
            $currentContext = tenant() ? 'tenant:'.tenant()->id : 'central';
            if ($currentContext !== $tokenContext) {
                return null; // Block authentication at provider level
            }
        }

        return $user;
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     */
    public function retrieveByToken($identifier, $token)
    {
        $model = $this->createModel();
        $retrievedModel = $this->newModelQuery($model)
            ->where($model->getAuthIdentifierName(), $identifier)
            ->first();

        if (! $retrievedModel) {
            return null;
        }

        $rememberToken = $retrievedModel->getRememberToken();

        return $rememberToken && hash_equals($rememberToken, $token) ? $retrievedModel : null;
    }

    /**
     * Update the "remember me" token for the given user in storage.
     */
    public function updateRememberToken(Authenticatable $user, $token)
    {
        $user->setRememberToken($token);
        $timestamps = $user->timestamps;
        $user->timestamps = false;
        $user->save();
        $user->timestamps = $timestamps;
    }

    /**
     * Retrieve a user by the given credentials.
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (empty($credentials) ||
            (count($credentials) === 1 &&
             str_contains(array_keys($credentials)[0], 'password'))) {
            return null;
        }

        $model = $this->createModel();
        $query = $this->newModelQuery($model);

        foreach ($credentials as $key => $value) {
            if (str_contains($key, 'password')) {
                continue;
            }

            if (is_array($value) || $value instanceof Arrayable) {
                $query->whereIn($key, $value);
            } else {
                $query->where($key, $value);
            }
        }

        return $query->first();
    }

    /**
     * Get a new query builder for the model instance.
     */
    protected function newModelQuery($model = null)
    {
        $model = $model ?: $this->createModel();

        return $model->newQuery();
    }

    /**
     * Get token context from the current request if it's a token-based authentication
     */
    protected function getTokenContextFromRequest(): ?string
    {
        try {
            $request = request();
            if (! $request) {
                return null;
            }

            $tokenValue = $request->bearerToken();
            if (! $tokenValue) {
                return null;
            }

            // Parse the JWT to get token ID
            $tokenId = $this->getTokenIdFromJWT($tokenValue);
            if (! $tokenId) {
                return null;
            }

            $token = ContextAwareToken::find($tokenId);
            if (! $token) {
                return null;
            }

            return $this->extractContextFromTokenName($token->name);
        } catch (\Exception $e) {

            return null;
        }
    }

    /**
     * Extract token ID from JWT
     */
    protected function getTokenIdFromJWT(string $tokenValue): ?string
    {
        try {
            // JWT tokens have 3 parts separated by dots
            $parts = explode('.', $tokenValue);
            if (count($parts) !== 3) {
                return null;
            }

            // Decode the payload (second part)
            $payload = json_decode(base64_decode($parts[1]), true);

            return $payload['jti'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Extract context information from token scopes
     */
    protected function extractContextFromTokenName(string $tokenName): string
    {
        // Enhanced token name format: "PAT-central-1-1234567890" or "PAT-tenant:uuid-1-1234567890"
        if (preg_match('/^PAT-(.+?)-\d+-\d+$/', $tokenName, $matches)) {
            return $matches[1];
        }

        // Fallback for simpler format: "PAT-central" or "PAT-tenant:uuid"
        if (preg_match('/^PAT-(.+)$/', $tokenName, $matches)) {
            return $matches[1];
        }

        return 'unknown';
    }

    /**
     * Create model instance based on token context
     */
    protected function createModelFromTokenContext(string $tokenContext)
    {
        if ($tokenContext === 'central') {
            return new CentralUser;
        } elseif (str_starts_with($tokenContext, 'tenant:')) {
            return new User;
        }

        // Fallback to current context
        return $this->createModel();
    }
}
