<?php

namespace Modules\LSG\ProductGrowth\PrestaShopBridge\Providers;

use Modules\LSG\ProductGrowth\Shared\Providers\ProductGrowthStageServiceProvider;

class PrestaShopBridgeServiceProvider extends ProductGrowthStageServiceProvider
{
    protected function modulePath(): string
    {
        return dirname(__DIR__);
    }
}
