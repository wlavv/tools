<?php

namespace Modules\LSG\SiteManager\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Modules\LSG\SiteManager\Models\Site;
use Modules\LSG\SiteManager\Services\SitePageSpeedInsightsService;

class RunSitePageSpeedInsightsCommand extends Command
{
    protected $signature = 'lsg-sites:pagespeed {--strategy=mobile} {--force}';

    protected $description = 'Run one daily Google PageSpeed Insights check per monitored LSG site.';

    public function handle(SitePageSpeedInsightsService $service): int
    {
        if (!Schema::hasTable('lsg_sites')) {
            $this->warn('lsg_sites table is missing.');
            return self::SUCCESS;
        }

        $sites = Site::query()
            ->where('status', 'active')
            ->where('monitor_pagespeed', true)
            ->where(function ($query) {
                $query->whereNotNull('domain')->orWhereNotNull('public_url');
            })
            ->orderBy('name')
            ->get();

        $results = $service->runDailyForSites(
            $sites,
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
