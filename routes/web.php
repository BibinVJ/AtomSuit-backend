<?php

use Illuminate\Support\Facades\Route;

Route::fallback(fn() => response()->json('Not Found!!!', 404));
