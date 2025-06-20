<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ResetTokenController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
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
Route::post('send-reset-otp', [ResetTokenController::class, 'sendResetOtp']);
Route::post('verify-otp', [ResetTokenController::class, 'verifyOtp']);
Route::post('reset-password', [ResetTokenController::class, 'resetPassword']);

// Social Login (phase 3)
// Route::get('auth/{provider}/redirect', [AuthController::class, 'socialRedirect']);
// Route::get('auth/{provider}/callback', [AuthController::class, 'socialCallback']);


/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:api'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('logout-all-devices', [AuthController::class, 'logoutFromAllDevices']);


    // Route::get('home/dashboard', [DashboardController::class, 'home']);

    // Route::apiResource('user', UserController::class);
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
    // warehouse


    /*
    |--------------------------------------------------------------------------
    | Sales
    |--------------------------------------------------------------------------
    */


    /*
    |--------------------------------------------------------------------------
    | Purchases
    |--------------------------------------------------------------------------
    */


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
