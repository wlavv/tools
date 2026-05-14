<?php

namespace Modules\StreamDeckAccess\Tasks;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Modules\StreamDeckAccess\Contracts\StreamDeckTask;
use Modules\StreamDeckAccess\Models\StreamDeckAccessLog;
use Modules\StreamDeckAccess\Models\StreamDeckAccessPoint;
use Throwable;

class CheckExternalLinksTask implements StreamDeckTask
{
    public function handle(StreamDeckAccessPoint $accessPoint, StreamDeckAccessLog $log): array
    {
        $payload = $accessPoint->payload ?? [];
        $urls = Arr::wrap(Arr::get($payload, 'urls', []));
        $maxUrls = max(1, (int) config('streamdeck-access.link_checker_max_urls', 25));
        $timeout = max(1, (int) config('streamdeck-access.link_checker_timeout', 10));
        $urls = array_slice(array_values(array_unique(array_filter($urls, 'is_string'))), 0, $maxUrls);

        if ($urls === []) {
            throw new InvalidArgumentException('payload.urls must contain at least one URL.');
        }

        $results = [];

        foreach ($urls as $url) {
            $results[] = $this->checkUrl($url, $timeout);
        }

        $failed = array_values(array_filter($results, fn (array $result): bool => ! $result['ok']));

        return [
            'checked' => count($results),
            'failed' => count($failed),
            'results' => $results,
            'checked_at' => now()->toISOString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function checkUrl(string $url, int $timeout): array
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return [
                'url' => $url,
                'ok' => false,
                'error' => 'Invalid URL.',
            ];
        }

        $host = parse_url($url, PHP_URL_HOST);

        if (! is_string($host) || ! $this->hostIsAllowed($host)) {
            return [
                'url' => $url,
                'ok' => false,
                'error' => 'Host not allowed by link_checker_allowed_hosts.',
            ];
        }

        try {
            $response = Http::timeout($timeout)
                ->withoutRedirecting()
                ->head($url);

            if ($response->status() === 405) {
                $response = Http::timeout($timeout)
                    ->withoutRedirecting()
                    ->get($url);
            }

            return [
                'url' => $url,
                'ok' => $response->status() >= 200 && $response->status() < 400,
                'status' => $response->status(),
            ];
        } catch (Throwable $exception) {
            return [
                'url' => $url,
                'ok' => false,
                'error' => $exception->getMessage(),
            ];
        }
    }

    protected function hostIsAllowed(string $host): bool
    {
        $host = strtolower($host);
        $allowedHosts = config('streamdeck-access.link_checker_allowed_hosts', []);

        foreach ($allowedHosts as $allowedHost) {
            $allowedHost = strtolower(trim((string) $allowedHost));

            if ($allowedHost === '') {
                continue;
            }

            if ($host === $allowedHost || str_ends_with($host, '.' . $allowedHost)) {
                return true;
            }
        }

        return false;
    }
}
