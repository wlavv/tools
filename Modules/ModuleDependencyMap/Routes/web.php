<?php

use Illuminate\Support\Facades\Route;
use Modules\ModuleDependencyMap\Http\Controllers\ModuleDependencyMapController;

Route::middleware(config('module-dependency-map.middleware', ['web', 'auth']))
    ->prefix(config('module-dependency-map.route_prefix', 'module-dependency-map'))
    ->name(config('module-dependency-map.route_name', 'module-dependency-map.'))
    ->group(function (): void {
        Route::get('/', [ModuleDependencyMapController::class, 'index'])->name('index');
        Route::post('/run-all', [ModuleDependencyMapController::class, 'runAll'])->name('run-all');

        Route::get('/{module}', [ModuleDependencyMapController::class, 'show'])
            ->where('module', '[A-Za-z0-9_\-]+')
            ->name('show');

        Route::post('/{module}/run', [ModuleDependencyMapController::class, 'run'])
            ->where('module', '[A-Za-z0-9_\-]+')
            ->name('run');
    });
