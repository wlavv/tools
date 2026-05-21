<?php

namespace Modules\WebCatalogue\Services\Recognition\Comparators;

use Modules\WebCatalogue\Services\Recognition\OpenCvRecognitionClient;

class OrbMarkerComparator
{
    public function __construct(private OpenCvRecognitionClient $openCv)
    {
    }

    public function compare(array $queryMarkers, array $referenceMarkers): ?array
    {
        return $this->openCv->compareMarkers($queryMarkers, $referenceMarkers);
    }

    public function compareBatch(array $queryMarkers, array $references): ?array
    {
        return $this->openCv->compareMarkersBatch($queryMarkers, $references);
    }

    public function confidence(float $markerScore, int $goodMatches): float
    {
        return round(min(100, ($markerScore * 1.65) + ($goodMatches * 0.38)), 4);
    }

    public function boostResult(array $scoreSet, array $best, bool $markerOnly, string $markerScoringMode, int $resourceMarkerId): array
    {
        $baseScore = (float) ($scoreSet['final_score'] ?? 0);
        $markerScore = (float) ($best['score'] ?? 0);
        $weight = max(0, min(1, (float) config('webcatalogue.recognition.visual_markers.score_weight', 0.35)));
        $minScore = max(0, min(100, (float) config('webcatalogue.recognition.visual_markers.min_score_for_boost', 8)));
        $boostPerGoodMatch = max(0, (float) config('webcatalogue.recognition.visual_markers.boost_per_good_match', 0.18));
        $maxBoost = max(0, (float) config('webcatalogue.recognition.visual_markers.max_boost', 8));
        $strongMinScore = max(0, min(100, (float) config('webcatalogue.recognition.visual_markers.strong_min_score', 18)));
        $strongMinGoodMatches = max(1, (int) config('webcatalogue.recognition.visual_markers.strong_min_good_matches', 35));
        $goodMatches = (int) ($best['good_matches'] ?? 0);
        $relativeBoost = $markerScore >= $minScore
            ? min($maxBoost, ($markerScore * $weight) + ($goodMatches * $boostPerGoodMatch))
            : 0.0;
        $markerConfidence = $this->confidence($markerScore, $goodMatches);
        $strongMarkerQualified = $markerScore >= $strongMinScore && $goodMatches >= $strongMinGoodMatches;
        $strongMarkerScore = $strongMarkerQualified ? $markerConfidence : 0.0;
        $finalScore = $markerOnly
            ? $markerConfidence
            : min(100, max($baseScore + $relativeBoost, $strongMarkerScore));

        $scoreSet['final_score_before_markers'] = round($baseScore, 4);
        $scoreSet['marker_score'] = round($markerScore, 4);
        $scoreSet['marker_boost'] = $markerOnly ? 0.0 : round(max(0, $finalScore - $baseScore), 4);
        $scoreSet['marker_weight'] = round($weight, 4);
        $scoreSet['marker_scoring_mode'] = $markerScoringMode;
        $scoreSet['marker_min_score_for_boost'] = round($minScore, 4);
        $scoreSet['marker_boost_per_good_match'] = round($boostPerGoodMatch, 4);
        $scoreSet['marker_max_boost'] = round($maxBoost, 4);
        $scoreSet['marker_confidence_score'] = round($markerConfidence, 4);
        $scoreSet['marker_strong_min_score'] = round($strongMinScore, 4);
        $scoreSet['marker_strong_min_good_matches'] = $strongMinGoodMatches;
        $scoreSet['marker_strong_qualified'] = $strongMarkerQualified;
        $scoreSet['marker_strong_applied'] = $strongMarkerQualified && $markerConfidence >= $baseScore + $relativeBoost;
        $scoreSet['marker_applied'] = $markerOnly ? $markerScore > 0 : $finalScore > $baseScore;
        $scoreSet['marker_status'] = $markerOnly ? 'markers_only' : ($strongMarkerQualified ? 'strong_marker' : ($markerScore >= $minScore ? 'scored' : 'below_min_score'));
        $scoreSet['marker_matches'] = (int) ($best['matches'] ?? 0);
        $scoreSet['marker_good_matches'] = $goodMatches;
        $scoreSet['marker_inlier_ratio'] = round((float) ($best['inlier_ratio'] ?? 0), 4);
        $scoreSet['marker_capture_id'] = $best['capture_id'] ?? null;
        $scoreSet['marker_resource_marker_id'] = $resourceMarkerId;

        if ($markerOnly) {
            $scoreSet['visual_score_ignored'] = true;
            $scoreSet['scoring_mode'] = 'markers_only';
        }

        $scoreSet['final_score'] = round($finalScore, 4);

        return $scoreSet;
    }
}
