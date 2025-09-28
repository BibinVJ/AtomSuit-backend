<?php

namespace App\Providers;

use App\Auth\DynamicUserProvider;
use App\Models\User;
use App\Models\UserLoginDetail;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Request;
use Laravel\Passport\Events\AccessTokenCreated;
use Laravel\Passport\Passport;
use App\Models\Passport\ContextAwareToken;
use App\Auth\ContextAwareTokenGuard;
use Laravel\Passport\TokenRepository;
use League\OAuth2\Server\ResourceServer;
use Illuminate\Container\Container;

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
        \Log::info('Registering dynamic user provider');
        Auth::provider('dynamic', function ($app, array $config) {
            \Log::info('Dynamic user provider called');
            return new DynamicUserProvider($app['hash'], User::class);
        });

        // Use context-aware token model
        Passport::useTokenModel(ContextAwareToken::class);

        // Register context-aware passport guard
        Auth::extend('context-passport', function ($app, $name, array $config) {
            return new ContextAwareTokenGuard(
                $app->make(ResourceServer::class),
                Auth::createUserProvider($config['provider']),
                $app->make(TokenRepository::class),
                $app->make(Container::class),
                $app->make('request')
            );
        });

        // Token lifetime configuration
        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));

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