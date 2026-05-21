<?php

namespace Modules\WebCatalogue\Services\Recognition\Comparators;

class CompositeVisualComparator
{
    public function __construct(
        private HashComparator $hashes,
        private ColorHistogramComparator $colors,
        private EmbeddingComparator $embeddings
    ) {
    }

    public function score(array $captureProfile, array $resourceProfile, array $weights): array
    {
        $embeddingScore = $this->embeddings->score($captureProfile['embedding'] ?? [], $resourceProfile['embedding'] ?? []);
        $phashScore = $this->hashes->score($captureProfile['phash'] ?? null, $resourceProfile['phash'] ?? null);
        $edgeScore = $this->hashes->score($captureProfile['edge_hash'] ?? null, $resourceProfile['edge_hash'] ?? null);
        $colorScore = $this->colors->score($captureProfile['color_histogram'] ?? [], $resourceProfile['color_histogram'] ?? []);

        $final = ($embeddingScore * ($weights['embedding'] ?? 0))
            + ($phashScore * ($weights['phash'] ?? 0))
            + ($edgeScore * ($weights['edge'] ?? 0))
            + ($colorScore * ($weights['color'] ?? 0));

        return [
            'final_score' => round($final, 4),
            'embedding_score' => round($embeddingScore, 4),
            'phash_score' => round($phashScore, 4),
            'edge_score' => round($edgeScore, 4),
            'color_score' => round($colorScore, 4),
        ];
    }

    public function retrievalScore(array $captureProfile, array $resourceProfile): float
    {
        $embedding = $this->embeddings->score($captureProfile['embedding'] ?? [], $resourceProfile['embedding'] ?? []);
        $color = $this->colors->score($captureProfile['color_histogram'] ?? [], $resourceProfile['color_histogram'] ?? []);

        return round(($embedding * 0.75) + ($color * 0.25), 4);
    }
}
