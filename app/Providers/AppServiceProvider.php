<?php

namespace App\Providers;

use App\Models\User;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\Event;
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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Passport::tokensExpireIn(CarbonInterval::days(15));
        Passport::refreshTokensExpireIn(CarbonInterval::days(30));

        Event::listen(AccessTokenCreated::class, function ($event) {
            if ($event->userId) {
                $ip = Request::ip(); // gets the real client IP

                User::where('id', $event->userId)->update([
                    'last_login_at' => now(),
                    'last_login_ip' => $ip,
                ]);
            }
        });
    }
}
