<?php

use Illuminate\Support\Facades\Route;
use Modules\LSG\ProductGrowth\Shared\Http\Controllers\ProductGrowthStageController;

Route::middleware(['web', 'auth'])->prefix('product-growth/marketing-content-manager')->name('product_growth.marketing_content_manager.')->group(function () {
    Route::get('/', ProductGrowthStageController::class)->defaults('stage', 'marketing-content-manager')->name('dashboard');
    Route::get('products/{product}', [ProductGrowthStageController::class, 'edit'])->defaults('stage', 'marketing-content-manager')->name('product.edit');
    Route::put('products/{product}', [ProductGrowthStageController::class, 'update'])->defaults('stage', 'marketing-content-manager')->name('product.update');
});
