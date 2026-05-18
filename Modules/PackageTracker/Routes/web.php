<?php

use Illuminate\Support\Facades\Route;
use Modules\PackageTracker\Http\Controllers\CarrierController;
use Modules\PackageTracker\Http\Controllers\DashboardController;
use Modules\PackageTracker\Http\Controllers\PublicTrackingController;
use Modules\PackageTracker\Http\Controllers\ShipmentController;

if (config('package_tracker.public.enabled')) {
    Route::middleware(config('package_tracker.public.middleware', ['web']))
        ->prefix(config('package_tracker.public.route_prefix', 'track'))
        ->name('package_tracker.public.')
        ->group(function () {
            Route::get('/{token}', [PublicTrackingController::class, 'show'])->name('show');
        });
}

Route::middleware(config('package_tracker.middleware'))
    ->prefix(config('package_tracker.route_prefix'))
    ->name(config('package_tracker.route_name'))
    ->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::post('/shipments/{shipment}/sync', [ShipmentController::class, 'sync'])->name('shipments.sync');
        Route::resource('/shipments', ShipmentController::class)->only(['index', 'create', 'store', 'show']);

        Route::resource('/carriers', CarrierController::class)->except(['show', 'destroy']);
    });
