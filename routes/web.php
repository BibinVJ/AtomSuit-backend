<?php

use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return ApiResponse::success('Ping successful - ' . config('app.name'));
});
