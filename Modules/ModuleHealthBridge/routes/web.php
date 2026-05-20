<?php

use Illuminate\Support\Facades\Route;
use Modules\ModuleHealthBridge\Http\Controllers\ModuleHealthBridgeController;

Route::middleware(['web', 'auth'])
    ->prefix('module-health-bridge')
    ->name('module-health-bridge.')
    ->group(function () {
        Route::get('/', [ModuleHealthBridgeController::class, 'index'])->name('index');
        Route::post('/run', [ModuleHealthBridgeController::class, 'run'])->name('run');
    });
