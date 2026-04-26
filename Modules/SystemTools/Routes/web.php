<?php

use Illuminate\Support\Facades\Route;
use Modules\SystemTools\Http\Controllers\MaintenanceController;
use Modules\SystemTools\Http\Controllers\ExternalMaintenanceController;

Route::middleware(['web', 'auth'])->prefix('settings/system-tools')->name('system-tools.')->group(function () {
    Route::get('/',                 [MaintenanceController::class, 'index'])->name('index');
    Route::post('/run/{action}',    [MaintenanceController::class, 'run'])->name('run');
});

Route::middleware(['web'])->prefix('system-tools/external')->name('system-tools.external.')->group(function () {
    Route::match(['get', 'post'], '/run/{action}',  [ExternalMaintenanceController::class, 'run'])->name('run');
    Route::match(['get', 'post'], '/run/{action}',  [ExternalMaintenanceController::class, 'run'])->name('run');
    Route::match(['get', 'post'], '/links',         [ExternalMaintenanceController::class, 'links'])->name('links');
});