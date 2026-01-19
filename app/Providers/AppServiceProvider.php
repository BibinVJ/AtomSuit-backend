<?php

namespace App\Providers;

use App\Listeners\LogUserLogin;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\User;
use App\Observers\PurchaseObserver;
use App\Observers\SaleObserver;
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
        Sale::observe(SaleObserver::class);
        Purchase::observe(PurchaseObserver::class);

        Event::listen(AccessTokenCreated::class, LogUserLogin::class);

        Event::listen(Login::class, LogUserLogin::class);
    }
}
