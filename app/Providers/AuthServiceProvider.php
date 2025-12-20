<?php

namespace App\Providers;

use App\Auth\DynamicUserProvider;
use App\Models\Passport\ContextAwareToken;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Register dynamic user provider
        Auth::provider('dynamic', function ($app, array $config) {
            return new DynamicUserProvider($app['hash'], User::class);
        });

        // // Use context-aware token model
        Passport::useTokenModel(ContextAwareToken::class);

        // Token lifetime configuration
        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));

    }
}
