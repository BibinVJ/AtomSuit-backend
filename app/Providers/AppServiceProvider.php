<?php

namespace App\Providers;

use App\Models\Purchase;
use App\Models\Sale;
use App\Models\User;
use App\Observers\PurchaseObserver;
use App\Observers\SaleObserver;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;

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

        \Illuminate\Support\Facades\Event::listen(
            \Laravel\Passport\Events\AccessTokenCreated::class,
            \App\Listeners\LogUserLogin::class
        );

        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Login::class,
            \App\Listeners\LogUserLogin::class
        );
    }
}
