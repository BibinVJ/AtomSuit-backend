<?php

namespace App\Providers;

use App\Listeners\LogUserLogin;
use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Events\AccessTokenCreated;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(UserObserver::class);

        Event::listen(AccessTokenCreated::class, LogUserLogin::class);

        Event::listen(Login::class, LogUserLogin::class);
    }
}
