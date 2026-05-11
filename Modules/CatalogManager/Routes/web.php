<?php

use Illuminate\Support\Facades\Route;
use Modules\CatalogManager\Http\Controllers\ActionPanelController;
use Modules\CatalogManager\Http\Controllers\AiController;
use Modules\CatalogManager\Http\Controllers\CategoryController;
use Modules\CatalogManager\Http\Controllers\DashboardController;
use Modules\CatalogManager\Http\Controllers\DiagnosticsController;
use Modules\CatalogManager\Http\Controllers\IssuePanelController;
use Modules\CatalogManager\Http\Controllers\ManufacturerController;
use Modules\CatalogManager\Http\Controllers\ProductController;
use Modules\CatalogManager\Http\Controllers\StoreController;
use Modules\CatalogManager\Http\Controllers\SupplierController;
use Modules\CatalogManager\Http\Controllers\SyncController;

Route::middleware(['web', 'auth'])
    ->prefix(config('catalogmanager.route_prefix', 'catalog-manager'))
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('catalog-manager.dashboard');

        Route::get('/diagnostics', [DiagnosticsController::class, 'index'])->name('catalog-manager.diagnostics.index');

        Route::resource('products', ProductController::class)
            ->names('catalog-manager.products')
            ->except(['destroy']);

        Route::resource('manufacturers', ManufacturerController::class)
            ->names('catalog-manager.manufacturers')
            ->except(['show', 'destroy']);

        Route::resource('suppliers', SupplierController::class)
            ->names('catalog-manager.suppliers')
            ->except(['show', 'destroy']);

        Route::resource('stores', StoreController::class)
            ->names('catalog-manager.stores')
            ->except(['show', 'destroy']);

        Route::resource('categories', CategoryController::class)
            ->names('catalog-manager.categories')
            ->except(['show', 'destroy']);

        Route::get('/sync', [SyncController::class, 'index'])->name('catalog-manager.sync.index');
        Route::get('/ai', [AiController::class, 'index'])->name('catalog-manager.ai.index');

        Route::get('/issue-panels', [IssuePanelController::class, 'index'])->name('catalog-manager.issue-panels.index');
        Route::get('/issue-panels/data', [IssuePanelController::class, 'data'])->name('catalog-manager.issue-panels.data');

        Route::get('/action-panels', [ActionPanelController::class, 'index'])->name('catalog-manager.action-panels.index');
        Route::get('/action-panels/data', [ActionPanelController::class, 'data'])->name('catalog-manager.action-panels.data');
    });
