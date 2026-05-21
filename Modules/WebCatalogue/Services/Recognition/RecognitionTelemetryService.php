<?php

namespace Modules\WebCatalogue\Services\Recognition;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\WebCatalogue\Models\RecognitionScan;
use Modules\WebCatalogue\Models\RecognitionScanCandidate;
use Modules\WebCatalogue\Models\RecognitionScanTiming;
use Modules\WebCatalogue\Models\VisualRecognitionCapture;
use Modules\WebCatalogue\Models\VisualRecognitionSession;

class RecognitionTelemetryService
{
    public function startScan(VisualRecognitionSession $session, ?VisualRecognitionCapture $capture, array $context = []): RecognitionScan
    {
        return RecognitionScan::create([
            'scan_uuid' => (string) Str::uuid(),
            'id_session' => $session->id,
            'id_capture' => $capture?->id,
            'id_store' => $session->id_store,
            'id_catalogue' => $session->id_catalogue,
            'recognition_profile' => (string) config('webcatalogue.recognition.pipeline_v2.profile', 'default'),
            'product_scope' => $context['product_scope'] ?? ($session->id_store ? 'store' : 'global'),
            'status' => 'started',
            'metadata' => [
                'pipeline_version' => 'v2',
                'debug' => (bool) config('webcatalogue.recognition.pipeline_v2.debug', false),
                'created_from' => $context['created_from'] ?? 'front_match',
            ],
        ]);
    }

    public function completeScan(RecognitionScan $scan, array $quality, array $scoring, array $candidateStats, array $timings): RecognitionScan
    {
        $databaseStartedAt = microtime(true);
        $top = $scoring['top_1_candidate'] ?? null;
        $groundTruth = $scoring['ground_truth'] ?? [];

        DB::transaction(function () use ($scan, $quality, $scoring, $candidateStats, $top, $groundTruth, &$timings): void {
            $scan->update([
                'status' => $scoring['status'] ?? 'rejected',
                'decision_reason' => $scoring['decision_reason'] ?? null,
                'rejection_reason' => $scoring['rejection_reason'] ?? null,
                'input_image_width' => $quality['width'] ?? null,
                'input_image_height' => $quality['height'] ?? null,
                'input_image_size' => $quality['size'] ?? null,
                'quality_score' => $quality['score'] ?? null,
                'blur_score' => $quality['blur'] ?? null,
                'brightness_score' => $quality['brightness'] ?? null,
                'glare_score' => $quality['glare'] ?? null,
                'card_area_score' => $quality['card_area'] ?? null,
                'object_area_score' => $quality['object_area'] ?? null,
                'perspective_score' => $quality['perspective'] ?? null,
                'number_of_candidates_initial' => $candidateStats['candidate_resources'] ?? null,
                'number_of_candidates_after_hash' => $candidateStats['after_hash_stage'] ?? $candidateStats['fingerprinted_candidates'] ?? null,
                'number_of_candidates_after_ocr' => $candidateStats['after_ocr'] ?? null,
                'number_of_candidates_after_orb' => $candidateStats['after_marker_stage'] ?? $candidateStats['marker_augmented_candidates'] ?? null,
                'top_1_product_id' => $top['product_id'] ?? null,
                'top_3_candidates' => $scoring['top_3_candidates'] ?? [],
                'score_final' => $scoring['score_final'] ?? null,
                'comparator_scores' => $scoring['comparator_scores'] ?? [],
                'expected_product_id' => $groundTruth['expected_product_id'] ?? null,
                'expected_card_id' => $groundTruth['expected_card_id'] ?? null,
                'scenario_label' => $groundTruth['scenario_label'] ?? null,
                'top_1_correct' => $groundTruth['top_1_correct'] ?? null,
                'top_3_correct' => $groundTruth['top_3_correct'] ?? null,
                'false_positive' => $groundTruth['false_positive'] ?? null,
                'false_negative' => $groundTruth['false_negative'] ?? null,
                'metadata' => array_replace_recursive($scan->metadata ?: [], [
                    'candidate_stats' => $candidateStats,
                    'quality_modifier' => $scoring['quality_modifier'] ?? null,
                ]),
                'completed_at' => now(),
            ]);

            RecognitionScanCandidate::where('id_scan', $scan->id)->delete();
            foreach ((array) ($scoring['candidates'] ?? []) as $candidate) {
                RecognitionScanCandidate::create([
                    'id_scan' => $scan->id,
                    'id_product' => $candidate['product_id'] ?? null,
                    'id_resource' => $candidate['resource_id'] ?? null,
                    'rank' => $candidate['rank'] ?? 1,
                    'score_final' => $candidate['score_final'] ?? null,
                    'weighted_score' => $candidate['weighted_score'] ?? null,
                    'quality_modifier' => $candidate['quality_modifier'] ?? null,
                    'scores' => $candidate['scores'] ?? [],
                    'metadata' => $candidate['metadata'] ?? [],
                ]);
            }
        });

        $timings['database_time_ms'] = ($timings['database_time_ms'] ?? 0) + (int) round((microtime(true) - $databaseStartedAt) * 1000);

        RecognitionScanTiming::updateOrCreate(
            ['id_scan' => $scan->id],
            $this->timingPayload($timings)
        );

        return $scan->refresh();
    }

