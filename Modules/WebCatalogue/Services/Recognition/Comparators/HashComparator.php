<?php

namespace Modules\WebCatalogue\Services\Recognition\Comparators;

class HashComparator
{
    public function score(?string $a, ?string $b): float
    {
        $a = (string) $a;
        $b = (string) $b;
        $length = min(strlen($a), strlen($b));

        if ($length === 0) {
            return 0.0;
        }

        $distance = 0;
        for ($i = 0; $i < $length; $i++) {
            if ($a[$i] !== $b[$i]) {
                $distance++;
            }
        }

        return max(0, (1 - ($distance / $length)) * 100);
    }
}
