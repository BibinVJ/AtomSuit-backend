<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Contracts\TenantCouldNotBeIdentifiedException;
use Stancl\Tenancy\Middleware\IdentificationMiddleware;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;
use Stancl\Tenancy\Tenancy;

class InitializeTenancyBySubdomainHeader extends IdentificationMiddleware
{
    /** @var string */
    public static string $header = 'X-Tenant';

    /** @var callable|null */
    public static $onFail;

    /** @var Tenancy */
    protected $tenancy;   // 🚀 removed type hints

    /** @var DomainTenantResolver */
    protected $resolver;  // 🚀 removed type hints

    public function __construct(Tenancy $tenancy, DomainTenantResolver $resolver)
    {
        $this->tenancy = $tenancy;
        $this->resolver = $resolver;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->method() === 'OPTIONS') {
            return $next($request);
        }

        $subdomain = $request->header(static::$header);

        // Case 1: No X-Tenant → treat as central request
        if (! $subdomain) {
            return tenancy()->central(function () use ($request, $next) {
                return $next($request);
            });
        }

        // Case 2: Has X-Tenant → resolve tenant
        $fullDomain = $subdomain . '.' . config('tenancy.base_domain');

        try {
            return $this->initializeTenancy($request, $next, $fullDomain);
        } catch (TenantCouldNotBeIdentifiedException $e) {
            // 🔹 Case 3: Wrong tenant value
            $onFail = static::$onFail ?? function ($e) {
                throw $e;
            };

            return $onFail($e, $request, $next);
        }
    }
}
