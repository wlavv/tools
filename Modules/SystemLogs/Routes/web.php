<?php

use Illuminate\Support\Facades\Route;
use Modules\SystemLogs\Http\Controllers\SystemLogsController;

Route::middleware(['web'])
    ->prefix('settings/system-logs')
    ->name('system_logs.')
    ->group(function () {
        Route::get('/', [SystemLogsController::class, 'index'])->name('index');
        Route::post('/create', [SystemLogsController::class, 'store'])->name('store');
        Route::post('/acknowledge-errors', [SystemLogsController::class, 'acknowledgeErrors'])->name('acknowledge_errors');
        Route::post('/{log}/acknowledge', [SystemLogsController::class, 'acknowledge'])->name('acknowledge');
        Route::get('/export', [SystemLogsController::class, 'export'])->name('export');
        Route::match(['GET', 'POST'], '/clear', [SystemLogsController::class, 'clear'])->name('clear');
    });
