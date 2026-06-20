<?php

namespace Modules\WebCatalogue\Services\Recognition;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
            $quality = $this->postImage($baseUrl . '/recognition/quality', $capture, $token, [], $timeout);
            $normalize = $this->postImage($baseUrl . '/recognition/normalize', $capture, $token, [
                'mode' => (string) config('webcatalogue.recognition.opencv.normalize_mode', 'mtg_card'),
                'debug' => '1',
            ], $timeout);
            $markers = $this->postImage($baseUrl . '/recognition/markers', $capture, $token, [
                'max_markers' => (string) config('webcatalogue.recognition.visual_markers.max_markers', 250),
                'preprocess' => (string) config('webcatalogue.recognition.visual_markers.preprocess', 'clahe'),
            ], $timeout);
            $identifiers = $this->postImage($baseUrl . '/recognition/identifiers', $capture, $token, [], $timeout);

            $normalizedPath = $this->storeBenchmarkImage($run, $flowKey, 'normalized', $normalize['json']['normalized_image_base64'] ?? null);
            $debugPath = $this->storeBenchmarkImage($run, $flowKey, 'debug', $normalize['json']['debug_image_base64'] ?? null);

            $ok = $quality['ok'] && $normalize['ok'] && $markers['ok'] && $identifiers['ok'];
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
                    'normalize_ok' => $normalize['ok'],
                    'markers_ok' => $markers['ok'],
                    'identifiers_ok' => $identifiers['ok'],
                    'normalize_used_perspective' => $normalize['json']['used_perspective'] ?? null,
                    'normalize_profile' => $normalize['json']['profile'] ?? null,
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

    protected function postImage(string $url, VisualRecognitionCapture $capture, string $token, array $fields, int $timeout): array
    {
        $startedAt = microtime(true);
        $path = Storage::disk('public')->path($capture->file_path);
        $request = Http::timeout($timeout)->acceptJson();

        if ($token !== '') {
            $request = $request->withToken($token);
        }

        $response = $request
            ->attach('image', fopen($path, 'rb'), basename($path))
            ->post($url, $fields);

        $json = $response->json();

        return [
            'ok' => $response->ok() && is_array($json) && !empty($json['ok']),
            'status' => $response->status(),
            'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            'json' => is_array($json) ? $json : ['raw' => $response->body()],
        ];
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
        return (int) (data_get($session->metadata, 'ground_truth.expected_product_id') ?: $session->id_product) ?: null;
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
