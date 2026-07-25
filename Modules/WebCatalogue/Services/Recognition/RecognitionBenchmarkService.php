<?php

namespace Modules\WebCatalogue\Services\Recognition;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\WebCatalogue\Models\RecognitionBenchmarkCall;
use Modules\WebCatalogue\Models\RecognitionBenchmarkResult;
use Modules\WebCatalogue\Models\RecognitionBenchmarkRun;
use Modules\WebCatalogue\Models\VisualRecognitionCapture;
use Modules\WebCatalogue\Models\VisualRecognitionSession;

class RecognitionBenchmarkService
{
    public function runForSession(VisualRecognitionSession $session, string $triggeredBy = 'manual'): RecognitionBenchmarkRun
    {
        $session->loadMissing(['captures', 'matches.product']);

        $capture = $session->captures
            ->where('capture_type', 'object_photo')
            ->sortByDesc('id')
            ->first();

        if (!$capture || !$capture->file_path || !Storage::disk('public')->exists($capture->file_path)) {
            return RecognitionBenchmarkRun::create([
                'id_session' => $session->id,
                'id_capture' => $capture?->id,
                'id_store' => $session->id_store,
                'expected_product_id' => $this->expectedProductId($session),
                'scenario_label' => data_get($session->metadata, 'ground_truth.scenario_label'),
                'status' => 'failed',
                'triggered_by' => $triggeredBy,
                'created_by' => auth()->id(),
                'started_at' => now(),
                'completed_at' => now(),
                'duration_ms' => 0,
                'summary' => ['error' => 'No object_photo capture available.'],
            ]);
        }

        $startedAt = microtime(true);
        $run = RecognitionBenchmarkRun::create([
            'id_session' => $session->id,
            'id_capture' => $capture->id,
            'id_store' => $session->id_store,
            'expected_product_id' => $this->expectedProductId($session),
            'scenario_label' => data_get($session->metadata, 'ground_truth.scenario_label'),
            'status' => 'running',
            'triggered_by' => $triggeredBy,
            'created_by' => auth()->id(),
            'started_at' => now(),
            'metadata' => [
                'session_status' => $session->status,
                'session_matched_product_id' => $session->id_product,
                'session_matched_score' => $session->matched_score,
                'recognition_algorithm' => data_get($session->metadata, 'recognition_algorithm'),
                'top_matches' => $this->topMatches($session),
            ],
        ]);

        foreach ($this->configuredFlows() as $flowKey => $flow) {
            $this->runFlow($run, $session, $capture, $flowKey, $flow);
        }

        $run->refresh();
        $summary = $this->buildSummary($run);

        $run->update([
            'status' => $summary['failed_flows'] > 0 ? 'completed_with_errors' : 'completed',
            'completed_at' => now(),
            'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            'summary' => $summary,
        ]);

        return $run->refresh();
    }

    public function configuredFlows(): array
    {
        return collect(config('webcatalogue.recognition.benchmark.flows', []))
            ->filter(fn ($flow) => filled($flow['base_url'] ?? null))
            ->all();
    }

