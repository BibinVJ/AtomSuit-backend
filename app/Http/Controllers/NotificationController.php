<?php

namespace App\Http\Controllers;

use App\Enums\PermissionsEnum;
use App\Helpers\ApiResponse;
use App\Http\Resources\NotificationResource;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {
        $this->middleware("permission:" . PermissionsEnum::MANAGE_NOTIFICATIONS->value)->only(['index', 'unread', 'markAsRead']);
    }

    public function index(Request $request)
    {
        $notifications = $this->notificationService->getAll();
        \Log::info(json_encode($notifications));
        return ApiResponse::success('Notifications fetched', NotificationResource::collection($notifications));
    }

    public function unread()
    {
        $result = $this->notificationService->getUnread();
        return ApiResponse::success('Unread notifications fetched', [
            'unread_count' => $result['count'],
            'notifications' => NotificationResource::collection($result['notifications']),
        ]);
    }

    public function markAsRead($id = null)
    {
        $this->notificationService->markAsRead($id);
        return ApiResponse::success('Notification(s) marked as read.');
    }
}
