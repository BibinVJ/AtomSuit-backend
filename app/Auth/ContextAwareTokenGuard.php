<?php

namespace App\Auth;

use Laravel\Passport\TokenRepository;
use Laravel\Passport\Guards\TokenGuard;
use League\OAuth2\Server\ResourceServer;
use Illuminate\Http\Request;
use Illuminate\Container\Container;
use App\Models\User;
use App\Models\CentralUser;
use App\Models\Passport\ContextAwareToken;

class ContextAwareTokenGuard extends TokenGuard
{
    public function __construct(ResourceServer $resourceServer, $userProvider, TokenRepository $tokens, Container $container, Request $request)
    {
        parent::__construct($resourceServer, $userProvider, $tokens, $container, $request);
    }

    /**
     * Get the user for the incoming request.
     *
     * @return mixed
     */
    public function user(): ?\Illuminate\Contracts\Auth\Authenticatable
    {
        $request = $this->request;

        if ($this->user) {
            return $this->user;
        }

        // Get the token first
        $psr = $this->getPsrRequestViaBearerToken($request);
        if (! $psr) {
            return null;
        }

        // Validate the token
        try {
            $psr = $this->resourceServer->validateAuthenticatedRequest($psr);
        } catch (\Exception $e) {
            return null;
        }

        // Get the token info using context-aware token model
        $tokenId = $psr->getAttribute('oauth_access_token_id');
        $token = ContextAwareToken::find($tokenId);

        if (! $token) {
            return null;
        }

        // Validate token context based on token name
        if (! $this->validateTokenContext($token)) {
            \Log::warning('Token context validation failed', [
                'token_id' => $tokenId,
                'token_name' => $token->name,
                'current_context' => tenant() ? 'tenant:' . tenant()->id : 'central'
            ]);
            return null;
        }

        // Get the user
        $userId = $psr->getAttribute('oauth_user_id');
        
        // Determine the correct model class from token context
        $userModel = $this->getUserModelFromTokenContext($token);
        
        if ($userModel) {
            $this->user = $userModel->find($userId);
        }

        return $this->user;
    }

    /**
     * Validate that the token can be used in the current context
     */
    protected function validateTokenContext($token): bool
    {
        $currentContext = tenant() ? 'tenant:' . tenant()->id : 'central';
        $tokenContext = $this->extractContextFromTokenName($token->name);
        
        return $currentContext === $tokenContext;
    }

    /**
     * Extract context information from token name
     */
    protected function extractContextFromTokenName(string $tokenName): string
    {
        // Token name format: "PAT-central" or "PAT-tenant:1" 
        if (preg_match('/^PAT-(.+)$/', $tokenName, $matches)) {
            return $matches[1];
        }
        
        // Fallback for tokens without context info (legacy tokens)
        return 'unknown';
    }

    /**
     * Get the appropriate user model based on token context
     */
    protected function getUserModelFromTokenContext($token)
    {
        $tokenContext = $this->extractContextFromTokenName($token->name);
        
        if (str_starts_with($tokenContext, 'tenant:')) {
            return new User();
        } elseif ($tokenContext === 'central') {
            return new CentralUser();
        }
        
        return null;
    }
}