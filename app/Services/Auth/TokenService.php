<?php

namespace App\Services\Auth;

use App\Models\User;
use Laravel\Passport\Token;
use Laravel\Passport\PersonalAccessTokenResult;

class TokenService
{
    public function create(User $user): PersonalAccessTokenResult
    {
        return $user->createToken('Personal Access Token');
    }

    public function revokeCurrent(User $user): void
    {
        $token = $user->token();
        $token?->revoke();
        $token?->refreshToken?->revoke();
    }

    public function revokeAll(User $user): void
    {
        $user->tokens()->each(function (Token $token) {
            $token->revoke();
            $token->refreshToken?->revoke();
        });
    }
}
