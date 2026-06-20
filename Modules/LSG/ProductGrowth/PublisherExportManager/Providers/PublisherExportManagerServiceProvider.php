<?php

namespace Modules\LSG\ProductGrowth\PublisherExportManager\Providers;

use Modules\LSG\ProductGrowth\Shared\Providers\ProductGrowthStageServiceProvider;

class PublisherExportManagerServiceProvider extends ProductGrowthStageServiceProvider
{
    protected function modulePath(): string
    {
        return dirname(__DIR__);
    }
}
