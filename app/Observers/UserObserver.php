<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    public function creating(User $user)
    {
        if ($user->status) {
            $user->status_updated_at = now();
        }
    }

    public function updating(User $user)
    {
        if ($user->isDirty('status')) {
            $user->status_updated_at = now();
        }
    }
}
