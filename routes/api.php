<?php

use App\Helpers\ApiResponse;
use App\Http\Controllers\AccountGroupController;
use App\Http\Controllers\AccountTypeController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChartOfAccountController;
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
use App\Http\Controllers\UserLoginDetailController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\WarehouseController;
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
    Route::prefix('plans')->group(function () {
        Route::get('/', [PlanController::class, 'index']);
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
    Route::post('tenants/{tenant}/send-mail', [TenantController::class, 'sendMail']);
    Route::apiResource('tenants', TenantController::class);

    Route::apiResource('subscriptions', SubscriptionController::class);

    Route::prefix('tenant-subscription')->group(function () {
        Route::get('current', [TenantSubscriptionController::class, 'current']);
        Route::get('change-plan', [TenantSubscriptionController::class, 'changePLan']);
        Route::post('cancel', [TenantSubscriptionController::class, 'cancel']);
    });

    Route::get('domains', [DomainController::class, 'index']);

    /* Accounting */
    /* Account Types */
    Route::get('account-types', [AccountTypeController::class, 'index']);

    /* Account Groups */
    Route::get('account-groups/export/excel', [AccountGroupController::class, 'export']);
    Route::post('account-groups/import', [AccountGroupController::class, 'import']);
    Route::post('account-groups/{accountGroup}/restore', [AccountGroupController::class, 'restore'])->withTrashed();
    Route::apiResource('account-groups', AccountGroupController::class)->withTrashed(['show', 'destroy']);

    /* Chart of Accounts */
    Route::get('chart-of-accounts/export/excel', [ChartOfAccountController::class, 'export']);
    Route::post('chart-of-accounts/import', [ChartOfAccountController::class, 'import']);
    Route::post('chart-of-accounts/{chartOfAccount}/restore', [ChartOfAccountController::class, 'restore'])->withTrashed();
    Route::apiResource('chart-of-accounts', ChartOfAccountController::class)->withTrashed(['show', 'destroy']);

    /* Currency & Exchange Rate */
    Route::get('currencies/export/excel', [CurrencyController::class, 'export']);
    Route::post('currencies/{currency}/restore', [CurrencyController::class, 'restore'])->withTrashed();
    Route::apiResource('currencies', CurrencyController::class)->withTrashed(['show', 'destroy']);

    Route::get('exchange-rates/export/excel', [ExchangeRateController::class, 'export']);
    Route::post('exchange-rates/{exchange_rate}/restore', [ExchangeRateController::class, 'restore'])->withTrashed();
    Route::apiResource('exchange-rates', ExchangeRateController::class)->withTrashed(['show', 'destroy']);

    // gl settings

    /*
    |--------------------------------------------------------------------------
    | Tax Management
    |--------------------------------------------------------------------------
    */
    Route::post('tax-rates/{tax_rate}/restore', [App\Http\Controllers\TaxRateController::class, 'restore'])->withTrashed();
    Route::apiResource('tax-rates', App\Http\Controllers\TaxRateController::class)->withTrashed(['show', 'destroy']);

    Route::post('tax-groups/{tax_group}/restore', [App\Http\Controllers\TaxGroupController::class, 'restore'])->withTrashed();
    Route::apiResource('tax-groups', App\Http\Controllers\TaxGroupController::class)->withTrashed(['show', 'destroy']);

    /*
    |--------------------------------------------------------------------------
    | Inventory
    |--------------------------------------------------------------------------
    */
    /* Category */
    /* Category */
    Route::get('categories/export', [CategoryController::class, 'export']);
    Route::get('categories/sample-excel', [CategoryController::class, 'downloadSample']);
    Route::post('categories/import', [CategoryController::class, 'import']);
    Route::post('categories/{category}/restore', [CategoryController::class, 'restore'])->withTrashed();
    Route::apiResource('categories', CategoryController::class)->withTrashed(['show', 'destroy']);

    /* Unit */
    /* Unit */
    Route::get('units/export', [UnitController::class, 'export']);
    Route::get('units/sample-excel', [UnitController::class, 'downloadSample']);
    Route::post('units/import', [UnitController::class, 'import']);
    Route::post('units/{unit}/restore', [UnitController::class, 'restore'])->withTrashed();
    Route::apiResource('units', UnitController::class)->withTrashed(['show', 'destroy']);

    /* Item */
    /* Item */
    Route::get('items/export', [ItemController::class, 'export']);
    Route::get('items/sample-excel', [ItemController::class, 'downloadSample']);
    Route::post('items/import', [ItemController::class, 'import']);
    Route::post('items/{item}/restore', [ItemController::class, 'restore'])->withTrashed();
    Route::apiResource('items', ItemController::class)->withTrashed(['show', 'destroy']);

    Route::apiResource('batches', BatchController::class);

    // warehouse
    // warehouse
    Route::get('warehouses/export', [WarehouseController::class, 'export']);
    Route::get('warehouses/sample-excel', [WarehouseController::class, 'downloadSample']);
    Route::post('warehouses/import', [WarehouseController::class, 'import']);
    Route::post('warehouses/{warehouse}/restore', [WarehouseController::class, 'restore'])->withTrashed();
    Route::apiResource('warehouses', WarehouseController::class)->withTrashed(['show', 'destroy']);

    /*
    |--------------------------------------------------------------------------
    | Customer & Sales
    |--------------------------------------------------------------------------
    */
    /* Customer */
    /* Customer */
    Route::get('customers/export', [CustomerController::class, 'export']);
    Route::get('customers/sample-excel', [CustomerController::class, 'downloadSample']);
    Route::post('customers/import', [CustomerController::class, 'import']);
    Route::post('customers/{customer}/restore', [CustomerController::class, 'restore'])->withTrashed();
    Route::apiResource('customers', CustomerController::class)->withTrashed(['show', 'destroy']);

    /* Sale */
    Route::get('sales/next-invoice-number', [SaleController::class, 'getNextInvoiceNumber']);
    Route::apiResource('sales', SaleController::class);

    /*
    |--------------------------------------------------------------------------
    | Vendor & Purchases
    |--------------------------------------------------------------------------
    */
    /* Vendor */
    /* Vendor */
    Route::get('vendors/export', [VendorController::class, 'export']);
    Route::get('vendors/sample-excel', [VendorController::class, 'downloadSample']);
    Route::post('vendors/import', [VendorController::class, 'import']);
    Route::post('vendors/{vendor}/restore', [VendorController::class, 'restore'])->withTrashed();
    Route::apiResource('vendors', VendorController::class)->withTrashed(['show', 'destroy']);

    /* Purchase */
    Route::get('purchases/next-invoice-number', [PurchaseController::class, 'getNextInvoiceNumber']);
    Route::apiResource('purchases', PurchaseController::class);

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
    Route::get('audits', [AuditController::class, 'index']);
    Route::get('audits/{activity}', [AuditController::class, 'show']);

    /* User Management */
    Route::post('users/{user}/send-mail', [UserController::class, 'sendMail']);
    Route::post('users/{user}/restore', [UserController::class, 'restore'])->withTrashed();
    Route::apiResource('users', UserController::class)->withTrashed(['show', 'destroy']);
    Route::get('user-login-details', [UserLoginDetailController::class, 'index']);

    /* Role Management */
    Route::post('roles/{role}/restore', [RoleController::class, 'restore'])->withTrashed();
    Route::apiResource('roles', RoleController::class)->withTrashed(['show', 'destroy']);
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