    protected function runFlow(
        RecognitionBenchmarkRun $run,
        VisualRecognitionSession $session,
        VisualRecognitionCapture $capture,
        string $flowKey,
        array $flow
    ): RecognitionBenchmarkResult {
        $startedAt = microtime(true);
        $baseUrl = rtrim((string) ($flow['base_url'] ?? ''), '/');
        $token = (string) ($flow['token'] ?? '');
        $timeout = (int) config('webcatalogue.recognition.benchmark.timeout', 30);

        $result = RecognitionBenchmarkResult::create([
            'id_run' => $run->id,
            'id_session' => $session->id,
            'id_capture' => $capture->id,
            'flow_key' => $flowKey,
            'flow_label' => $flow['label'] ?? $flowKey,
            'flow_stage' => $flow['stage'] ?? null,
            'base_url' => $baseUrl,
            'status' => 'running',
        ]);

        if ($baseUrl === '') {
            $result->update([
                'status' => 'skipped',
                'error' => 'Flow base_url is empty.',
                'total_time_ms' => 0,
            ]);

            return $result;
        }

        try {
            $quality = $this->postImage($result, $capture, 'quality', $baseUrl . '/recognition/quality', $token, [], $timeout);
            $normalize = $this->postImage($result, $capture, 'normalize', $baseUrl . '/recognition/normalize', $token, [
                'mode' => (string) config('webcatalogue.recognition.opencv.normalize_mode', 'mtg_card'),
                'debug' => '1',
            ], $timeout);
            $markers = $this->postImage($result, $capture, 'markers', $baseUrl . '/recognition/markers', $token, [
                'max_markers' => (string) config('webcatalogue.recognition.visual_markers.max_markers', 250),
                'preprocess' => (string) config('webcatalogue.recognition.visual_markers.preprocess', 'clahe'),
            ], $timeout);
            $identifiers = $this->postImage($result, $capture, 'identifiers', $baseUrl . '/recognition/identifiers', $token, [], $timeout);

            $normalizedPath = $this->storeBenchmarkImage($run, $flowKey, 'normalized', $normalize['json']['normalized_image_base64'] ?? null);
            $debugPath = $this->storeBenchmarkImage($run, $flowKey, 'debug', $normalize['json']['debug_image_base64'] ?? null);

            $requiredResponses = collect([$quality, $normalize, $markers, $identifiers])
                ->reject(fn ($response) => $response['unsupported'] ?? false);
            $ok = $requiredResponses->isNotEmpty()
                && $requiredResponses->every(fn ($response) => !empty($response['ok']));
            $payload = [
                'quality' => $this->withoutImagePayload($quality['json']),
                'normalize' => $this->withoutImagePayload($normalize['json']),
                'markers' => [
                    'ok' => $markers['json']['ok'] ?? null,
                    'algorithm' => $markers['json']['algorithm'] ?? null,
                    'descriptor_type' => $markers['json']['descriptor_type'] ?? null,
                    'preprocess' => $markers['json']['preprocess'] ?? null,
                    'marker_count' => $markers['json']['marker_count'] ?? null,
                    'marker_hash' => $markers['json']['marker_hash'] ?? null,
                    'width' => $markers['json']['width'] ?? null,
                    'height' => $markers['json']['height'] ?? null,
                ],
                'identifiers' => $identifiers['json'],
            ];

            $result->update([
                'status' => $ok ? 'completed' : 'completed_with_errors',
                'ok' => $ok,
                'http_status' => $normalize['status'] ?: $quality['status'] ?: $markers['status'] ?: $identifiers['status'],
                'total_time_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                'quality_time_ms' => $quality['duration_ms'],
                'normalize_time_ms' => $normalize['duration_ms'],
                'markers_time_ms' => $markers['duration_ms'],
                'identifiers_time_ms' => $identifiers['duration_ms'],
                'quality_score' => isset($quality['json']['score']) ? (float) $quality['json']['score'] : null,
                'normalize_confidence' => isset($normalize['json']['confidence']) ? (float) $normalize['json']['confidence'] : null,
                'marker_count' => isset($markers['json']['marker_count']) ? (int) $markers['json']['marker_count'] : null,
                'identifier_count' => count((array) ($identifiers['json']['identifiers'] ?? [])),
                'normalized_path' => $normalizedPath,
                'debug_path' => $debugPath,
                'metrics' => [
                    'quality_ok' => $quality['ok'],
                    'quality_unsupported' => $quality['unsupported'] ?? false,
                    'normalize_ok' => $normalize['ok'],
                    'normalize_unsupported' => $normalize['unsupported'] ?? false,
                    'markers_ok' => $markers['ok'],
                    'markers_unsupported' => $markers['unsupported'] ?? false,
                    'identifiers_ok' => $identifiers['ok'],
                    'identifiers_unsupported' => $identifiers['unsupported'] ?? false,
                    'normalize_used_perspective' => $normalize['json']['used_perspective'] ?? null,
                    'normalize_profile' => $normalize['json']['profile'] ?? null,
                    'normalize_candidate_source' => $normalize['json']['candidate_source'] ?? null,
                    'framing_crop_applied' => $normalize['json']['framing_crop_applied'] ?? null,
                    'framing_area_ratio' => $normalize['json']['framing_area_ratio'] ?? null,
                    'framing_margins' => $normalize['json']['framing_margins'] ?? null,
                    'source_width' => $normalize['json']['source_width'] ?? $quality['json']['source_width'] ?? null,
                    'source_height' => $normalize['json']['source_height'] ?? $quality['json']['source_height'] ?? null,
                    'normalized_width' => $normalize['json']['normalized_width'] ?? null,
                    'normalized_height' => $normalize['json']['normalized_height'] ?? null,
                ],
                'payload' => $payload,
            ]);
        } catch (\Throwable $exception) {
            $result->update([
                'status' => 'failed',
                'ok' => false,
                'total_time_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                'error' => $exception->getMessage(),
                'payload' => [
                    'exception' => get_class($exception),
                ],
            ]);
        }

        return $result->refresh();
    }

