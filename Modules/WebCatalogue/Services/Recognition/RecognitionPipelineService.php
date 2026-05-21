<?php

namespace Modules\WebCatalogue\Services\Recognition;

use Modules\WebCatalogue\Models\RecognitionScan;
use Modules\WebCatalogue\Models\Store;
use Modules\WebCatalogue\Models\VisualRecognitionCapture;
use Modules\WebCatalogue\Models\VisualRecognitionSession;

class RecognitionPipelineService
{
    public function __construct(
        private InternalImageMatchService $legacyMatcher,
        private RecognitionQualityService $quality,
        private RecognitionScoringService $scoring,
        private RecognitionTelemetryService $telemetry
    ) {
    }

    public function matchSession(VisualRecognitionSession $session, ?Store $store = null): array
    {
        $tracker = new RecognitionTimingTracker();
        $capture = $this->latestObjectCapture($session);
        $tracker->mark('input_preparation_time_ms');

        $scan = $this->telemetry->startScan($session, $capture, [
            'product_scope' => $store ? 'store' : 'global',
        ]);

        $quality = $tracker->measure(
            'quality_check_time_ms',
            fn () => $this->quality->analyseCapture($capture)
        );

        $legacyResult = [
            'matched' => false,
            'auto_match' => null,
            'suggestions' => [],
            'debug_matches' => [],
            'message' => 'Frame rejected before heavy matching.',
        ];

        if (($quality['ok'] ?? false) && (float) ($quality['score'] ?? 0) >= (float) config('webcatalogue.recognition.pipeline_v2.quality.reject_below', 60)) {
            $legacyResult = $tracker->measure('hash_search_time_ms', function () use ($session, $store) {
                return $store
                    ? $this->legacyMatcher->matchSession($session, $store)
                    : $this->legacyMatcher->matchGlobalSession($session);
            });
        } else {
            $this->markSessionRejectedByQuality($session, $quality);
        }

        $session->load('matches.product');

        $scoring = $tracker->measure(
            'scoring_time_ms',
            fn () => $this->scoring->scoreResult($session, $legacyResult, $quality)
        );

        $candidateStats = $this->candidateStats($session, $legacyResult, $scoring);
        $scan = $this->telemetry->completeScan($scan, $quality, $scoring, $candidateStats, $this->mergeTimings($tracker->all(), $legacyResult));

        return $this->resultPayload($legacyResult, $scan);
    }

    private function latestObjectCapture(VisualRecognitionSession $session): ?VisualRecognitionCapture
    {
        $captures = $session->relationLoaded('captures') ? $session->captures : $session->captures()->latest('id')->get();

        return $captures->firstWhere('capture_type', 'object_photo') ?: $captures->first();
    }

    private function markSessionRejectedByQuality(VisualRecognitionSession $session, array $quality): void
    {
        $session->update([
            'status' => 'quality_rejected',
            'metadata' => array_merge($session->metadata ?: [], [
                'quality_rejected_at' => now()->toIso8601String(),
                'quality_score' => $quality['score'] ?? 0,
                'quality_rejection_reason' => $quality['rejection_reason'] ?: 'quality_score_below_threshold',
            ]),
        ]);
    }

    private function candidateStats(VisualRecognitionSession $session, array $legacyResult, array $scoring): array
    {
        $metadata = $session->fresh()?->metadata ?: [];
        $internalCounters = $legacyResult['internal_counters'] ?? [];

        return [
            'candidate_resources' => $internalCounters['candidate_resources'] ?? $metadata['candidate_resources'] ?? count($legacyResult['debug_matches'] ?? $legacyResult['suggestions'] ?? []),
            'fingerprinted_candidates' => $internalCounters['fingerprinted_candidates'] ?? $metadata['fingerprinted_candidates'] ?? null,
            'missing_fingerprint_candidates' => $metadata['missing_fingerprint_candidates'] ?? null,
            'marker_augmented_candidates' => $internalCounters['marker_augmented_candidates'] ?? $metadata['marker_augmented_candidates'] ?? null,
            'after_ocr' => $internalCounters['server_identifiers_detected'] ?? $metadata['identifier_candidate_count'] ?? null,
            'scored_candidates' => $internalCounters['scored_candidates'] ?? count($scoring['candidates'] ?? []),
            'after_hash_stage' => $internalCounters['after_hash_stage'] ?? null,
            'after_marker_stage' => $internalCounters['after_marker_stage'] ?? null,
            'after_verification_stage' => $internalCounters['after_verification_stage'] ?? null,
            'after_final_stage' => $internalCounters['after_final_stage'] ?? null,
            'debug_top_count' => $metadata['debug_top_count'] ?? count($legacyResult['debug_matches'] ?? []),
            'recognition_algorithm' => $metadata['recognition_algorithm'] ?? null,
        ];
    }

    private function mergeTimings(array $pipelineTimings, array $legacyResult): array
    {
        $internal = $legacyResult['internal_timings_ms'] ?? [];
        if (!is_array($internal) || empty($internal)) {
            return $pipelineTimings;
        }

        $merged = $pipelineTimings;
        foreach ([
            'input_preparation_time_ms',
            'contour_detection_time_ms',
            'perspective_correction_time_ms',
            'hash_generation_time_ms',
            'hash_search_time_ms',
            'color_comparison_time_ms',
            'ocr_time_ms',
            'orb_time_ms',
            'database_time_ms',
        ] as $key) {
            if (array_key_exists($key, $internal)) {
                $merged[$key] = (int) $internal[$key];
            }
        }

        $merged['legacy_match_total_time_ms'] = (int) ($internal['total_processing_time_ms'] ?? 0);
        $merged['scope_time_ms'] = (int) ($internal['scope_time_ms'] ?? 0);

        return $merged;
    }

    private function resultPayload(array $legacyResult, RecognitionScan $scan): array
    {
        $pipeline = $this->telemetry->output($scan);

        return array_merge($legacyResult, [
            'pipeline_v2' => $pipeline,
            'scan_id' => $pipeline['scan_id'],
            'decision_status' => $pipeline['status'],
            'decision_reason' => $pipeline['decision_reason'],
        ]);
    }
}
