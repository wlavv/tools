<?php

use Illuminate\Support\Facades\Route;
use Modules\ProductImageReview\Http\Controllers\ProductImageReviewController;

Route::middleware(['web', 'auth'])
    ->prefix('marketing/product-image-review')
    ->name('product-image-review.')
    ->group(function (): void {
        Route::get('/', [ProductImageReviewController::class, 'index'])->name('index');
        Route::get('/products', [ProductImageReviewController::class, 'products'])->name('products');
    });
