<?php

namespace App\Providers;

use App\Models\UserLoginDetail;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Request;
use Laravel\Passport\Events\AccessTokenCreated;
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

        // Configure Passport
        Passport::tokensCan([
            'tenant-access' => 'Access tenant data',
            'central-access' => 'Access central application',
        ]);

        Passport::setDefaultScope(['tenant-access']);

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