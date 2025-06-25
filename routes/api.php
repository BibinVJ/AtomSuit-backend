<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', fn() => response()->json(['message' => 'Ping successful!']));
Route::post('contact', [EnquiryController::class, 'store']);


/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::prefix('auth')->group(function () {
    Route::post('send-reset-otp', [PasswordResetController::class, 'sendResetOtp']);
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


    // Route::get('home/dashboard', [DashboardController::class, 'home']);

    // Route::get('user/profile', [UserController::class, 'profile']);


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
    Route::apiResource('sale', SaleController::class);


    /*
    |--------------------------------------------------------------------------
    | Vendor & Purchases
    |--------------------------------------------------------------------------
    */
    Route::apiResource('vendor', VendorController::class);
    Route::apiResource('purchase', PurchaseController::class);


    /*
    |--------------------------------------------------------------------------
    | Reports
    |--------------------------------------------------------------------------
    */


    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    */
    /* User Management */
    Route::apiResource('user', UserController::class);

    // update settings

});



/*
|--------------------------------------------------------------------------
| Webhook Callback Routes
|--------------------------------------------------------------------------
*/
Route::prefix('callback')->middleware('log.webhook')->group(function () {
    Route::get('/', fn() => response()->json(['message' => 'Webhook ping successful!']));

});
