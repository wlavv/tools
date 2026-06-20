<?php

namespace Modules\LSG\SiteManager\Services;

use Illuminate\Support\Collection;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Modules\LSG\SiteManager\Models\Site;
use Modules\LSG\SiteManager\Models\SitePageSpeedRun;

class SitePageSpeedInsightsService
{
    public function todayMetricsForSites(Collection $sites, string $strategy = 'mobile'): Collection
    {
        if (!Schema::hasTable('lsg_site_pagespeed_runs') || $sites->isEmpty()) {
            return collect();
        }

        return SitePageSpeedRun::query()
            ->whereDate('checked_on', today())
            ->where('strategy', $strategy)
            ->whereIn('site_id', $sites->pluck('id')->filter()->values())
            ->get()
            ->keyBy('site_id');
    }

    public function runDailyForSite(Site $site, string $strategy = 'mobile', bool $force = false): ?SitePageSpeedRun
    {
        if (!Schema::hasTable('lsg_site_pagespeed_runs')) {
            return null;
        }

        $query = SitePageSpeedRun::query()
            ->where('site_id', $site->id)
            ->whereDate('checked_on', today())
            ->where('strategy', $strategy);

        $existing = $query->first();

        if ($existing && !$force) {
            return $existing;
        }

        if ($existing && $force) {
            $existing->delete();
        }

        $url = $site->resolved_url;

        if (!$url) {
            return $this->createRun($site, $strategy, null, [
                'status' => 'skipped',
                'error_message' => 'Site sem dominio ou URL configurado.',
            ]);
        }

        try {
            $response = Http::timeout((int) config('site-manager.pagespeed.timeout', 25))
                ->get($this->pageSpeedUrl(array_filter([
                    'url' => $url,
                    'strategy' => $strategy,
                    'key' => config('site-manager.pagespeed.api_key'),
                ])));

            if (!$response->successful()) {
                return $this->createRun($site, $strategy, $url, $this->failedResponseData($response));
            }

            return $this->createRun($site, $strategy, $url, $this->parsePayload($response->json()));
        } catch (\Throwable $e) {
            return $this->createRun($site, $strategy, $url, [
                'status' => 'failed',
                'error_message' => $this->sanitizeErrorMessage($e->getMessage()),
            ]);
        }
    }

    public function runDailyForSites(Collection $sites, string $strategy = 'mobile', bool $force = false): Collection
    {
        $results = collect();
        $delayMs = (int) config('site-manager.pagespeed.between_requests_ms', 1500);

        foreach ($sites as $site) {
            $run = $this->runDailyForSite($site, $strategy, $force);

            if ($run) {
                $results->put($site->id, $run);
            }

            if ($this->isRateLimited($run)) {
                break;
            }

            if ($delayMs > 0) {
                usleep($delayMs * 1000);
            }
        }

        return $results;
    }

    public function isRateLimited(?SitePageSpeedRun $run): bool
    {
        return (int) data_get($run?->raw_summary, 'http_status') === 429;
    }

    private function createRun(Site $site, string $strategy, ?string $url, array $data): SitePageSpeedRun
    {
        return SitePageSpeedRun::create([
            'site_id' => $site->id,
            'checked_on' => today()->toDateString(),
            'strategy' => $strategy,
            'url' => $url,
            'status' => $data['status'] ?? 'completed',
            'performance_score' => $data['performance_score'] ?? null,
            'accessibility_score' => $data['accessibility_score'] ?? null,
            'best_practices_score' => $data['best_practices_score'] ?? null,
            'seo_score' => $data['seo_score'] ?? null,
            'first_contentful_paint_ms' => $data['first_contentful_paint_ms'] ?? null,
            'largest_contentful_paint_ms' => $data['largest_contentful_paint_ms'] ?? null,
            'total_blocking_time_ms' => $data['total_blocking_time_ms'] ?? null,
            'cumulative_layout_shift' => $data['cumulative_layout_shift'] ?? null,
            'speed_index_ms' => $data['speed_index_ms'] ?? null,
            'error_message' => $data['error_message'] ?? null,
            'raw_summary' => $data['raw_summary'] ?? [],
        ]);
    }

    private function parsePayload(array $payload): array
    {
        $lighthouse = $payload['lighthouseResult'] ?? [];
        $categories = $lighthouse['categories'] ?? [];
        $audits = $lighthouse['audits'] ?? [];

        return [
            'status' => 'completed',
            'performance_score' => $this->categoryScore($categories, 'performance'),
            'accessibility_score' => $this->categoryScore($categories, 'accessibility'),
            'best_practices_score' => $this->categoryScore($categories, 'best-practices'),
            'seo_score' => $this->categoryScore($categories, 'seo'),
            'first_contentful_paint_ms' => $this->auditMs($audits, 'first-contentful-paint'),
            'largest_contentful_paint_ms' => $this->auditMs($audits, 'largest-contentful-paint'),
            'total_blocking_time_ms' => $this->auditMs($audits, 'total-blocking-time'),
            'cumulative_layout_shift' => isset($audits['cumulative-layout-shift']['numericValue'])
                ? (int) round(((float) $audits['cumulative-layout-shift']['numericValue']) * 1000)
                : null,
            'speed_index_ms' => $this->auditMs($audits, 'speed-index'),
            'raw_summary' => [
                'fetchTime' => $lighthouse['fetchTime'] ?? null,
                'requestedUrl' => $lighthouse['requestedUrl'] ?? null,
                'finalUrl' => $lighthouse['finalUrl'] ?? null,
            ],
        ];
    }

    private function categoryScore(array $categories, string $key): ?int
    {
        return isset($categories[$key]['score'])
            ? (int) round(((float) $categories[$key]['score']) * 100)
            : null;
    }

    private function auditMs(array $audits, string $key): ?int
    {
        return isset($audits[$key]['numericValue'])
            ? (int) round((float) $audits[$key]['numericValue'])
            : null;
    }

    private function sanitizeErrorMessage(string $message): string
    {
        return (string) preg_replace('/([?&]key=)[^&\s]+/', '$1[redacted]', $message);
    }

    private function failedResponseData(Response $response): array
    {
        $status = $response->status();
        $retryAfter = $response->header('Retry-After');
        $payload = $response->json();

        $message = $status === 429
            ? 'Limite de pedidos PageSpeed atingido (HTTP 429). Aguarda alguns minutos ou aumenta a quota da GOOGLE_PAGESPEED_API_KEY.'
            : 'PageSpeed devolveu HTTP ' . $status . '.';

        if ($status === 429 && $retryAfter) {
            $message .= ' Retry-After: ' . $retryAfter . 's.';
        }

        return [
            'status' => 'failed',
            'error_message' => $message,
            'raw_summary' => [
                'http_status' => $status,
                'retry_after' => $retryAfter,
                'response' => $payload,
            ],
        ];
    }

    private function pageSpeedUrl(array $query): string
    {
        $categoryQuery = collect(['PERFORMANCE', 'ACCESSIBILITY', 'BEST_PRACTICES', 'SEO'])
            ->map(fn (string $category) => 'category=' . rawurlencode($category))
            ->implode('&');

        return 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed?'
            . http_build_query($query)
            . '&'
            . $categoryQuery;
    }
}
