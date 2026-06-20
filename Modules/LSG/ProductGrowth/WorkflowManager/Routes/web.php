<?php

use Illuminate\Support\Facades\Route;
use Modules\LSG\ProductGrowth\Shared\Http\Controllers\ProductGrowthStageController;

Route::middleware(['web', 'auth'])->prefix('product-growth/workflow-manager')->name('product_growth.workflow_manager.')->group(function () {
    Route::get('/', ProductGrowthStageController::class)->defaults('stage', 'workflow-manager')->name('dashboard');
    Route::get('products/{product}', [ProductGrowthStageController::class, 'edit'])->defaults('stage', 'workflow-manager')->name('product.edit');
    Route::put('products/{product}', [ProductGrowthStageController::class, 'update'])->defaults('stage', 'workflow-manager')->name('product.update');
    Route::post('products/{product}/areas/{area}/approve', [ProductGrowthStageController::class, 'reviewArea'])->defaults('stage', 'workflow-manager')->name('product.review_area');
    Route::post('products/{product}/areas/{area}/items/{item}/{decision}', [ProductGrowthStageController::class, 'reviewItem'])->defaults('stage', 'workflow-manager')->name('product.review_item');
});
