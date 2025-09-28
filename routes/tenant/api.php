<?php

declare(strict_types=1);

use App\Helpers\ApiResponse;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
| All routes here will be tenant-specific.
|
*/


/*
|--------------------------------------------------------------------------
| Public Tenant Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return ApiResponse::success('API ping successful - ' . (tenant()->name ?? config('app.name')));
});


Route::post('enquiry', [EnquiryController::class, 'store']);

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::prefix('auth')->group(function () {
    Route::post('send-reset-otp', [PasswordResetController::class, 'sendPasswordResetOtp']);
    Route::post('verify-otp', [PasswordResetController::class, 'verifyOtp']);
    Route::post('reset-password', [PasswordResetController::class, 'resetPassword']);
});

/*
|--------------------------------------------------------------------------
| Debug Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:api'])->get('/debug-auth-tenant', function () {
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
| Authenticated Tenant Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:api', 'validate.token.context'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('logout-all-devices', [AuthController::class, 'logoutFromAllDevices']);

    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'home']);
        Route::get('layout', [DashboardController::class, 'getLayout']);
        Route::post('layout', [DashboardController::class, 'updateLayout']);
    });

    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('unread', [NotificationController::class, 'unread']);
        Route::post('read/{id?}', [NotificationController::class, 'markAsRead']);
    });

    Route::prefix('profile')->group(function () {
        Route::get('/', [UserProfileController::class, 'show']);
        Route::post('/', [UserProfileController::class, 'update']);
        Route::post('/address', [UserProfileController::class, 'updateAddress']);
        Route::post('/social-links', [UserProfileController::class, 'updateSocialLinks']);
        Route::post('profile-image', [UserProfileController::class, 'updateProfileImage']);
        Route::delete('profile-image', [UserProfileController::class, 'removeProfileImage']);
    });

    /*
    |--------------------------------------------------------------------------
    | Accounting
    |--------------------------------------------------------------------------
    */
    // chart of account groups
    // Route::apiResource('chart-of-account', ChartOfAccountController::class);
    // taxes
    // tax groups
    // item tax types
    // currency
    // exchange rate
    // gl settings

    /*
    |--------------------------------------------------------------------------
    | Inventory
    |--------------------------------------------------------------------------
    */
    Route::apiResource('category', CategoryController::class);
    Route::apiResource('unit', UnitController::class);
    Route::apiResource('item', ItemController::class);
    Route::apiResource('batch', BatchController::class);
    // warehouse

    /*
    |--------------------------------------------------------------------------
    | Customer & Sales
    |--------------------------------------------------------------------------
    */
    Route::apiResource('customer', CustomerController::class);
    Route::get('sale/next-invoice-number', [SaleController::class, 'getNextInvoiceNumber']);
    Route::apiResource('sale', SaleController::class);

    /*
    |--------------------------------------------------------------------------
    | Vendor & Purchases
    |--------------------------------------------------------------------------
    */
    Route::apiResource('vendor', VendorController::class);
    Route::get('purchase/next-invoice-number', [PurchaseController::class, 'getNextInvoiceNumber']);
    Route::apiResource('purchase', PurchaseController::class);

    /*
    |--------------------------------------------------------------------------
    | Reports
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Others
    |--------------------------------------------------------------------------
    */
    /* User Management */
    Route::post('user/{user}/send-mail', [UserController::class, 'sendMail']);
    Route::apiResource('user', UserController::class);

    /* Role Management */
    Route::apiResource('role', RoleController::class);
    Route::get('permissions', [PermissionController::class, 'index']);

    // update settings
});
