<?php

namespace Modules\WebCatalogue\Http\Controllers\Recognition;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
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

        return $this->view('webcatalogue::recognition.benchmarks.show', [
            'item' => $run->load(['session.store', 'session.product', 'capture', 'results']),
        ]);
    }

    public function runSession(VisualRecognitionSession $session, RecognitionBenchmarkService $service): RedirectResponse
    {
        $run = $service->runForSession($session, 'manual');

        return redirect()
            ->route('webcatalogue.recognition.benchmarks.show', $run)
            ->with('success', 'Benchmark created for session #' . $session->id . '.');
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
}
