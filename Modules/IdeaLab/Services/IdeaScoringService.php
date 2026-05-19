<?php

namespace Modules\IdeaLab\Services;

use Modules\IdeaLab\Models\Idea;

class IdeaScoringService
{
    public function calculate(Idea $idea): float
    {
        $weights = config('idealab.scoring.weights');

        $opportunity = (int) ($idea->opportunity_score ?? 0);
        $strategic = (int) ($idea->strategic_score ?? 0);
        $reusability = (int) ($idea->reusability_score ?? 0);
        $monetization = (int) ($idea->monetization_score ?? 0);
        $effort = (int) ($idea->effort_score ?? 0);
        $risk = (int) ($idea->risk_score ?? 0);

        $score =
            ($opportunity * $weights['opportunity']) +
            ($strategic * $weights['strategic_fit']) +
            ($reusability * $weights['reusability']) +
            ($monetization * $weights['monetization']) -
            ($effort * $weights['effort_penalty']) -
            ($risk * $weights['risk_penalty']);

        return round(max(0, min(100, $score)), 2);
    }

    public function refresh(Idea $idea): Idea
    {
        $idea->final_score = $this->calculate($idea);
        $idea->save();

        return $idea;
    }
}
