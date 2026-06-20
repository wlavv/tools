<?php

namespace Modules\LSG\ProductGrowth\WebCataloguePremiumLayer\Providers;

use Modules\LSG\ProductGrowth\Shared\Providers\ProductGrowthStageServiceProvider;

class WebCataloguePremiumLayerServiceProvider extends ProductGrowthStageServiceProvider
{
    protected function modulePath(): string
    {
        return dirname(__DIR__);
    }
}
