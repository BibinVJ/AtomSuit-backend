<?php

namespace App\Http\Middleware;

use App\Enums\RolesEnum;
use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyAdminTenantContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only proceed if authenticated via web and has super-admin role
        if (auth()->guard('web')->check() && auth()->guard('web')->user()->hasRole(RolesEnum::SUPER_ADMIN->value)) {
            $tenantId = session('admin_tenant_id');

            if ($tenantId && !tenancy()->initialized) {
                $tenant = Tenant::find($tenantId);
                if ($tenant) {
                    \Illuminate\Support\Facades\Log::info('Initializing Tenant Context from Session', ['tenant_id' => $tenantId]);
                    tenancy()->initialize($tenant);
                }
            }
        }

        return $next($request);
    }
}
