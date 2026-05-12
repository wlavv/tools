<?php

use Illuminate\Support\Facades\Route;
use Modules\ERP\Http\Controllers\ERPDashboardController;
use Modules\ERP\Http\Controllers\ERPSettingsController;
use Modules\ERP\Http\Controllers\ERPDocumentTypeController;
use Modules\ERP\Http\Controllers\ERPStatusController;
use Modules\ERP\Http\Controllers\ERPWorkflowController;
use Modules\ERP\Http\Controllers\ERPSupplierTermsController;

Route::middleware(['web', 'auth'])
    ->prefix('erp')
    ->as('erp.')
    ->group(function () {
        Route::get('/', [ERPDashboardController::class, 'index'])->name('dashboard');
        Route::get('/timeline/{step?}', [ERPDashboardController::class, 'timeline'])->name('timeline');

        Route::prefix('settings')->as('settings.')->group(function () {
            Route::get('/', [ERPSettingsController::class, 'index'])->name('index');
            Route::post('/config', [ERPSettingsController::class, 'saveConfig'])->name('config.save');

            Route::resource('document-types', ERPDocumentTypeController::class)->except(['show']);
            Route::resource('statuses', ERPStatusController::class)->except(['show']);
            Route::resource('workflows', ERPWorkflowController::class)->except(['show']);
            Route::resource('supplier-terms', ERPSupplierTermsController::class)->except(['show']);
        });
    });
