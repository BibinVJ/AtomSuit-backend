<?php

use App\Helpers\ApiResponse;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\ExchangeRateController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\TenantSubscriptionController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes work for both central and tenant contexts automatically.
| The tenancy middleware runs globally and handles sqitching between
| the central and tenant contexts, and the dynamic auth provider
| handles switching between central and tenant user models.
|
*/

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    $context = tenant() ? 'Tenant: '.tenant()->name : 'Central';

    return ApiResponse::success('API ping successful - '.config('app.name').' ('.$context.')');
});

Route::get('plan', [PlanController::class, 'index']);
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
        Route::get('cards', [DashboardController::class, 'getCards']);
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
    | Plan
    |--------------------------------------------------------------------------
    */
    Route::prefix('plan')->group(function () {
        Route::get('/{plan}', [PlanController::class, 'show']);
        Route::post('/', [PlanController::class, 'store']);
        Route::post('/{plan}', [PlanController::class, 'update']);
        Route::delete('/{plan}', [PlanController::class, 'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | Tenant, Subscription & Domain Management
    |--------------------------------------------------------------------------
    */
    Route::get('tenant-stats', [TenantController::class, 'stats']);
    Route::post('tenant/{tenant}/send-mail', [TenantController::class, 'sendMail']);
    Route::apiResource('tenant', TenantController::class);

    Route::apiResource('subscription', SubscriptionController::class);

    Route::prefix('tenant-subscription')->group(function () {
        Route::get('current', [TenantSubscriptionController::class, 'current']);
        Route::get('change-plan', [TenantSubscriptionController::class, 'changePLan']);
        Route::post('cancel', [TenantSubscriptionController::class, 'cancel']);
    });

    Route::get('domain', [DomainController::class, 'index']);

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

    /* Currency & Exchange Rate */
    Route::get('currency/export/excel', [CurrencyController::class, 'export']);
    Route::post('currency/{currency}/restore', [CurrencyController::class, 'restore'])->withTrashed();
    Route::apiResource('currency', CurrencyController::class)->withTrashed(['show', 'destroy']);

    Route::get('exchange-rate/export/excel', [ExchangeRateController::class, 'export']);
    Route::post('exchange-rate/{exchange_rate}/restore', [ExchangeRateController::class, 'restore'])->withTrashed();
    Route::apiResource('exchange-rate', ExchangeRateController::class)->withTrashed(['show', 'destroy']);

    // gl settings

    /*
    |--------------------------------------------------------------------------
    | Inventory
    |--------------------------------------------------------------------------
    */
    /* Category */
    Route::get('category/export', [CategoryController::class, 'export']);
    Route::get('category/sample-excel', [CategoryController::class, 'downloadSample']);
    Route::post('category/import', [CategoryController::class, 'import']);
    Route::post('category/{category}/restore', [CategoryController::class, 'restore'])->withTrashed();
    Route::apiResource('category', CategoryController::class)->withTrashed(['show', 'destroy']);

    /* Unit */
    Route::get('unit/export', [UnitController::class, 'export']);
    Route::get('unit/sample-excel', [UnitController::class, 'downloadSample']);
    Route::post('unit/import', [UnitController::class, 'import']);
    Route::post('unit/{unit}/restore', [UnitController::class, 'restore'])->withTrashed();
    Route::apiResource('unit', UnitController::class)->withTrashed(['show', 'destroy']);

    /* Item */
    Route::get('item/export', [ItemController::class, 'export']);
    Route::get('item/sample-excel', [ItemController::class, 'downloadSample']);
    Route::post('item/import', [ItemController::class, 'import']);
    Route::post('item/{item}/restore', [ItemController::class, 'restore'])->withTrashed();
    Route::apiResource('item', ItemController::class)->withTrashed(['show', 'destroy']);

    Route::apiResource('batch', BatchController::class);
    // warehouse

    /*
    |--------------------------------------------------------------------------
    | Customer & Sales
    |--------------------------------------------------------------------------
    */
    /* Customer */
    Route::get('customer/export', [CustomerController::class, 'export']);
    Route::get('customer/sample-excel', [CustomerController::class, 'downloadSample']);
    Route::post('customer/import', [CustomerController::class, 'import']);
    Route::post('customer/{customer}/restore', [CustomerController::class, 'restore'])->withTrashed();
    Route::apiResource('customer', CustomerController::class)->withTrashed(['show', 'destroy']);

    /* Sale */
    Route::get('sale/next-invoice-number', [SaleController::class, 'getNextInvoiceNumber']);
    Route::apiResource('sale', SaleController::class);

    /*
    |--------------------------------------------------------------------------
    | Vendor & Purchases
    |--------------------------------------------------------------------------
    */
    /* Vendor */
    Route::get('vendor/export', [VendorController::class, 'export']);
    Route::get('vendor/sample-excel', [VendorController::class, 'downloadSample']);
    Route::post('vendor/import', [VendorController::class, 'import']);
    Route::post('vendor/{vendor}/restore', [VendorController::class, 'restore'])->withTrashed();
    Route::apiResource('vendor', VendorController::class)->withTrashed(['show', 'destroy']);

    /* Purchase */
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
    /* Audit Logs */
    Route::get('audit', [AuditController::class, 'index']);
    Route::get('audit/{activity}', [AuditController::class, 'show']);

    /* User Management */
    Route::post('user/{user}/send-mail', [UserController::class, 'sendMail']);
    Route::post('user/{user}/restore', [UserController::class, 'restore'])->withTrashed();
    Route::apiResource('user', UserController::class)->withTrashed(['show', 'destroy']);

    /* Role Management */
    Route::post('role/{role}/restore', [RoleController::class, 'restore'])->withTrashed();
    Route::apiResource('role', RoleController::class)->withTrashed(['show', 'destroy']);
    Route::get('permissions', [PermissionController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | Settings Management
    |--------------------------------------------------------------------------
    */
    Route::prefix('settings')->group(function () {
        Route::get('/', [SettingController::class, 'index']);
        Route::get('groups', [SettingController::class, 'groups']);
        Route::get('group/{group}', [SettingController::class, 'getByGroup']);
        Route::get('{key}', [SettingController::class, 'show']);
        Route::post('{key}', [SettingController::class, 'update']);
        Route::post('/', [SettingController::class, 'bulkUpdate']);
        Route::delete('{key}', [SettingController::class, 'destroy']);
        Route::delete('{key}/file', [SettingController::class, 'deleteFile']);
    });
});

/*
|--------------------------------------------------------------------------
| Webhook Callback Routes
|--------------------------------------------------------------------------
*/
Route::prefix('webhook')->middleware('log.webhook')->group(function () {
    Route::get('/', fn () => response()->json(['message' => 'Central webhook ping successful!']));
    Route::post('stripe', [StripeWebhookController::class, 'handleWebhook']);
});
