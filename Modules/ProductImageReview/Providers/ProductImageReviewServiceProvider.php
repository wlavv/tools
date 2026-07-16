<?php

namespace Modules\ProductImageReview\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\ProductImageReview\Services\ProductImageReviewService;

class ProductImageReviewServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'product-image-review');
        $this->app->singleton(ProductImageReviewService::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'product-image-review');
    }
}
