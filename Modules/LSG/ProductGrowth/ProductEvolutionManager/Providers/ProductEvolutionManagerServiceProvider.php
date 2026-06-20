<?php

namespace Modules\LSG\ProductGrowth\ProductEvolutionManager\Providers;

use Modules\LSG\ProductGrowth\Shared\Providers\ProductGrowthStageServiceProvider;

class ProductEvolutionManagerServiceProvider extends ProductGrowthStageServiceProvider
{
    protected function modulePath(): string
    {
        return dirname(__DIR__);
    }
}
