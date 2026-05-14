<?php

namespace Modules\StreamDeckAccess\Tasks;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Modules\StreamDeckAccess\Contracts\StreamDeckTask;
use Modules\StreamDeckAccess\Models\StreamDeckAccessLog;
use Modules\StreamDeckAccess\Models\StreamDeckAccessPoint;

class PagespeedInsightsTask implements StreamDeckTask
{
    public function handle(StreamDeckAccessPoint $accessPoint, StreamDeckAccessLog $log): array
    {
        $payload = $accessPoint->payload ?? [];
        $url = (string) Arr::get($payload, 'url');
        $strategy = (string) Arr::get($payload, 'strategy', 'mobile');

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('payload.url must be a valid URL.');
        }

        if (! in_array($strategy, ['mobile', 'desktop'], true)) {
            throw new InvalidArgumentException('payload.strategy must be mobile or desktop.');
        }

        $query = [
            'url' => $url,
            'strategy' => $strategy,
        ];

        if (config('streamdeck-access.google_pagespeed_api_key')) {
            $query['key'] = config('streamdeck-access.google_pagespeed_api_key');
        }

        $response = Http::timeout(120)
            ->retry(2, 1000)
            ->get('https://www.googleapis.com/pagespeedonline/v5/runPagespeed', $query);

        $response->throw();

        $json = $response->json();
        $score = data_get($json, 'lighthouseResult.categories.performance.score');

        return [
            'url' => $url,
            'strategy' => $strategy,
            'performance_score' => $score === null ? null : (int) round(((float) $score) * 100),
            'first_contentful_paint' => data_get($json, 'lighthouseResult.audits.first-contentful-paint.displayValue'),
            'largest_contentful_paint' => data_get($json, 'lighthouseResult.audits.largest-contentful-paint.displayValue'),
            'cumulative_layout_shift' => data_get($json, 'lighthouseResult.audits.cumulative-layout-shift.displayValue'),
            'analyzed_at' => now()->toISOString(),
        ];
    }
}
