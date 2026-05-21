<?php

namespace Modules\WebCatalogue\Services\Recognition;

use Modules\WebCatalogue\Models\VisualRecognitionSession;

class RecognitionScoringService
{
    public function scoreResult(VisualRecognitionSession $session, array $legacyResult, array $quality): array
    {
        $qualityScore = (float) ($quality['score'] ?? 0);
        $qualityModifier = app(RecognitionQualityService::class)->qualityModifier($qualityScore);
        $candidates = $this->candidateRows($session, $legacyResult, $qualityModifier);
        $top = $candidates[0] ?? null;
        $second = $candidates[1] ?? null;
        $decision = $this->decision($quality, $top, $second);
        $groundTruth = $this->groundTruth($session, $candidates, $decision);

        return [
            'status' => $decision['status'],
            'decision_reason' => $decision['reason'],
            'rejection_reason' => $decision['rejection_reason'] ?? null,
            'quality_modifier' => $qualityModifier,
            'candidates' => $candidates,
            'top_1_candidate' => $top,
            'top_3_candidates' => array_slice($candidates, 0, 3),
            'score_final' => $top['score_final'] ?? 0,
            'comparator_scores' => $top['scores'] ?? [],
            'ground_truth' => $groundTruth,
        ];
    }

    private function candidateRows(VisualRecognitionSession $session, array $legacyResult, float $qualityModifier): array
    {
        $matches = $session->matches()
            ->with('product')
            ->orderBy('rank')
            ->limit(10)
            ->get();
        $rows = [];

        foreach ($matches as $match) {
            $scores = $this->normalisedComparatorScores($match->metadata['scores'] ?? [], (float) $match->score, (string) $match->match_provider);
            $weighted = $this->weightedScore($scores, (float) $match->score);

            $rows[] = [
                'product_id' => $match->id_product,
                'resource_id' => $match->metadata['resource_id'] ?? null,
                'name' => strip_tags((string) ($match->product?->name ?? 'Product #' . $match->id_product)),
                'rank' => (int) $match->rank,
                'score_final' => round($weighted * $qualityModifier, 4),
                'weighted_score' => round($weighted, 4),
                'quality_modifier' => round($qualityModifier, 4),
                'scores' => $scores,
                'metadata' => [
                    'legacy_score' => (float) $match->score,
                    'legacy_provider' => $match->match_provider,
                    'legacy_status' => $match->status,
                    'candidate_sources' => $match->metadata['candidate_sources'] ?? ($match->metadata['scores']['candidate_sources'] ?? []),
                ],
            ];
        }

        if ($rows) {
            usort($rows, fn ($a, $b) => ((float) $b['score_final']) <=> ((float) $a['score_final']));
            foreach ($rows as $index => $row) {
                $rows[$index]['rank'] = $index + 1;
            }

            return $rows;
        }

        foreach ((array) ($legacyResult['suggestions'] ?? []) as $index => $suggestion) {
            $legacyScore = (float) ($suggestion['score'] ?? 0);
            $scores = $this->normalisedComparatorScores($suggestion, $legacyScore, 'legacy_suggestion');
            $weighted = $this->weightedScore($scores, $legacyScore);
            $rows[] = [
                'product_id' => $suggestion['product_id'] ?? null,
                'resource_id' => null,
                'name' => strip_tags((string) ($suggestion['name'] ?? 'Candidate')),
                'rank' => $index + 1,
                'score_final' => round($weighted * $qualityModifier, 4),
                'weighted_score' => round($weighted, 4),
                'quality_modifier' => round($qualityModifier, 4),
                'scores' => $scores,
                'metadata' => ['legacy_score' => $legacyScore, 'legacy_provider' => 'legacy_suggestion'],
            ];
        }

        return $rows;
    }

    private function normalisedComparatorScores(array $source, float $fallbackScore, string $provider): array
    {
        $identifierExact = $provider === 'identifier_exact_match_v1';

        return [
            'phash' => $this->scoreOrNull($source['phash_score'] ?? null),
            'ahash_dhash' => $this->scoreOrNull($source['edge_score'] ?? $source['dhash_score'] ?? null),
            'ocr_collector' => $identifierExact ? 100.0 : $this->scoreOrNull($source['ocr_collector_score'] ?? $source['identifier_score'] ?? null),
            'ocr_name' => $this->scoreOrNull($source['ocr_name_score'] ?? null),
            'orb' => $this->scoreOrNull($source['marker_confidence_score'] ?? $source['marker_score'] ?? null),
            'layout' => $this->scoreOrNull($source['region_score'] ?? $source['layout_score'] ?? null),
            'color' => $this->scoreOrNull($source['color_score'] ?? null),
            'legacy' => round(max(0, min(100, $fallbackScore)), 4),
        ];
    }

