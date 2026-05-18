<?php

namespace Modules\WebCatalogue\Http\Controllers\Recognition;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\WebCatalogue\Models\BrandProspectLead;
use Modules\WebCatalogue\Models\FingerprintRebuildLog;
use Modules\WebCatalogue\Models\Resource;
use Modules\WebCatalogue\Models\ResourceFingerprint;
use Modules\WebCatalogue\Models\Store;
use Modules\WebCatalogue\Models\UnmatchedProductLead;
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
}
