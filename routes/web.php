<?php

use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\Route;


foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {

        Route::get('/', function () {
            return ApiResponse::success('Ping successful - ' . config('app.name'));
        });
    });
}