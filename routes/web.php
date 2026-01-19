<?php

use App\Enums\PermissionsEnum;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Auth\SystemMonitoringAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ApiResponse::success('Ping successful - '.config('app.name'));
});

Route::get('login', [SystemMonitoringAuthController::class, 'showLoginForm'])->name('login');
Route::post('login', [SystemMonitoringAuthController::class, 'login']);
Route::post('logout', [SystemMonitoringAuthController::class, 'logout'])->name('logout');

Route::get('admin/dashboard', [AdminDashboardController::class, 'index'])
    ->middleware(['auth:web', 'can:'.PermissionsEnum::VIEW_SYSTEM_ANALYTICS->value])
    ->name('admin.dashboard');

Route::post('admin/dashboard/tenant-context', [AdminDashboardController::class, 'setTenantContext'])
    ->middleware(['auth:web', 'can:'.PermissionsEnum::VIEW_SYSTEM_ANALYTICS->value])
    ->name('admin.tenant-context.set');

Route::post('admin/dashboard/tenant-context/clear', [AdminDashboardController::class, 'clearTenantContext'])
    ->middleware(['auth:web', 'can:'.PermissionsEnum::VIEW_SYSTEM_ANALYTICS->value])
    ->name('admin.tenant-context.clear');
