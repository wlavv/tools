<?php

use Illuminate\Support\Facades\Route;
use Modules\LSG\ProductGrowth\Shared\Http\Controllers\ProductGrowthStageController;

Route::middleware(['web', 'auth'])->prefix('product-growth/creative-asset-manager')->name('product_growth.creative_asset_manager.')->group(function () {
    Route::get('/', ProductGrowthStageController::class)->defaults('stage', 'creative-asset-manager')->name('dashboard');
    Route::get('products/{product}', [ProductGrowthStageController::class, 'edit'])->defaults('stage', 'creative-asset-manager')->name('product.edit');
    Route::put('products/{product}', [ProductGrowthStageController::class, 'update'])->defaults('stage', 'creative-asset-manager')->name('product.update');
});
