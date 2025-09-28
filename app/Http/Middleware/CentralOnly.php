<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;

class CentralOnly
{
    /**
     * Handle an incoming request.
     * Ensure the request is only processed in central context (no tenant).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if we're in a tenant context
        if (tenant()) {
            \Log::warning('Attempted to access central-only route from tenant context', [
                'url' => $request->url(),
                'method' => $request->method(),
                'tenant_id' => tenant()->id,
                'tenant_name' => tenant()->name,
                'user_id' => auth()->id(),
            ]);

            return ApiResponse::error(
                'Access denied',
                [
                    'message' => 'This endpoint is only available in central context',
                    'current_context' => 'tenant',
                    'required_context' => 'central'
                ],
                403
            );
        }

        return $next($request);
    }
}