    protected function postImage(
        RecognitionBenchmarkResult $result,
        VisualRecognitionCapture $capture,
        string $endpointKey,
        string $url,
        string $token,
        array $fields,
        int $timeout
    ): array
    {
        $startedAt = microtime(true);
        $path = Storage::disk('public')->path($capture->file_path);
        $requestBytes = Storage::disk('public')->size($capture->file_path);
        $started = now();
        $request = Http::timeout($timeout)->acceptJson();

        if ($token !== '') {
            $request = $request->withToken($token);
        }

        $handle = fopen($path, 'rb');

        try {
            $response = $request
                ->attach('image', $handle, basename($path))
                ->post($url, $fields);

            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
            $json = $response->json();
            $json = is_array($json) ? $json : ['raw' => $response->body()];
            $unsupported = $response->status() === 404;
            $ok = $response->ok() && !empty($json['ok']);

            $this->storeCall($result, $capture, [
                'endpoint_key' => $endpointKey,
                'url' => $url,
                'status' => $unsupported ? 'unsupported' : ($ok ? 'completed' : 'completed_with_errors'),
                'ok' => $ok,
                'http_status' => $response->status(),
                'request_bytes' => $requestBytes,
                'response_bytes' => strlen((string) $response->body()),
                'client_time_ms' => $durationMs,
                'server_time_ms' => $this->serverTimeMs($json, $response->headers()),
                'gateway_time_ms' => $this->gatewayTimeMs($response->headers()),
                'started_at' => $started,
                'completed_at' => now(),
                'headers' => $this->benchmarkHeaders($response->headers()),
                'metadata' => [
                    'fields' => $fields,
                    'service' => $json['service'] ?? null,
                    'version' => $json['version'] ?? null,
                    'provider' => $json['provider'] ?? null,
                    'mode' => $json['mode'] ?? null,
                    'profile' => $json['profile'] ?? null,
                ],
            ]);

            return [
                'ok' => $ok,
                'unsupported' => $unsupported,
                'status' => $response->status(),
                'duration_ms' => $durationMs,
                'json' => $json,
            ];
        } catch (\Throwable $exception) {
            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

            $this->storeCall($result, $capture, [
                'endpoint_key' => $endpointKey,
                'url' => $url,
                'status' => 'failed',
                'ok' => false,
                'request_bytes' => $requestBytes,
                'client_time_ms' => $durationMs,
                'started_at' => $started,
                'completed_at' => now(),
                'metadata' => ['fields' => $fields, 'exception' => get_class($exception)],
                'error' => $exception->getMessage(),
            ]);

            return [
                'ok' => false,
                'unsupported' => false,
                'status' => null,
                'duration_ms' => $durationMs,
                'json' => ['ok' => false, 'error' => $exception->getMessage()],
            ];
        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
        }
    }

    protected function storeCall(RecognitionBenchmarkResult $result, VisualRecognitionCapture $capture, array $data): void
    {
        RecognitionBenchmarkCall::create([
            'id_run' => $result->id_run,
            'id_result' => $result->id,
            'id_session' => $result->id_session,
            'id_capture' => $capture->id,
            'flow_key' => $result->flow_key,
            'flow_stage' => $result->flow_stage,
            'endpoint_key' => $data['endpoint_key'],
            'method' => 'POST',
            'url_path' => parse_url($data['url'], PHP_URL_PATH),
            'status' => $data['status'],
            'ok' => $data['ok'],
            'http_status' => $data['http_status'] ?? null,
            'request_bytes' => $data['request_bytes'] ?? null,
            'response_bytes' => $data['response_bytes'] ?? null,
            'client_time_ms' => $data['client_time_ms'] ?? null,
            'server_time_ms' => $data['server_time_ms'] ?? null,
            'gateway_time_ms' => $data['gateway_time_ms'] ?? null,
            'started_at' => $data['started_at'] ?? null,
            'completed_at' => $data['completed_at'] ?? null,
            'headers' => $data['headers'] ?? null,
            'metadata' => $data['metadata'] ?? null,
            'error' => $data['error'] ?? null,
        ]);
    }

