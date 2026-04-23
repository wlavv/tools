<?php

use Illuminate\Support\Facades\Route;
use Modules\SystemLogs\Http\Controllers\SystemLogsController;

Route::middleware(['web'])
    ->prefix('system-logs')
    ->name('system_logs.')
    ->group(function () {
        Route::get('/', [SystemLogsController::class, 'index'])->name('index');
        Route::post('/create', [SystemLogsController::class, 'store'])->name('store');
        Route::get('/export', [SystemLogsController::class, 'export'])->name('export');
        Route::match(['GET', 'POST'], '/clear', [SystemLogsController::class, 'clear'])->name('clear');
    });
