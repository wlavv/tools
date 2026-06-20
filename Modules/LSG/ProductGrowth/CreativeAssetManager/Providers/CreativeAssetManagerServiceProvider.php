<?php

namespace Modules\LSG\ProductGrowth\CreativeAssetManager\Providers;

use Modules\LSG\ProductGrowth\Shared\Providers\ProductGrowthStageServiceProvider;

class CreativeAssetManagerServiceProvider extends ProductGrowthStageServiceProvider
{
    protected function modulePath(): string
    {
        return dirname(__DIR__);
    }
}
