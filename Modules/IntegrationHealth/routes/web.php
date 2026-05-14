<?php

use Illuminate\Support\Facades\Route;
use Modules\IntegrationHealth\Http\Controllers\IntegrationHealthApiController;
use Modules\IntegrationHealth\Http\Controllers\IntegrationHealthDashboardController;
use Modules\IntegrationHealth\Http\Controllers\IntegrationHealthEventController;
use Modules\IntegrationHealth\Http\Controllers\IntegrationHealthIntegrationController;

Route::middleware(['web', 'auth'])
    ->prefix(config('integration-health.route_prefix', 'integration-health'))
    ->name('integration_health.')
    ->group(function () {
        Route::get('/', [IntegrationHealthDashboardController::class, 'index'])->name('index');
        Route::resource('integrations', IntegrationHealthIntegrationController::class)
            ->parameters(['integrations' => 'integration'])
            ->except(['show', 'destroy']);
        Route::get('events', [IntegrationHealthEventController::class, 'index'])->name('events.index');
        Route::post('events/{event}/resolve', [IntegrationHealthEventController::class, 'resolve'])->name('events.resolve');
    });

Route::middleware(['web', 'auth'])
    ->prefix(config('integration-health.route_prefix', 'integration-health') . '/api')
    ->name('integration_health.api.')
    ->group(function () {
        Route::post('heartbeat', [IntegrationHealthApiController::class, 'heartbeat'])->name('heartbeat');
        Route::post('event', [IntegrationHealthApiController::class, 'event'])->name('event');
        Route::post('metric', [IntegrationHealthApiController::class, 'metric'])->name('metric');
    });
