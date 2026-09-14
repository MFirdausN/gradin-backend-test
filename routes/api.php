<?php

use App\Http\Controllers\CourierController;
use Illuminate\Support\Facades\Route;

Route::delete('couriers/{courier}/force', [CourierController::class, 'forceDestroy'])
    ->withTrashed()
    ->name('couriers.force-destroy');

Route::patch('couriers/{courier}/restore', [CourierController::class, 'restore'])
    ->withTrashed()
    ->name('couriers.restore');

Route::apiResource('couriers', CourierController::class);
