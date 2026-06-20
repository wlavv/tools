<?php

use Illuminate\Support\Facades\Route;
use Modules\LSG\ProductGrowth\Shared\Http\Controllers\ProductGrowthStageController;

Route::middleware(['web', 'auth'])->prefix('product-growth/brand-compliance-manager')->name('product_growth.brand_compliance_manager.')->group(function () {
    Route::get('/', ProductGrowthStageController::class)->defaults('stage', 'brand-compliance-manager')->name('dashboard');
    Route::get('products/{product}', [ProductGrowthStageController::class, 'edit'])->defaults('stage', 'brand-compliance-manager')->name('product.edit');
    Route::put('products/{product}', [ProductGrowthStageController::class, 'update'])->defaults('stage', 'brand-compliance-manager')->name('product.update');
});
