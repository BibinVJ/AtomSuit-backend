<?php

use App\Helpers\ApiResponse;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Auth;
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
| Debug Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:central'])->get('/debug-central-auth', function () {
    return ApiResponse::success('Tenant Auth Debug', [
        'tenant' => tenant() ? tenant()->id : null,
        'auth_guard' => config('auth.defaults.guard'),
        'auth_user' => Auth::user() ? Auth::user()->toArray() : null,
        'auth_user_type' => Auth::user() ? get_class(Auth::user()) : null,
        'db_connection' => config('database.default'),
    ]);
});

/*
|--------------------------------------------------------------------------
| Central Protected Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:central'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('central-profile', [UserProfileController::class, 'show']);

    Route::prefix('profile')->group(function () {
        Route::get('/', [UserProfileController::class, 'show']);
        Route::post('/', [UserProfileController::class, 'update']);
    });

    // Tenant Management
    Route::get('tenant-stats', [TenantController::class, 'stats']);
    Route::post('tenant/{tenant}/send-mail', [TenantController::class, 'sendMail']);
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
