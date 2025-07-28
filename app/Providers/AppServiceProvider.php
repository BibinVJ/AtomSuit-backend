<?php

namespace App\Providers;

use App\Enums\RolesEnum;
use App\Models\UserLoginDetail;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Events\AccessTokenCreated;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            return $user->hasRole(RolesEnum::SUPER_ADMIN->value) ? true : null;
        });

        Passport::tokensExpireIn(CarbonInterval::days(15));
        Passport::refreshTokensExpireIn(CarbonInterval::days(30));

        Event::listen(AccessTokenCreated::class, function ($event) {
            if ($event->userId) {
                $ip = Request::ip(); // gets the real client IP

                UserLoginDetail::create([
                    'user_id' => $event->userId,
                    'token_id' => $event->tokenId, // Store token ID for logout tracking
                    'login_at' => now(),
                    'logout_at' => null, // initially null, will be updated on logout
                    'ip_address' => $ip,
                    'user_agent' => Request::userAgent(),
                    'login_method' => 'oauth', // or determine based on grant type
                ]);
            }
        });
    }
}
