<?php

namespace Modules\WebCatalogue\Services\Recognition\Comparators;

class ColorHistogramComparator
{
    public function score(array $a, array $b): float
    {
        $length = min(count($a), count($b));

        if ($length === 0) {
            return 0.0;
        }

        $intersection = 0.0;
        for ($i = 0; $i < $length; $i++) {
            $intersection += min((float) $a[$i], (float) $b[$i]);
        }

        return max(0, min(100, $intersection * 100));
    }
}
