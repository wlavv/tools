<?php

namespace Modules\LSG\ProductGrowth\LogisticsManager\Providers;

use Modules\LSG\ProductGrowth\Shared\Providers\ProductGrowthStageServiceProvider;

class LogisticsManagerServiceProvider extends ProductGrowthStageServiceProvider
{
    protected function modulePath(): string
    {
        return dirname(__DIR__);
    }
}
