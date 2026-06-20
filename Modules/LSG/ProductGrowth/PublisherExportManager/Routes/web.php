<?php

use Illuminate\Support\Facades\Route;
use Modules\LSG\ProductGrowth\Shared\Http\Controllers\ProductGrowthStageController;

Route::middleware(['web', 'auth'])->prefix('product-growth/publisher-export-manager')->name('product_growth.publisher_export_manager.')->group(function () {
    Route::get('/', ProductGrowthStageController::class)->defaults('stage', 'publisher-export-manager')->name('dashboard');
    Route::get('products/{product}', [ProductGrowthStageController::class, 'edit'])->defaults('stage', 'publisher-export-manager')->name('product.edit');
    Route::put('products/{product}', [ProductGrowthStageController::class, 'update'])->defaults('stage', 'publisher-export-manager')->name('product.update');
});
