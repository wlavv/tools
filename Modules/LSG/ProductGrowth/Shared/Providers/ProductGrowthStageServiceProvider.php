<?php

namespace Modules\LSG\ProductGrowth\Shared\Providers;

use Illuminate\Support\ServiceProvider;

abstract class ProductGrowthStageServiceProvider extends ServiceProvider
{
    abstract protected function modulePath(): string;

    public function boot(): void
    {
        $this->loadRoutesFrom($this->modulePath() . '/Routes/web.php');
        $this->loadViewsFrom(dirname(__DIR__) . '/Resources/views', 'product-growth-stage');
    }
}
