<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class NotificationService
{
    public function getAll(int $limit = 15)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return $user
            ->notifications()
            ->latest()
            ->take($limit)
            ->get();
    }

    public function getUnread(): array
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return [
            'count' => $user->unreadNotifications->count(),
            'notifications' => $user->unreadNotifications,
        ];
    }

    public function markAsRead(?string $id = null): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($id) {
            $notification = $user->notifications()->where('id', $id)->firstOrFail();
            $notification->markAsRead();
        } else {
            $user->unreadNotifications->markAsRead();
        }
    }
}
