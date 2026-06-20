<?php

use Illuminate\Support\Facades\Route;
use Modules\LSG\ProductGrowth\Shared\Http\Controllers\ProductGrowthStageController;

Route::middleware(['web', 'auth'])->prefix('product-growth/prestashop-bridge')->name('product_growth.prestashop_bridge.')->group(function () {
    Route::get('/', ProductGrowthStageController::class)->defaults('stage', 'prestashop-bridge')->name('dashboard');
    Route::get('products/{product}', [ProductGrowthStageController::class, 'edit'])->defaults('stage', 'prestashop-bridge')->name('product.edit');
    Route::put('products/{product}', [ProductGrowthStageController::class, 'update'])->defaults('stage', 'prestashop-bridge')->name('product.update');
});
