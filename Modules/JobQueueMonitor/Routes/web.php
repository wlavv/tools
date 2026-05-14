<?php

use Illuminate\Support\Facades\Route;
use Modules\JobQueueMonitor\Http\Controllers\JobQueueMonitorController;

Route::middleware(['web', 'auth'])
    ->prefix(config('job-queue-monitor.route_prefix', 'job-queue-monitor'))
    ->name('job_queue_monitor.')
    ->group(function () {
        Route::get('/', [JobQueueMonitorController::class, 'index'])->name('index');
        Route::get('/failed', [JobQueueMonitorController::class, 'failed'])->name('failed.index');
        Route::get('/health', [JobQueueMonitorController::class, 'health'])->name('health.index');
        Route::get('/settings', [JobQueueMonitorController::class, 'settings'])->name('settings.index');
        Route::get('/runs/{run}', [JobQueueMonitorController::class, 'show'])->name('show');
        Route::post('/runs/{run}/resolve', [JobQueueMonitorController::class, 'resolve'])->name('resolve');
        Route::post('/health/run', [JobQueueMonitorController::class, 'runHealthCheck'])->name('health.run');
    });
