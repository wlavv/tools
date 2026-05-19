<?php

namespace Modules\AIConsensus\Services;

class AIConsensusScoringService
{
    public function score(array $signals): float
    {
        $values = array_filter($signals, 'is_numeric');

        return empty($values) ? 0.0 : round(array_sum($values) / count($values), 2);
    }
}
