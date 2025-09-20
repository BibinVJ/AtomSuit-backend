<?php

declare(strict_types=1);

use App\Helpers\ApiResponse;
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



Route::get('/', function () {
    return ApiResponse::success('Ping successful - ' . (tenant()->name ?? 'your tenant'));
});
