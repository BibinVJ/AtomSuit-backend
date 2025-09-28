<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\CentralUser;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\PersonalAccessTokenResult;
use Laravel\Passport\Token;
use App\Models\Passport\ContextAwareToken;

class TokenService
{
    public function create(User|CentralUser $user): PersonalAccessTokenResult
    {
        // Revoke existing tokens for this user in this context
        $context = tenant() ? 'tenant:' . tenant()->id : 'central';
        $this->revokeContextTokens($user, $context);
        
        // Create token with context information in the name
        $tokenName = 'PAT-' . $context . '-' . $user->id . '-' . time();
        
        \Log::info('Creating token with context name (old context tokens revoked)', [
            'user_id' => $user->id,
            'user_class' => get_class($user),
            'context' => $context,
            'token_name' => $tokenName
        ]);
        
        return $user->createToken($tokenName);
    }

    public function revokeCurrent(User|CentralUser $user): void
    {
        $token = $user->token();
        $token?->revoke();
        $token?->refreshToken?->revoke();
    }

    public function revokeAll(User|CentralUser $user): void
    {
        $user->tokens()->each(function (Token $token) {
            $token->revoke();
            $token->refreshToken?->revoke();
        });
    }

    public function revokeContextTokens(User|CentralUser $user, string $context): void
    {
        // Use context-aware token model to query the right database
        $tokens = ContextAwareToken::forCurrentContext()
            ->where('user_id', $user->id)
            ->where('revoked', false)
            ->get();
            
        foreach ($tokens as $token) {
            // Check if token belongs to the same context
            if (str_contains($token->name, 'PAT-' . $context . '-')) {
                \Log::info('Revoking old token in same context', [
                    'token_name' => $token->name,
                    'context' => $context
                ]);
                $token->revoked = true;
                $token->save();
            }
        }
    }
}
