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
        $agent = $this->parseUserAgent(Request::userAgent());

        $loginDetail = UserLoginDetail::create([
            'user_id' => $userId,
            'token_id' => $tokenId,
            'login_at' => now(),
            'logout_at' => null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'login_method' => $method,
            'os' => $agent['os'],
            'browser' => $agent['browser'],
            'device_type' => $agent['device_type'],
        ]);

        \App\Jobs\UpdateLoginLocation::dispatch($loginDetail);
    }

    /**
     * Parse User Agent string.
     */
    protected function parseUserAgent(?string $userAgent): array
    {
        $os = 'Unknown';
        $browser = 'Unknown';
        $deviceType = 'desktop';

        if (! $userAgent) {
            return compact('os', 'browser', 'deviceType');
        }

        // OS Detection
        if (preg_match('/windows/i', $userAgent)) {
            $os = 'Windows';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            $os = 'macOS';
        } elseif (preg_match('/linux/i', $userAgent)) {
            $os = 'Linux';
        } elseif (preg_match('/android/i', $userAgent)) {
            $os = 'Android';
            $deviceType = 'mobile';
        } elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) {
            $os = 'iOS';
            $deviceType = 'mobile';
        }

        // Browser Detection
        if (preg_match('/MSIE/i', $userAgent) && ! preg_match('/Opera/i', $userAgent)) {
            $browser = 'Internet Explorer';
        } elseif (preg_match('/Firefox/i', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Chrome/i', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Safari/i', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/Opera/i', $userAgent)) {
            $browser = 'Opera';
        } elseif (preg_match('/Edge/i', $userAgent)) {
            $browser = 'Edge';
        }

        // Refine Device Type
        if (preg_match('/tablet|ipad|playbook/i', $userAgent)) {
            $deviceType = 'tablet';
        } elseif (preg_match('/mobile|android|iphone|ipod/i', $userAgent)) {
            $deviceType = 'mobile';
        }

        return [
            'os' => $os,
            'browser' => $browser,
            'device_type' => $deviceType,
        ];
    }
}