    private function weightedScore(array $scores, float $fallbackScore): float
    {
        $weights = (array) config('webcatalogue.recognition.pipeline_v2.weights', []);
        $weighted = 0.0;
        $totalWeight = 0.0;

        foreach ($weights as $key => $weight) {
            if (($scores[$key] ?? null) === null) {
                continue;
            }

            $weighted += ((float) $scores[$key]) * ((float) $weight);
            $totalWeight += (float) $weight;
        }

        if ($totalWeight <= 0) {
            return max(0, min(100, $fallbackScore));
        }

        return max(0, min(100, $weighted / $totalWeight));
    }

    private function decision(array $quality, ?array $top, ?array $second): array
    {
        $qualityScore = (float) ($quality['score'] ?? 0);
        $rejectBelow = (float) config('webcatalogue.recognition.pipeline_v2.quality.reject_below', 60);
        if (!$quality['ok'] || $qualityScore < $rejectBelow) {
            return [
                'status' => 'rejected',
                'reason' => 'quality_gate_rejected',
                'rejection_reason' => $quality['rejection_reason'] ?: 'quality_score_below_threshold',
            ];
        }

        if (!$top) {
            return [
                'status' => 'rejected',
                'reason' => 'no_candidates',
                'rejection_reason' => 'no_candidate_reached_minimum_score',
            ];
        }

        $score = (float) ($top['score_final'] ?? 0);
        $margin = $second ? $score - (float) ($second['score_final'] ?? 0) : 100;
        $ambiguousMargin = (float) config('webcatalogue.recognition.pipeline_v2.decision.ambiguous_margin', 8);

        if ($margin < $ambiguousMargin) {
            return ['status' => 'ambiguous', 'reason' => 'top_candidates_too_close'];
        }

        if ($score >= (float) config('webcatalogue.recognition.pipeline_v2.decision.auto_accept_score', 90)) {
            return ['status' => 'accepted', 'reason' => 'score_above_auto_accept_threshold'];
        }

        if (
            $score >= (float) config('webcatalogue.recognition.pipeline_v2.decision.conditional_accept_min', 80)
            && $score <= (float) config('webcatalogue.recognition.pipeline_v2.decision.conditional_accept_max', 89.999)
            && $margin > (float) config('webcatalogue.recognition.pipeline_v2.decision.conditional_accept_margin', 10)
        ) {
            return ['status' => 'accepted', 'reason' => 'conditional_score_with_safe_margin'];
        }

        if ($score >= (float) config('webcatalogue.recognition.pipeline_v2.decision.ambiguous_min', 65)) {
            return ['status' => 'ambiguous', 'reason' => 'score_requires_confirmation'];
        }

        return [
            'status' => 'rejected',
            'reason' => 'score_below_minimum',
            'rejection_reason' => 'score_final_below_threshold',
        ];
    }

    private function groundTruth(VisualRecognitionSession $session, array $candidates, array $decision): array
    {
        $metadata = $session->metadata ?: [];
        $expectedProductId = $metadata['ground_truth']['expected_product_id'] ?? $metadata['expected_product_id'] ?? null;
        $expectedCardId = $metadata['ground_truth']['expected_card_id'] ?? $metadata['expected_card_id'] ?? null;
        $scenarioLabel = $metadata['ground_truth']['scenario_label'] ?? $metadata['scenario_label'] ?? null;
        $expectedProductId = $expectedProductId ? (int) $expectedProductId : null;
        $topIds = array_values(array_filter(array_map(fn ($row) => isset($row['product_id']) ? (int) $row['product_id'] : null, array_slice($candidates, 0, 3))));
        $top1Correct = $expectedProductId ? (($topIds[0] ?? null) === $expectedProductId) : null;
        $top3Correct = $expectedProductId ? in_array($expectedProductId, $topIds, true) : null;
        $accepted = ($decision['status'] ?? null) === 'accepted';

        return [
            'expected_product_id' => $expectedProductId,
            'expected_card_id' => $expectedCardId,
            'scenario_label' => $scenarioLabel,
            'top_1_correct' => $top1Correct,
            'top_3_correct' => $top3Correct,
            'false_positive' => $expectedProductId ? ($accepted && !$top1Correct) : null,
            'false_negative' => $expectedProductId ? (!$accepted && $top3Correct) : null,
        ];
    }

    private function scoreOrNull(mixed $value): ?float
    {
        if (!is_numeric($value)) {
            return null;
        }

        return round(max(0, min(100, (float) $value)), 4);
    }
}
