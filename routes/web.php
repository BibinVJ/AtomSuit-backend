<?php

use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return ApiResponse::success('Ping successful - ' . config('app.name'));
});

Route::get('login', [App\Http\Controllers\Auth\SystemMonitoringAuthController::class, 'showLoginForm'])->name('login');
Route::post('login', [App\Http\Controllers\Auth\SystemMonitoringAuthController::class, 'login']);
Route::post('logout', [App\Http\Controllers\Auth\SystemMonitoringAuthController::class, 'logout'])->name('logout');

Route::get('admin/dashboard', [App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])
    ->middleware(['auth:web', 'can:' . \App\Enums\PermissionsEnum::VIEW_SYSTEM_ANALYTICS->value])
    ->name('admin.dashboard');

Route::post('admin/dashboard/tenant-context', [App\Http\Controllers\Admin\AdminDashboardController::class, 'setTenantContext'])
    ->middleware(['auth:web', 'can:' . \App\Enums\PermissionsEnum::VIEW_SYSTEM_ANALYTICS->value])
    ->name('admin.tenant-context.set');

Route::post('admin/dashboard/tenant-context/clear', [App\Http\Controllers\Admin\AdminDashboardController::class, 'clearTenantContext'])
    ->middleware(['auth:web', 'can:' . \App\Enums\PermissionsEnum::VIEW_SYSTEM_ANALYTICS->value])
    ->name('admin.tenant-context.clear');


