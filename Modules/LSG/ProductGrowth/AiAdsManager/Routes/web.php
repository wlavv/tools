<?php

use Illuminate\Support\Facades\Route;
use Modules\LSG\ProductGrowth\Shared\Http\Controllers\ProductGrowthStageController;

Route::middleware(['web', 'auth'])->prefix('product-growth/ai-ads-manager')->name('product_growth.ai_ads_manager.')->group(function () {
    Route::get('/', ProductGrowthStageController::class)->defaults('stage', 'ai-ads-manager')->name('dashboard');
    Route::get('products/{product}', [ProductGrowthStageController::class, 'edit'])->defaults('stage', 'ai-ads-manager')->name('product.edit');
    Route::put('products/{product}', [ProductGrowthStageController::class, 'update'])->defaults('stage', 'ai-ads-manager')->name('product.update');
});
