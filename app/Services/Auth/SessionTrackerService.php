<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\UserLoginDetail;

class SessionTrackerService
{
    public function markAllSessionsLoggedOut(User $user): void
    {
        UserLoginDetail::where('user_id', $user->id)
            ->whereNull('logout_at')
            ->update(['logout_at' => now()]);
    }

    public function markSessionLoggedOutByToken(string $tokenId): void
    {
        UserLoginDetail::where('token_id', $tokenId)
            ->whereNull('logout_at')
            ->update(['logout_at' => now()]);
    }
}
