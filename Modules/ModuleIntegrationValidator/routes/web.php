<?php

use Illuminate\Support\Facades\Route;
use Modules\ModuleIntegrationValidator\Http\Controllers\ModuleIntegrationValidatorController;

Route::middleware(['web', 'auth'])
    ->prefix(config('module-integration-validator.route_prefix', 'module-integration-validator'))
    ->name(config('module-integration-validator.route_name_prefix', 'module-integration-validator.'))
    ->group(function () {
        Route::get('/', [ModuleIntegrationValidatorController::class, 'index'])
            ->name('index')
            ->middleware('permission:permission_module_integration_validator_view');

        Route::post('/run', [ModuleIntegrationValidatorController::class, 'run'])
            ->name('run')
            ->middleware('permission:permission_module_integration_validator_run');
    });
