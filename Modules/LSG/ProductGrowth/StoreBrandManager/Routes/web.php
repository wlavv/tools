<?php

use Illuminate\Support\Facades\Route;
use Modules\LSG\ProductGrowth\Shared\Http\Controllers\ProductGrowthStageController;

Route::middleware(['web', 'auth'])->prefix('product-growth/store-brand-manager')->name('product_growth.store_brand_manager.')->group(function () {
    Route::get('/', ProductGrowthStageController::class)->defaults('stage', 'store-brand-manager')->name('dashboard');
    Route::get('products/{product}', [ProductGrowthStageController::class, 'edit'])->defaults('stage', 'store-brand-manager')->name('product.edit');
    Route::put('products/{product}', [ProductGrowthStageController::class, 'update'])->defaults('stage', 'store-brand-manager')->name('product.update');
});
