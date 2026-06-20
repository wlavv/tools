<?php

namespace Modules\LSG\SiteManager\Http\Controllers;

use Illuminate\Support\Facades\Schema;
use Modules\LSG\SiteManager\Models\Site;
use Modules\LSG\SiteManager\Services\SitePageSpeedInsightsService;

class SiteManagerDashboardController extends BaseSiteManagerController
{
    public function __invoke(SitePageSpeedInsightsService $pageSpeed)
    {
        $sites = Schema::hasTable('lsg_sites')
            ? Site::query()->orderBy('site_type')->orderBy('name')->get()
            : collect();

        return $this->view('site-manager::dashboard.index', [
            'sites' => $sites,
            'stats' => [
                'sites' => $sites->count(),
                'stores' => $sites->where('site_type', 'store')->count(),
                'services' => $sites->where('site_type', 'service')->count(),
                'presentation' => $sites->where('site_type', 'presentation')->count(),
            ],
            'pageSpeedMetricsByStrategy' => [
                'mobile' => $pageSpeed->todayMetricsForSites($sites, 'mobile'),
                'desktop' => $pageSpeed->todayMetricsForSites($sites, 'desktop'),
            ],
        ]);
    }
}
