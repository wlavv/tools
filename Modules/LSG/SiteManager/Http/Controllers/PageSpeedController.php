<?php

namespace Modules\LSG\SiteManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\LSG\SiteManager\Models\Site;
use Modules\LSG\SiteManager\Services\SitePageSpeedInsightsService;

class PageSpeedController extends Controller
{
    public function run(Request $request, Site $site, SitePageSpeedInsightsService $pageSpeed): RedirectResponse
    {
        $results = collect();

        foreach (['mobile', 'desktop'] as $strategy) {
            $run = $pageSpeed->runDailyForSite($site, $strategy, $request->boolean('force'));

            if ($run) {
                $results->push($run);
            }

            if ($pageSpeed->isRateLimited($run)) {
                return back()->with('warning', $run->error_message);
            }
        }

        $failed = $results->firstWhere('status', 'failed');
        if ($failed) {
            return back()->with('warning', $failed->error_message ?: 'PageSpeed Insights devolveu erro.');
        }

        return back()->with('success', 'PageSpeed Insights atualizado.');
    }
}
