<?php

use Illuminate\Support\Facades\Route;
use Modules\LSG\ProductGrowth\Shared\Http\Controllers\ProductGrowthStageController;

Route::middleware(['web', 'auth'])->prefix('product-growth/product-evolution-manager')->name('product_growth.product_evolution_manager.')->group(function () {
    Route::get('/', ProductGrowthStageController::class)->defaults('stage', 'product-evolution-manager')->name('dashboard');
    Route::get('products/{product}', [ProductGrowthStageController::class, 'edit'])->defaults('stage', 'product-evolution-manager')->name('product.edit');
    Route::put('products/{product}', [ProductGrowthStageController::class, 'update'])->defaults('stage', 'product-evolution-manager')->name('product.update');
});
