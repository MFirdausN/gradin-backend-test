<?php

use App\Http\Controllers\CourierController;
use Illuminate\Support\Facades\Route;

Route::delete('couriers/{courier}/force', [CourierController::class, 'forceDestroy'])
    ->withTrashed()
    ->name('couriers.force-destroy');

Route::apiResource('couriers', CourierController::class);
