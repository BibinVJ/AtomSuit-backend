<?php

use App\Helpers\ApiResponse;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\UserProfileController;
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

Route::get('plan', [PlanController::class, 'index']);
Route::post('enquiry', [EnquiryController::class, 'store']);

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

/*
|--------------------------------------------------------------------------
| Authenticated Routes (Universal)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:api'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('profile', [UserProfileController::class, 'show']);
    Route::post('profile', [UserProfileController::class, 'update']);

    Route::prefix('plan')->group(function () {
        Route::get('/{plan}', [PlanController::class, 'show']);
        Route::post('/', [PlanController::class, 'store']);
        Route::post('/{plan}', [PlanController::class, 'update']);
        Route::delete('/{plan}', [PlanController::class, 'destroy']);
    });

    Route::get('tenant-stats', [TenantController::class, 'stats']);
    Route::post('tenant/{tenant}/send-mail', [TenantController::class, 'sendMail']);
    Route::apiResource('tenant', TenantController::class);

    Route::apiResource('subscription', SubscriptionController::class);

    // domains

    /* User Management */
    // Route::post('user/{user}/send-mail', [UserController::class, 'sendMail']);
    // Route::apiResource('user', UserController::class);

    /* Role Management */
    // Route::apiResource('role', RoleController::class);
    // Route::get('permissions', [PermissionController::class, 'index']);
});

/*
|--------------------------------------------------------------------------
| Webhook Callback Routes
|--------------------------------------------------------------------------
*/
Route::prefix('webhook')->middleware('log.webhook')->group(function () {
    Route::get('/', fn() => response()->json(['message' => 'Central webhook ping successful!']));
    Route::post('stripe', [\Laravel\Cashier\Http\Controllers\WebhookController::class, 'handleWebhook']);
});
