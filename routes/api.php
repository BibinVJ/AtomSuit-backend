<?php

use App\Helpers\ApiResponse;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Universal API Routes
|--------------------------------------------------------------------------
|
| These routes work for both central and tenant contexts automatically.
| The tenancy middleware runs globally, and the dynamic auth provider
| handles switching between central and tenant user models.
|
*/

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    $context = tenant() ? 'Tenant: ' . tenant()->name : 'Central';
    return ApiResponse::success('API ping successful - ' . config('app.name') . ' (' . $context . ')');
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

/*
|--------------------------------------------------------------------------
| Debug Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:api', 'validate.token.context'])->get('/debug-auth', function () {
    return ApiResponse::success('Auth Debug', [
        'context' => tenant() ? 'tenant' : 'central',
        'tenant' => tenant() ? tenant()->id : null,
        'auth_guard' => config('auth.defaults.guard'),
        'auth_user' => Auth::user() ? Auth::user()->toArray() : null,
        'auth_user_type' => Auth::user() ? get_class(Auth::user()) : null,
        'db_connection' => config('database.default'),
    ]);
});

/*
|--------------------------------------------------------------------------
| Protected Routes (Universal)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:api', 'validate.token.context'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    // Universal profile route - works for both central and tenant
    Route::get('profile', [UserProfileController::class, 'show']);
    Route::post('profile', [UserProfileController::class, 'update']);

    // Central-only routes (will only work when no X-Tenant header is present)
    // Route::middleware('central.only')->group(function () {
    Route::get('tenant-stats', [TenantController::class, 'stats']);
    Route::post('tenant/{tenant}/send-mail', [TenantController::class, 'sendMail']);
    Route::apiResource('tenant', TenantController::class);
    // });
});

/*
|--------------------------------------------------------------------------
| Webhook Callback Routes
|--------------------------------------------------------------------------
*/
Route::prefix('webhook')->middleware('log.webhook')->group(function () {
    Route::get('/', fn() => response()->json(['message' => 'Central webhook ping successful!']));
});
