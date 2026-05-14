<?php

namespace Modules\CatalogManager\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Modules\CatalogManager\Support\CatalogLogger;
use Modules\CatalogManager\Support\CatalogTable;

class StorePageSpeedInsightsService
{
    public function todayMetricsForStores(Collection $stores, string $strategy = 'mobile'): Collection
    {
        if (!CatalogTable::exists('catalog_store_pagespeed_insights') || $stores->isEmpty()) {
            return collect();
        }

        return DB::table('catalog_store_pagespeed_insights')
            ->whereDate('checked_on', today())
            ->where('strategy', $strategy)
            ->whereIn('store_id', $stores->pluck('id')->filter()->values())
            ->get()
            ->keyBy('store_id');
    }

    public function runDailyForStore(object $store, string $strategy = 'mobile', bool $force = false): ?object
    {
        if (!CatalogTable::exists('catalog_store_pagespeed_insights')) {
            return null;
        }

        $existingQuery = DB::table('catalog_store_pagespeed_insights')
            ->where('store_id', $store->id)
            ->whereDate('checked_on', today())
            ->where('strategy', $strategy);

        $existing = $existingQuery->first();

        if ($existing && !$force) {
            return $existing;
        }

        if ($existing && $force) {
            $existingQuery->delete();
        }

        $url = $this->storeUrl($store);

        if (!$url) {
            return $this->insertResult($store->id, $strategy, null, [
                'status' => 'skipped',
                'error_message' => 'Loja sem dominio configurado.',
            ]);
        }

        try {
            $response = Http::timeout((int) config('catalogmanager.pagespeed.timeout', 25))
                ->retry(1, 500)
                ->get($this->pageSpeedUrl(array_filter([
                    'url' => $url,
                    'strategy' => $strategy,
                    'key' => config('catalogmanager.pagespeed.api_key'),
                ])));

            if (!$response->successful()) {
                return $this->insertResult($store->id, $strategy, $url, [
                    'status' => 'failed',
                    'error_message' => 'HTTP ' . $response->status(),
                    'raw_summary' => ['response' => $response->json()],
                ]);
            }

            return $this->insertResult($store->id, $strategy, $url, $this->parsePayload($response->json()));
        } catch (\Throwable $e) {
            CatalogLogger::exception($e, ['store_id' => $store->id, 'strategy' => $strategy]);

            return $this->insertResult($store->id, $strategy, $url, [
                'status' => 'failed',
                'error_message' => $this->sanitizeErrorMessage($e->getMessage()),
            ]);
        }
    }

    public function runDailyForStores(Collection $stores, string $strategy = 'mobile', bool $force = false): Collection
    {
        return $stores->mapWithKeys(function ($store) use ($strategy, $force) {
            return [$store->id => $this->runDailyForStore($store, $strategy, $force)];
        })->filter();
    }

    private function insertResult(int $storeId, string $strategy, ?string $url, array $data): object
    {
        $payload = array_merge([
            'store_id' => $storeId,
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
            'raw_summary' => json_encode($data['raw_summary'] ?? [], JSON_UNESCAPED_SLASHES),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('catalog_store_pagespeed_insights')->insert($payload);

        return (object) $payload;
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

    private function pageSpeedUrl(array $query): string
    {
        $categories = ['PERFORMANCE', 'ACCESSIBILITY', 'BEST_PRACTICES', 'SEO'];
        $categoryQuery = collect($categories)
            ->map(fn ($category) => 'category=' . rawurlencode($category))
            ->implode('&');

        return 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed?'
            . http_build_query($query)
            . '&'
            . $categoryQuery;
    }

    private function storeUrl(object $store): ?string
    {
        $domain = trim((string) ($store->domain ?? ''));

        if ($domain === '') {
            return null;
        }

        return str_starts_with($domain, 'http://') || str_starts_with($domain, 'https://')
            ? $domain
            : 'https://' . $domain;
    }
}
