<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\CentralUser;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\PersonalAccessTokenResult;
use Laravel\Passport\Token;

class TokenService
{
    public function create(User|CentralUser $user): PersonalAccessTokenResult
    {
        // Create token without scopes for now to avoid scope validation issues
        return $user->createToken('Personal Access Token');
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
