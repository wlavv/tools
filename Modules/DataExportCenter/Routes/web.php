<?php

use Illuminate\Support\Facades\Route;
use Modules\DataExportCenter\Http\Controllers\DashboardController;
use Modules\DataExportCenter\Http\Controllers\ExportController;
use Modules\DataExportCenter\Http\Controllers\ProfileController;
use Modules\DataExportCenter\Http\Controllers\ReportTemplateController;

Route::middleware(config('data-export-center.route_middleware', ['web']))
    ->prefix(config('data-export-center.route_prefix', 'data-export-center'))
    ->name('data_export_center.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/profiles', [ProfileController::class, 'index'])->name('profiles.index');
        Route::get('/profiles/{profile}', [ProfileController::class, 'show'])->name('profiles.show');
        Route::post('/profiles/{profile}/exports', [ExportController::class, 'store'])->name('profiles.exports.store');

        Route::get('/batches/{batch}', [ExportController::class, 'showBatch'])->name('batches.show');
        Route::get('/batches/{batch}/download', [ExportController::class, 'download'])->name('batches.download');

        Route::get('/templates', [ReportTemplateController::class, 'index'])->name('templates.index');
        Route::post('/templates', [ReportTemplateController::class, 'store'])->name('templates.store');
    });
