<?php

namespace Modules\CatalogManager\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\CatalogManager\Services\StorePageSpeedInsightsService;
use Modules\CatalogManager\Support\CatalogTable;

class RunStorePageSpeedInsightsCommand extends Command
{
    protected $signature = 'catalog-manager:pagespeed {--strategy=mobile} {--force}';

    protected $description = 'Run one daily Google PageSpeed Insights check per catalog store.';

    public function handle(StorePageSpeedInsightsService $service): int
    {
        if (!CatalogTable::exists('catalog_stores')) {
            $this->warn('catalog_stores table is missing.');
            return self::SUCCESS;
        }

        $stores = DB::table('catalog_stores')
            ->where('active', true)
            ->whereNotNull('domain')
            ->where('domain', '!=', '')
            ->orderBy('name')
            ->get();

        $results = $service->runDailyForStores(
            $stores,
            (string) $this->option('strategy'),
            (bool) $this->option('force')
        );

        $this->info('PageSpeed checks available today: ' . $results->count());

        $results
            ->groupBy('status')
            ->each(fn ($items, $status) => $this->line($status . ': ' . $items->count()));

        return self::SUCCESS;
    }
}
