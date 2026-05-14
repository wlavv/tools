<?php

use Illuminate\Support\Facades\Route;
use Modules\ModuleHealth\Http\Controllers\ModuleHealthController;
use Modules\ModuleHealth\Http\Controllers\ModuleHealthModuleController;
use Modules\ModuleHealth\Http\Controllers\ModuleHealthProfileController;

Route::middleware(['web', 'auth'])
    ->prefix(config('module-health.route_prefix', 'module-health'))
    ->name('module_health.')
    ->group(function () {
        Route::get('/', [ModuleHealthController::class, 'index'])->name('index');
        Route::post('/scan/run', [ModuleHealthController::class, 'runScan'])->name('scan.run');
        Route::get('/scans', [ModuleHealthController::class, 'scans'])->name('scans.index');
        Route::get('/modules', [ModuleHealthModuleController::class, 'index'])->name('modules.index');
        Route::get('/modules/{item}', [ModuleHealthModuleController::class, 'show'])->name('modules.show');
        Route::get('/profiles', [ModuleHealthProfileController::class, 'index'])->name('profiles.index');
    });
