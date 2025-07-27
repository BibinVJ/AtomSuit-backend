<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class NotificationService
{
    public function getAll(int $limit = 15)
    {
        return Auth::user()
            ->notifications()
            ->latest()
            ->take($limit)
            ->get();
    }

    public function getUnread(): array
    {
        $user = Auth::user();

        return [
            'count' => $user->unreadNotifications->count(),
            'notifications' => $user->unreadNotifications,
        ];
    }

    public function markAsRead(?string $id = null): void
    {
        $user = Auth::user();

        if ($id) {
            $notification = $user->notifications()->where('id', $id)->firstOrFail();
            $notification->markAsRead();
        } else {
            $user->unreadNotifications->markAsRead();
        }
    }
}
