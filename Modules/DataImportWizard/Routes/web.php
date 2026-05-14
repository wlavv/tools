<?php

use Illuminate\Support\Facades\Route;
use Modules\DataImportWizard\Http\Controllers\BatchController;
use Modules\DataImportWizard\Http\Controllers\DashboardController;
use Modules\DataImportWizard\Http\Controllers\ExecuteController;
use Modules\DataImportWizard\Http\Controllers\ProfileController;
use Modules\DataImportWizard\Http\Controllers\TemplateController;
use Modules\DataImportWizard\Http\Controllers\UploadController;

Route::middleware(config('data-import-wizard.route_middleware', ['web']))
    ->prefix(config('data-import-wizard.route_prefix', 'data-import-wizard'))
    ->name('data_import_wizard.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/profiles', [ProfileController::class, 'index'])->name('profiles.index');
        Route::get('/profiles/{profile}', [ProfileController::class, 'show'])->name('profiles.show');
        Route::get('/profiles/{profile}/template', [TemplateController::class, 'download'])->name('profiles.template');
        Route::get('/profiles/{profile}/upload', [UploadController::class, 'create'])->name('profiles.upload');
        Route::post('/profiles/{profile}/upload', [UploadController::class, 'store'])->name('profiles.upload.store');

        Route::get('/batches', [BatchController::class, 'index'])->name('batches.index');
        Route::get('/batches/{batch}/preview', [BatchController::class, 'preview'])->name('batches.preview');
        Route::post('/batches/{batch}/execute', [ExecuteController::class, 'execute'])->name('batches.execute');
        Route::get('/batches/{batch}', [BatchController::class, 'show'])->name('batches.show');
    });
