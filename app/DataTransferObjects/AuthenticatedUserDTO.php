<?php

namespace App\DataTransferObjects;

use App\Models\User;
use App\Models\CentralUser;
use Laravel\Passport\PersonalAccessTokenResult;

readonly class AuthenticatedUserDTO
{
    public function __construct(
        public User|CentralUser $user,
        public PersonalAccessTokenResult $authToken
    ) {}
}
