<?php

namespace App\Listeners;

use App\Models\UserLoginDetail;
use Illuminate\Support\Facades\Request;
use Laravel\Passport\Events\AccessTokenCreated;

class LogUserLogin
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        if ($event instanceof AccessTokenCreated) {
            $this->handlePassportLogin($event);
        } elseif ($event instanceof \Illuminate\Auth\Events\Login) {
            $this->handleWebLogin($event);
        }
    }

    /**
     * Handle Passport login event.
     */
    protected function handlePassportLogin(AccessTokenCreated $event): void
    {
        if ($event->userId) {
            $this->logLogin(
                $event->userId,
                $event->tokenId,
                'oauth'
            );
        }
    }

    /**
     * Handle standard web login event.
     */
    protected function handleWebLogin(\Illuminate\Auth\Events\Login $event): void
    {
        $this->logLogin(
            $event->user->getAuthIdentifier(),
            session()->getId(),
            'web'
        );
    }

    /**
     * Shared login logging logic.
     */
    protected function logLogin($userId, $tokenId, $method): void
    {
        UserLoginDetail::create([
            'user_id' => $userId,
            'token_id' => $tokenId,
            'login_at' => now(),
            'logout_at' => null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'login_method' => $method,
        ]);
    }
}
