<?php

use App\Enums\RolesEnum;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ResetTokenController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BoothController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SectionController;
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
Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:'.RolesEnum::ADMIN->value)->group(function () {
        Route::get('users', [UserController::class, 'index']);

        // booths
        Route::apiResource('booth', BoothController::class);
        // Route::post('booth/{id}/update', [BoothController::class, 'update']);
    });


    Route::apiResource('booking', BookingController::class);


    // payments
    // Route::post('payments/initiate', [PaymentController::class, 'initiate']);
    // Route::post('payments/callback', [PaymentController::class, 'handleCallback']);
});




// Route::prefix('pages')->group(function () {
//     Route::get('/', [PageController::class, 'index']);
//     Route::post('/', [PageController::class, 'store']);
//     Route::get('/{slug}', [PageController::class, 'show']);
//     Route::put('/{page}', [PageController::class, 'update']);
//     Route::delete('/{page}', [PageController::class, 'destroy']);

//     // Section routes nested under pages
//     Route::post('/{pageId}/sections', [SectionController::class, 'store']);
//     Route::post('/{page}/sections/reorder', [SectionController::class, 'reorder']);
// });

// Route::put('sections/{section}', [SectionController::class, 'update']);
// Route::delete('sections/{section}', [SectionController::class, 'destroy']);





/*
|--------------------------------------------------------------------------
| Webhook Callback Routes
|--------------------------------------------------------------------------
*/
Route::prefix('callback')->middleware('log.webhook')->group(function () {
    Route::get('/', fn() => response()->json(['message' => 'Webhook ping successful!']));

});
