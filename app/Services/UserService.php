<?php

namespace App\Services;

use App\Models\User;
use Exception;

class UserService
{
    public function ensureUserIsDeletable(User $user)
    {
        // if ($user->items()->exists()) {
        //     throw new Exception('Unit is assigned to items and cannot be deleted.');
        // }
    }
}
