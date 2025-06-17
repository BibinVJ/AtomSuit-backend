<?php

namespace App\DataTransferObjects;

use App\Models\User;
use Laravel\Passport\PersonalAccessTokenResult;

readonly class AuthenticatedUserDTO
{
    public function __construct(
        public User $user,
        public PersonalAccessTokenResult $authToken
    ) {}
}