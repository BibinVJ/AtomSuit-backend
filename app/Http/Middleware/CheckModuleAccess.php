<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use App\Services\ModuleManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleAccess
{
    public function __construct(protected ModuleManager $moduleManager) {}

    /**
     * Handle an incoming request.
     * Usage: Route::middleware('module:restaurant')
     */
    public function handle(Request $request, Closure $next, string $moduleSlug): Response
    {
        // Skip OPTIONS (preflight) requests
        if ($request->method() === 'OPTIONS') {
            return $next($request);
        }

        // In central context (no tenant), skip module check
        $tenant = tenant();
        if (! $tenant) {
            return $next($request);
        }

        // Check if module is enabled for this tenant
        if (! $this->moduleManager->isEnabled($tenant, $moduleSlug)) {
            return ApiResponse::error(
                'This module is not available on your current plan.',
                Response::HTTP_FORBIDDEN
            );
        }

        return $next($request);
    }
}
