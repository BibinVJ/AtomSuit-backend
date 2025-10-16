<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\CentralUser;
use Laravel\Passport\PersonalAccessTokenResult;
use Laravel\Passport\Token;

class TokenService
{
    public function create(User|CentralUser $user): PersonalAccessTokenResult
    {
        // Revoke existing tokens for this user in this context
        $context = tenant() ? 'tenant:' . tenant()->id : 'central';
        
        // Create token with context information in the name
        $tokenName = 'PAT-' . $context . '-' . $user->id . '-' . time();
        
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
}
