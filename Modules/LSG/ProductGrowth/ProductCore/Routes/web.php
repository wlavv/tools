<?php

use Illuminate\Support\Facades\Route;
use Modules\LSG\ProductGrowth\ProductCore\Http\Controllers\ProductCoreDashboardController;
use Modules\LSG\ProductGrowth\ProductCore\Http\Controllers\ProductCoreProductController;

Route::middleware(config('product-core.middleware', ['web', 'auth']))
    ->prefix(config('product-core.route_prefix', 'product-growth/product-core'))
    ->name(config('product-core.route_name', 'product_growth.product_core.'))
    ->group(function () {
        Route::get('/', ProductCoreDashboardController::class)->name('dashboard');

        Route::resource('products', ProductCoreProductController::class);
        Route::post('products/{product}/generate-description', [ProductCoreProductController::class, 'generateDescription'])->name('products.generate_description');
        Route::post('products/{product}/approve', [ProductCoreProductController::class, 'approve'])->name('products.approve');
        Route::post('products/{product}/mark-ready-to-sync', [ProductCoreProductController::class, 'markReadyToSync'])->name('products.mark_ready_to_sync');
        Route::post('products/{product}/archive', [ProductCoreProductController::class, 'archive'])->name('products.archive');
    });
