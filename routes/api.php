<?php

use App\Http\Controllers\Auth\CentralAuthController;
use App\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Central Domain API Routes
|--------------------------------------------------------------------------
|
| These routes are for the central application and tenant management.
| They are only accessible from central domains specified in config.
|
*/

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Public Central Routes
        |--------------------------------------------------------------------------
        */

        Route::get('/', function () {
            return response()->json([
                'message' => 'Central App - Multi-tenant Backend API',
                'version' => '1.0.0',
                'tenancy' => true,
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | Central Authentication Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('admin/auth')->group(function () {
            Route::post('login', [CentralAuthController::class, 'login']);
            Route::post('register', [CentralAuthController::class, 'register']);
        });

        /*
        |--------------------------------------------------------------------------
        | Central Protected Routes
        |--------------------------------------------------------------------------
        */
        Route::middleware(['auth:central'])->prefix('admin')->group(function () {
            Route::post('auth/logout', [CentralAuthController::class, 'logout']);
            Route::get('profile', [CentralAuthController::class, 'profile']);

            // Tenant Management Routes
            Route::prefix('tenants')->group(function () {
                Route::get('/', [TenantController::class, 'index']);
                Route::post('/', [TenantController::class, 'store']);
                Route::get('{tenant}', [TenantController::class, 'show']);
                Route::put('{tenant}', [TenantController::class, 'update']);
                Route::delete('{tenant}', [TenantController::class, 'destroy']);
            });
        });

        /*
        |--------------------------------------------------------------------------
        | Webhook Callback Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('callback')->middleware('log.webhook')->group(function () {
            Route::get('/', fn() => response()->json(['message' => 'Central webhook ping successful!']));
        });
    });
}