    protected function benchmarkHeaders(array $headers): array
    {
        $allowed = [
            'server',
            'x-served-by',
            'x-response-time',
            'x-process-time',
            'x-service-version',
            'x-runtime',
            'server-timing',
            'cf-ray',
            'cf-cache-status',
            'content-type',
            'content-length',
        ];

        $normalized = [];
        foreach ($headers as $name => $values) {
            $key = strtolower((string) $name);
            if (in_array($key, $allowed, true)) {
                $normalized[$key] = implode(', ', (array) $values);
            }
        }

        return $normalized;
    }

    protected function serverTimeMs(array $json, array $headers): ?int
    {
        foreach (['server_time_ms', 'processing_time_ms', 'duration_ms', 'elapsed_ms'] as $key) {
            if (isset($json[$key]) && is_numeric($json[$key])) {
                return (int) round((float) $json[$key]);
            }
        }

        return $this->headerTimeMs($headers, ['x-process-time', 'x-runtime', 'x-response-time']);
    }

    protected function gatewayTimeMs(array $headers): ?int
    {
        return $this->headerTimeMs($headers, ['x-gateway-time', 'x-proxy-time']);
    }

    protected function headerTimeMs(array $headers, array $names): ?int
    {
        $lookup = collect($headers)->mapWithKeys(fn ($value, $key) => [strtolower((string) $key) => $value]);

        foreach ($names as $name) {
            $value = $lookup[$name][0] ?? null;
            if ($value === null || !preg_match('/([0-9]+(?:\.[0-9]+)?)/', (string) $value, $matches)) {
                continue;
            }

            $number = (float) $matches[1];

            return str_contains((string) $value, 'ms')
                ? (int) round($number)
                : (int) round($number * 1000);
        }

        return null;
    }

    protected function storeBenchmarkImage(RecognitionBenchmarkRun $run, string $flowKey, string $kind, ?string $base64): ?string
    {
        if (!$base64 || !(bool) config('webcatalogue.recognition.benchmark.store_images', true)) {
            return null;
        }

        $binary = base64_decode($base64, true);
        if ($binary === false) {
            return null;
        }

        $path = 'webcatalogue/recognition/benchmarks/run_' . $run->id . '/' . Str::slug($flowKey) . '_' . $kind . '.jpg';
        Storage::disk('public')->put($path, $binary);

        return $path;
    }

    protected function withoutImagePayload(array $payload): array
    {
        unset($payload['normalized_image_base64'], $payload['debug_image_base64']);

        return $payload;
    }

    protected function buildSummary(RecognitionBenchmarkRun $run): array
    {
        $results = $run->results()->get();
        $completed = $results->where('ok', true);

        return [
            'flows_total' => $results->count(),
            'successful_flows' => $completed->count(),
            'failed_flows' => $results->where('ok', false)->count(),
            'average_total_time_ms' => $completed->avg('total_time_ms') ? round((float) $completed->avg('total_time_ms'), 2) : null,
            'fastest_flow' => $completed->sortBy('total_time_ms')->first()?->flow_key,
            'highest_quality_flow' => $completed->sortByDesc('quality_score')->first()?->flow_key,
            'highest_confidence_flow' => $completed->sortByDesc('normalize_confidence')->first()?->flow_key,
        ];
    }

    protected function expectedProductId(VisualRecognitionSession $session): ?int
    {
        return (int) data_get($session->metadata, 'ground_truth.expected_product_id') ?: null;
    }

    protected function topMatches(VisualRecognitionSession $session): array
    {
        return $session->matches
            ->reject(fn ($match) => $match->match_provider === 'manual_review')
            ->sortBy('rank')
            ->take(5)
            ->map(fn ($match) => [
                'rank' => (int) $match->rank,
                'product_id' => (int) $match->id_product,
                'reference' => $match->product?->reference,
                'name' => strip_tags((string) ($match->product?->name ?? '')),
                'score' => (float) $match->score,
                'provider' => $match->match_provider,
                'status' => $match->status,
            ])
            ->values()
            ->all();
    }
}
