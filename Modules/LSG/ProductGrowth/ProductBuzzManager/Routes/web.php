<?php

use Illuminate\Support\Facades\Route;
use Modules\LSG\ProductGrowth\Shared\Http\Controllers\ProductGrowthStageController;

Route::middleware(['web', 'auth'])->prefix('product-growth/product-buzz-manager')->name('product_growth.product_buzz_manager.')->group(function () {
    Route::get('/', ProductGrowthStageController::class)->defaults('stage', 'product-buzz-manager')->name('dashboard');
    Route::get('products/{product}', [ProductGrowthStageController::class, 'edit'])->defaults('stage', 'product-buzz-manager')->name('product.edit');
    Route::put('products/{product}', [ProductGrowthStageController::class, 'update'])->defaults('stage', 'product-buzz-manager')->name('product.update');
});
