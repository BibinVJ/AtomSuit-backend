<?php

use App\Helpers\ApiResponse;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\UserProfileController;
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
            return ApiResponse::success('API ping successful - ' . config('app.name'));
        });

        /*
        |--------------------------------------------------------------------------
        | Central Authentication Routes
        |--------------------------------------------------------------------------
        */
        Route::post('login', [AuthController::class, 'login']);
        Route::post('register', [AuthController::class, 'register']);

        /*
        |--------------------------------------------------------------------------
        | Central Protected Routes
        |--------------------------------------------------------------------------
        */
        Route::middleware(['auth:central'])->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);

            Route::prefix('profile')->group(function () {
                Route::get('/', [UserProfileController::class, 'show']);
            });

            // Tenant Management
            Route::get('tenant-stats', [TenantController::class, 'stats']);
            Route::post('user/{user}/send-mail', [TenantController::class, 'sendMail']);
            Route::apiResource('tenant', TenantController::class);
        });

        /*
        |--------------------------------------------------------------------------
        | Webhook Callback Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('webhook')->middleware('log.webhook')->group(function () {
            Route::get('/', fn() => response()->json(['message' => 'Central webhook ping successful!']));
        });
    });
}
