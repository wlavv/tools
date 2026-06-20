<?php

use Illuminate\Support\Facades\Route;
use Modules\LSG\ProductGrowth\Shared\Http\Controllers\ProductGrowthStageController;

Route::middleware(['web', 'auth'])->prefix('product-growth/webcatalogue-premium-layer')->name('product_growth.webcatalogue_premium_layer.')->group(function () {
    Route::get('/', ProductGrowthStageController::class)->defaults('stage', 'webcatalogue-premium-layer')->name('dashboard');
    Route::get('products/{product}', [ProductGrowthStageController::class, 'edit'])->defaults('stage', 'webcatalogue-premium-layer')->name('product.edit');
    Route::put('products/{product}', [ProductGrowthStageController::class, 'update'])->defaults('stage', 'webcatalogue-premium-layer')->name('product.update');
});
