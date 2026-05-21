<?php

namespace Modules\WebCatalogue\Http\Controllers\Recognition;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\WebCatalogue\Models\BrandProspectLead;
use Modules\WebCatalogue\Models\FingerprintRebuildLog;
use Modules\WebCatalogue\Models\RecognitionScan;
use Modules\WebCatalogue\Models\RecognitionScanCandidate;
use Modules\WebCatalogue\Models\RecognitionScanTiming;
use Modules\WebCatalogue\Models\Resource;
use Modules\WebCatalogue\Models\ResourceFingerprint;
use Modules\WebCatalogue\Models\Store;
use Modules\WebCatalogue\Models\UnmatchedProductLead;
use Modules\WebCatalogue\Models\VisualRecognitionCapture;
use Modules\WebCatalogue\Models\VisualRecognitionMatch;
use Modules\WebCatalogue\Models\VisualRecognitionSession;

class RecognitionDashboardController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = $this->resolvePageTitle();
    }

    public function index(): View
    {
        $reviewStatuses = ['suggestions_found', 'no_match', 'unmatched_lead_created', 'capture_missing', 'capture_failed', 'capture_received', 'matching', 'match_failed', 'started'];
        $totalSessions = VisualRecognitionSession::count();
        $matchedSessions = VisualRecognitionSession::whereIn('status', ['matched', 'manual_matched'])->count();
        $actionNeededSessions = VisualRecognitionSession::whereIn('status', $reviewStatuses)->count();
        $candidateImagesCount = Resource::whereNotNull('id_product')->whereIn('resource_type', ['image', 'gallery_image', 'thumbnail', 'cover'])->count();
        $fingerprintsCount = ResourceFingerprint::count();
        $coveragePercent = $candidateImagesCount > 0 ? (int) round(min(100, ($fingerprintsCount / $candidateImagesCount) * 100)) : 0;
        $averageScore = (float) VisualRecognitionSession::whereNotNull('matched_score')->avg('matched_score');

        return $this->view('webcatalogue::recognition.index', [
            'sessionsCount' => $totalSessions,
            'todaySessionsCount' => VisualRecognitionSession::whereDate('created_at', now()->toDateString())->count(),
            'newLeadsCount' => UnmatchedProductLead::where('status', 'new')->count(),
            'brandProspectsCount' => BrandProspectLead::count(),
            'fingerprintsCount' => $fingerprintsCount,
            'candidateImagesCount' => $candidateImagesCount,
            'actionNeededSessionsCount' => $actionNeededSessions,
            'matchRate' => $totalSessions > 0 ? (int) round(($matchedSessions / $totalSessions) * 100) : 0,
            'datasetCoveragePercent' => $coveragePercent,
            'averageScore' => round($averageScore, 1),
            'statusCounts' => VisualRecognitionSession::query()
                ->select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->orderByDesc('total')
                ->get()
                ->map(fn ($row) => [
                    'status' => (string) $row->status,
                    'total' => (int) $row->total,
                    'group' => $this->sessionGroupForStatus((string) $row->status),
                ]),
            'scoreBands' => [
                '80-100' => VisualRecognitionSession::where('matched_score', '>=', 80)->count(),
                '60-79' => VisualRecognitionSession::whereBetween('matched_score', [60, 79.9999])->count(),
                '40-59' => VisualRecognitionSession::whereBetween('matched_score', [40, 59.9999])->count(),
                '0-39' => VisualRecognitionSession::whereNotNull('matched_score')->where('matched_score', '<', 40)->count(),
            ],
            'storeDatasetRows' => Store::query()
                ->with('latestFingerprintRebuildLog')
                ->withCount([
                    'resources as candidate_images_count' => fn ($query) => $query->whereNotNull('id_product')->whereIn('resource_type', ['image', 'gallery_image', 'thumbnail', 'cover']),
                    'resources as fingerprinted_images_count' => fn ($query) => $query
                        ->whereNotNull('id_product')
                        ->whereIn('resource_type', ['image', 'gallery_image', 'thumbnail', 'cover'])
                        ->whereHas('fingerprints'),
                ])
                ->orderBy('name')
                ->limit(8)
                ->get(),
            'recentRebuildLogs' => FingerprintRebuildLog::with('store')->latest('id')->limit(8)->get(),
            'recentSessions' => VisualRecognitionSession::with([
                'store',
                'captures' => fn ($query) => $query->latest('id'),
            ])
                ->whereIn('status', $reviewStatuses)
                ->latest()
                ->limit(8)
                ->get(),
            'recentLeads' => UnmatchedProductLead::with('store')->latest()->limit(8)->get(),
        ]);
    }

    public function pipeline(): View
    {
        return $this->view('webcatalogue::recognition.pipeline', $this->pipelineMetrics());
    }

    public function pipelineSummary(): JsonResponse
    {
        return response()->json($this->pipelineMetrics());
    }

    public function flushPipeline(): RedirectResponse
    {
        DB::transaction(function (): void {
            RecognitionScanTiming::query()->delete();
            RecognitionScanCandidate::query()->delete();
            RecognitionScan::query()->delete();
            VisualRecognitionMatch::query()->delete();
            VisualRecognitionCapture::query()->delete();
            VisualRecognitionSession::query()->delete();
        });

        return redirect()
            ->route('webcatalogue.recognition.pipeline.index')
            ->with('success', 'Recognition sessions and pipeline metrics were cleared.');
    }

    private function sessionGroupForStatus(string $status): string
    {
        return match ($status) {
            'suggestions_found' => 'suggestions_found',
            'started' => 'started',
            'manual_matched' => 'manual_matched',
            'matched' => 'matched',
            'product_created', 'manual_lead_created' => 'converted',
            'no_match', 'unmatched_lead_created', 'capture_missing', 'match_failed' => 'review',
            default => 'all',
        };
    }

    private function pipelineMetrics(): array
    {
        $totalScans = RecognitionScan::count();
        $acceptedScans = RecognitionScan::where('status', 'accepted')->count();
        $rejectedScans = RecognitionScan::where('status', 'rejected')->count();
        $ambiguousScans = RecognitionScan::where('status', 'ambiguous')->count();
        $timings = RecognitionScanTiming::query()
            ->whereNotNull('total_processing_time_ms')
            ->latest('id')
            ->limit(5000)
            ->pluck('total_processing_time_ms')
            ->map(fn ($value) => (int) $value)
            ->sort()
            ->values();

        $withGroundTruth = RecognitionScan::whereNotNull('expected_product_id');
        $groundTruthCount = (clone $withGroundTruth)->count();

        return [
            'totalScans' => $totalScans,
            'acceptedScans' => $acceptedScans,
            'rejectedScans' => $rejectedScans,
            'ambiguousScans' => $ambiguousScans,
            'acceptanceRate' => $totalScans > 0 ? round(($acceptedScans / $totalScans) * 100, 1) : 0,
            'rejectionRate' => $totalScans > 0 ? round(($rejectedScans / $totalScans) * 100, 1) : 0,
            'averageResponseTime' => round((float) RecognitionScanTiming::avg('total_processing_time_ms'), 1),
            'medianResponseTime' => $this->percentile($timings, 50),
            'p95ResponseTime' => $this->percentile($timings, 95),
            'p99ResponseTime' => $this->percentile($timings, 99),
            'averageQualityScore' => round((float) RecognitionScan::avg('quality_score'), 1),
            'averageFinalScore' => round((float) RecognitionScan::avg('score_final'), 1),
            'averageInputPreparationTime' => round((float) RecognitionScanTiming::avg('input_preparation_time_ms'), 1),
            'averagePerspectiveTime' => round((float) RecognitionScanTiming::avg('perspective_correction_time_ms'), 1),
            'averageHashGenerationTime' => round((float) RecognitionScanTiming::avg('hash_generation_time_ms'), 1),
            'averageHashSearchTime' => round((float) RecognitionScanTiming::avg('hash_search_time_ms'), 1),
            'averageHashTime' => $this->averageCombinedTiming(['hash_generation_time_ms', 'hash_search_time_ms']),
            'averageOcrTime' => round((float) RecognitionScanTiming::avg('ocr_time_ms'), 1),
            'averageOrbTime' => round((float) RecognitionScanTiming::avg('orb_time_ms'), 1),
            'averageScoringTime' => round((float) RecognitionScanTiming::avg('scoring_time_ms'), 1),
            'averageDatabaseTime' => round((float) RecognitionScanTiming::avg('database_time_ms'), 1),
            'groundTruthCount' => $groundTruthCount,
            'top1Accuracy' => $groundTruthCount > 0 ? round(((clone $withGroundTruth)->where('top_1_correct', true)->count() / $groundTruthCount) * 100, 1) : null,
            'top3Accuracy' => $groundTruthCount > 0 ? round(((clone $withGroundTruth)->where('top_3_correct', true)->count() / $groundTruthCount) * 100, 1) : null,
            'falsePositiveRate' => $groundTruthCount > 0 ? round(((clone $withGroundTruth)->where('false_positive', true)->count() / $groundTruthCount) * 100, 1) : null,
            'falseNegativeRate' => $groundTruthCount > 0 ? round(((clone $withGroundTruth)->where('false_negative', true)->count() / $groundTruthCount) * 100, 1) : null,
            'scansByScope' => RecognitionScan::query()
                ->select('product_scope', DB::raw('count(*) as total'))
                ->groupBy('product_scope')
                ->orderByDesc('total')
                ->get(),
            'scansByProfile' => RecognitionScan::query()
                ->select('recognition_profile', DB::raw('count(*) as total'))
                ->groupBy('recognition_profile')
                ->orderByDesc('total')
                ->get(),
            'recentScans' => RecognitionScan::with(['topProduct', 'timings', 'candidates.product'])
                ->latest('id')
                ->limit(20)
                ->get(),
            'performanceTargets' => config('webcatalogue.recognition.pipeline_v2.performance_targets', []),
        ];
    }

    private function percentile($values, int $percentile): ?int
    {
        $count = $values->count();
        if ($count === 0) {
            return null;
        }

        $index = (int) ceil(($percentile / 100) * $count) - 1;

        return (int) $values->get(max(0, min($count - 1, $index)));
    }

    private function averageCombinedTiming(array $columns): float
    {
        $expression = collect($columns)
            ->map(fn (string $column) => 'coalesce(' . $column . ', 0)')
            ->implode(' + ');

        return round((float) RecognitionScanTiming::query()
            ->selectRaw('avg(' . $expression . ') as aggregate')
            ->value('aggregate'), 1);
    }
}
