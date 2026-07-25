<?php

namespace Modules\WebCatalogue\Http\Controllers\Recognition;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Modules\WebCatalogue\Models\RecognitionBenchmarkCall;
use Modules\WebCatalogue\Models\RecognitionBenchmarkResult;
use Modules\WebCatalogue\Models\RecognitionBenchmarkRun;
use Modules\WebCatalogue\Models\VisualRecognitionSession;
use Modules\WebCatalogue\Services\Recognition\RecognitionBenchmarkService;

class RecognitionBenchmarkController extends Controller
{
    public function index(Request $request): View
    {
        $this->disableDefaultAction('new');

        $items = RecognitionBenchmarkRun::query()
            ->with(['session.product', 'session.store', 'capture', 'results'])
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $flowSummary = RecognitionBenchmarkResult::query()
            ->select('flow_key', 'flow_label', 'flow_stage')
            ->selectRaw('COUNT(*) as total_runs')
            ->selectRaw('SUM(CASE WHEN ok = 1 THEN 1 ELSE 0 END) as ok_runs')
            ->selectRaw('AVG(total_time_ms) as avg_total_time_ms')
            ->selectRaw('AVG(quality_score) as avg_quality_score')
            ->selectRaw('AVG(normalize_confidence) as avg_normalize_confidence')
            ->groupBy('flow_key', 'flow_label', 'flow_stage')
            ->orderBy('flow_key')
            ->get();

        return $this->view('webcatalogue::recognition.benchmarks.index', [
            'items' => $items,
            'flowSummary' => $flowSummary,
        ]);
    }

    public function show(RecognitionBenchmarkRun $run): View
    {
        $this->disableDefaultAction('new');
        $run->load(['session.store', 'session.product', 'capture', 'results.calls']);

        return $this->view('webcatalogue::recognition.benchmarks.show', [
            'item' => $run,
            'callComparison' => $this->callComparison($run),
            'groundTruth' => $this->groundTruthForRun($run),
            'accuracy' => $this->accuracyForRun($run),
        ]);
    }

    public function runSession(VisualRecognitionSession $session, RecognitionBenchmarkService $service): RedirectResponse
    {
        $run = $service->runForSession($session, 'manual');

        return redirect()
            ->route('webcatalogue.recognition.benchmarks.show', $run)
            ->with('success', 'Benchmark created for session #' . $session->id . '.');
    }

    public function syncGroundTruth(RecognitionBenchmarkRun $run): RedirectResponse
    {
        $run->load('session');
        $groundTruth = $this->groundTruthForRun($run);

        if (!$groundTruth['expected_product_id']) {
            return back()->with('error', 'This benchmark session still has no ground truth saved.');
        }

        $run->update([
            'expected_product_id' => $groundTruth['expected_product_id'],
            'scenario_label' => $groundTruth['scenario_label'],
        ]);

        return back()->with('success', 'Ground truth synced from session #' . $run->id_session . '.');
    }

