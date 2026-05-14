<?php

use Illuminate\Support\Facades\Route;
use Modules\EnvironmentManager\Http\Controllers\EnvironmentManagerController;

Route::middleware(config('environment-manager.middleware', ['web', 'auth']))
    ->prefix(config('environment-manager.route_prefix', 'environment-manager'))
    ->name('environment_manager.')
    ->group(function () {
        Route::get('/', [EnvironmentManagerController::class, 'index'])->name('index');
        Route::get('/env', [EnvironmentManagerController::class, 'env'])->name('env');
        Route::get('/config', [EnvironmentManagerController::class, 'config'])->name('config');
        Route::get('/modules', [EnvironmentManagerController::class, 'modules'])->name('modules');
        Route::get('/modules/{moduleKey}', [EnvironmentManagerController::class, 'showModule'])->name('modules.show');
        Route::get('/effective/{key?}', [EnvironmentManagerController::class, 'effective'])->where('key', '.*')->name('effective');
    });