    public function output(RecognitionScan $scan): array
    {
        $scan->loadMissing(['candidates.product', 'timings']);

        return [
            'scan_id' => $scan->scan_uuid,
            'status' => $scan->status,
            'decision_reason' => $scan->decision_reason,
            'quality' => [
                'score' => $this->number($scan->quality_score),
                'blur' => $this->number($scan->blur_score),
                'brightness' => $this->number($scan->brightness_score),
                'glare' => $this->number($scan->glare_score),
                'perspective' => $this->number($scan->perspective_score),
            ],
            'timings_ms' => [
                'total' => $scan->timings?->total_processing_time_ms,
                'input_preparation' => $scan->timings?->input_preparation_time_ms,
                'quality_check' => $scan->timings?->quality_check_time_ms,
                'contour_detection' => $scan->timings?->contour_detection_time_ms,
                'perspective_correction' => $scan->timings?->perspective_correction_time_ms,
                'hash_generation' => $scan->timings?->hash_generation_time_ms,
                'hash_search' => $scan->timings?->hash_search_time_ms,
                'color_comparison' => $scan->timings?->color_comparison_time_ms,
                'ocr' => $scan->timings?->ocr_time_ms,
                'orb' => $scan->timings?->orb_time_ms,
                'scoring' => $scan->timings?->scoring_time_ms,
                'database' => $scan->timings?->database_time_ms,
            ],
            'candidates' => $scan->candidates
                ->sortBy('rank')
                ->take(3)
                ->map(fn ($candidate) => [
                    'product_id' => $candidate->id_product,
                    'name' => $candidate->product?->name,
                    'score_final' => $this->number($candidate->score_final),
                    'scores' => $candidate->scores ?: [],
                ])
                ->values()
                ->all(),
            'ground_truth' => [
                'expected_product_id' => $scan->expected_product_id,
                'expected_card_id' => $scan->expected_card_id,
                'scenario_label' => $scan->scenario_label,
                'top_1_correct' => $scan->top_1_correct,
                'top_3_correct' => $scan->top_3_correct,
                'false_positive' => $scan->false_positive,
                'false_negative' => $scan->false_negative,
            ],
        ];
    }

    private function timingPayload(array $timings): array
    {
        $keys = [
            'total_processing_time_ms',
            'input_preparation_time_ms',
            'quality_check_time_ms',
            'contour_detection_time_ms',
            'perspective_correction_time_ms',
            'hash_generation_time_ms',
            'hash_search_time_ms',
            'color_comparison_time_ms',
            'ocr_time_ms',
            'orb_time_ms',
            'scoring_time_ms',
            'database_time_ms',
        ];

        $payload = [];
        foreach ($keys as $key) {
            $payload[$key] = isset($timings[$key]) ? max(0, (int) $timings[$key]) : 0;
        }
        $payload['metadata'] = ['raw' => $timings];

        return $payload;
    }

    private function number(mixed $value): ?float
    {
        return $value === null ? null : round((float) $value, 4);
    }
}
