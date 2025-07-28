<?php

namespace App\Helpers;

use App\DataTransferObjects\AuthenticatedUserDTO;
use App\Http\Resources\AuthUserResource;
use App\Http\Resources\UserResource;

class AuthResponseFormatter
{
    public static function format(AuthenticatedUserDTO $dto): array
    {
        return [
            'token' => [
                'access_token' => $dto->authToken->accessToken,
                'expires_in'   => $dto->authToken->token->expires_at->diffInSeconds(now()),
            ],
            'user' => new AuthUserResource($dto->user),
        ];
    }
}