    public function exportCsv(): Response
    {
        $rows = [[
            'run_id',
            'session_id',
            'capture_id',
            'store_id',
            'expected_product_id',
            'scenario_label',
            'run_status',
            'triggered_by',
            'flow_key',
            'flow_stage',
            'flow_status',
            'ok',
            'current_top_1_product_id',
            'current_top_1_reference',
            'expected_rank',
            'classification',
            'top_1_correct',
            'top_3_correct',
            'top_5_correct',
            'total_time_ms',
            'quality_time_ms',
            'normalize_time_ms',
            'markers_time_ms',
            'identifiers_time_ms',
            'quality_score',
            'normalize_confidence',
            'marker_count',
            'identifier_count',
            'base_url',
            'created_at',
        ]];

        RecognitionBenchmarkRun::query()
            ->with('results')
            ->orderBy('id')
            ->chunk(100, function ($runs) use (&$rows) {
                foreach ($runs as $run) {
                    $accuracy = $this->accuracyForRun($run);

                    foreach ($run->results as $result) {
                        $rows[] = [
                            $run->id,
                            $run->id_session,
                            $run->id_capture,
                            $run->id_store,
                            $run->expected_product_id,
                            $run->scenario_label,
                            $run->status,
                            $run->triggered_by,
                            $result->flow_key,
                            $result->flow_stage,
                            $result->status,
                            $result->ok ? 1 : 0,
                            $accuracy['current_top_1_product_id'],
                            $accuracy['current_top_1_reference'],
                            $accuracy['expected_rank'],
                            $accuracy['classification'],
                            $accuracy['top_1_correct'],
                            $accuracy['top_3_correct'],
                            $accuracy['top_5_correct'],
                            $result->total_time_ms,
                            $result->quality_time_ms,
                            $result->normalize_time_ms,
                            $result->markers_time_ms,
                            $result->identifiers_time_ms,
                            $result->quality_score,
                            $result->normalize_confidence,
                            $result->marker_count,
                            $result->identifier_count,
                            $result->base_url,
                            $result->created_at?->toDateTimeString(),
                        ];
                    }
                }
            });

        $handle = fopen('php://temp', 'w+');
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="webcatalogue-recognition-benchmarks.csv"',
        ]);
    }

    public function exportCallsCsv(): Response
    {
        $rows = [[
            'run_id',
            'result_id',
            'session_id',
            'capture_id',
            'flow_key',
            'flow_stage',
            'endpoint_key',
            'method',
            'url_path',
            'status',
            'ok',
            'http_status',
            'request_bytes',
            'response_bytes',
            'client_time_ms',
            'server_time_ms',
            'gateway_time_ms',
            'started_at',
            'completed_at',
            'served_by',
            'server',
            'cf_ray',
            'error',
        ]];

        RecognitionBenchmarkCall::query()
            ->orderBy('id')
            ->chunk(200, function ($calls) use (&$rows) {
                foreach ($calls as $call) {
                    $headers = $call->headers ?: [];
                    $rows[] = [
                        $call->id_run,
                        $call->id_result,
                        $call->id_session,
                        $call->id_capture,
                        $call->flow_key,
                        $call->flow_stage,
                        $call->endpoint_key,
                        $call->method,
                        $call->url_path,
                        $call->status,
                        $call->ok ? 1 : 0,
                        $call->http_status,
                        $call->request_bytes,
                        $call->response_bytes,
                        $call->client_time_ms,
                        $call->server_time_ms,
                        $call->gateway_time_ms,
                        $call->started_at?->toDateTimeString(),
                        $call->completed_at?->toDateTimeString(),
                        $headers['x-served-by'] ?? null,
                        $headers['server'] ?? null,
                        $headers['cf-ray'] ?? null,
                        $call->error,
                    ];
                }
            });

        $handle = fopen('php://temp', 'w+');
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="webcatalogue-recognition-benchmark-calls.csv"',
        ]);
    }

    private function accuracyForRun(RecognitionBenchmarkRun $run): array
    {
        $matches = collect(data_get($run->metadata, 'top_matches', []));
        $groundTruth = $this->groundTruthForRun($run);
        $expectedProductId = $groundTruth['expected_product_id'];
        $top1 = $matches->first();
        $rank = $expectedProductId
            ? $matches->firstWhere('product_id', $expectedProductId)['rank'] ?? null
            : null;

        return [
            'current_top_1_product_id' => $top1['product_id'] ?? null,
            'current_top_1_reference' => $top1['reference'] ?? null,
            'expected_rank' => $rank,
            'classification' => $this->classificationForRank($rank),
            'top_1_correct' => $rank !== null ? (int) ((int) $rank === 1) : null,
            'top_3_correct' => $rank !== null ? (int) ((int) $rank <= 3) : null,
            'top_5_correct' => $rank !== null ? (int) ((int) $rank <= 5) : null,
        ];
    }

    private function groundTruthForRun(RecognitionBenchmarkRun $run): array
    {
        $sessionGroundTruth = data_get($run->session?->metadata, 'ground_truth', []);

        return [
            'expected_product_id' => $run->expected_product_id
                ? (int) $run->expected_product_id
                : ((int) data_get($sessionGroundTruth, 'expected_product_id') ?: null),
            'scenario_label' => $run->scenario_label
                ?: data_get($sessionGroundTruth, 'scenario_label'),
            'expected_product_reference' => data_get($sessionGroundTruth, 'expected_product_reference'),
            'expected_product_name' => data_get($sessionGroundTruth, 'expected_product_name'),
        ];
    }

    private function callComparison(RecognitionBenchmarkRun $run): array
    {
        $flows = $run->results
            ->pluck('flow_key')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $endpoints = $run->results
            ->flatMap(fn ($result) => $result->calls)
            ->pluck('endpoint_key')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $rows = [];

        foreach ($endpoints as $endpoint) {
            $row = [
                'endpoint' => $endpoint,
                'flows' => [],
            ];

            foreach ($flows as $flow) {
                $call = $run->results
                    ->firstWhere('flow_key', $flow)
                    ?->calls
                    ->firstWhere('endpoint_key', $endpoint);

                $row['flows'][$flow] = $call ? [
                    'status' => $call->status,
                    'ok' => $call->ok,
                    'http_status' => $call->http_status,
                    'client_time_ms' => $call->client_time_ms,
                    'server_time_ms' => $call->server_time_ms,
                    'gateway_time_ms' => $call->gateway_time_ms,
                    'request_kb' => $call->request_bytes ? round($call->request_bytes / 1024, 1) : null,
                    'response_kb' => $call->response_bytes ? round($call->response_bytes / 1024, 1) : null,
                ] : null;
            }

            $rows[] = $row;
        }

        return [
            'flows' => $flows,
            'rows' => $rows,
        ];
    }

    private function classificationForRank(mixed $rank): ?string
    {
        if ($rank === null) {
            return null;
        }

        $rank = (int) $rank;

        if ($rank === 1) {
            return 'top_1_match';
        }

        if ($rank <= 3) {
            return 'failed_auto_but_in_top_3';
        }

        if ($rank <= 5) {
            return 'failed_auto_but_in_top_5';
        }

        return 'missed_top_5';
    }
}
