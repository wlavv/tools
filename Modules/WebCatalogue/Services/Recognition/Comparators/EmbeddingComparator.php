<?php

namespace Modules\WebCatalogue\Services\Recognition\Comparators;

class EmbeddingComparator
{
    public function score(array $a, array $b): float
    {
        $length = min(count($a), count($b));

        if ($length === 0) {
            return 0.0;
        }

        $dot = 0.0;
        for ($i = 0; $i < $length; $i++) {
            $dot += ((float) $a[$i]) * ((float) $b[$i]);
        }

        return max(0, min(100, (($dot + 1) / 2) * 100));
    }
}
