<?php

namespace Modules\CatalogManager\Console;

class RunStorePageSpeedInsightsCatalogueAliasCommand extends RunStorePageSpeedInsightsCommand
{
    protected $signature = 'catalogue-manager:pagespeed {--strategy=mobile} {--force}';

    protected $description = 'Alias for catalog-manager:pagespeed.';
}
