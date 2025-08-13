<?php

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
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => response()->json(['message' => 'Ping successful!']));
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
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:api'])->group(function () {
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

/*
|--------------------------------------------------------------------------
| Webhook Callback Routes
|--------------------------------------------------------------------------
*/
Route::prefix('callback')->middleware('log.webhook')->group(function () {
    Route::get('/', fn () => response()->json(['message' => 'Webhook ping successful!']));
});
