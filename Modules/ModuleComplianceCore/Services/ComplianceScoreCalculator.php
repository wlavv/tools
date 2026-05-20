<?php

namespace Modules\ModuleComplianceCore\Services;

use Modules\ModuleComplianceCore\DTO\ModuleValidationFinding;
use Modules\ModuleComplianceCore\Enums\ValidationStatus;

class ComplianceScoreCalculator
{
    /** @param array<int, ModuleValidationFinding> $findings */
    public function calculate(array $findings): int
    {
        if (empty($findings)) {
            return 100;
        }

        $maxPenalty = 100;
        $penalty = 0;

        foreach ($findings as $finding) {
            if ($finding->status === ValidationStatus::Passed || $finding->status === ValidationStatus::Skipped) {
                continue;
            }

            $severityWeight = (float) config('module-compliance-core.severity_weights.' . $finding->severity->value, 5);
            $statusWeight = (float) config('module-compliance-core.status_weights.' . $finding->status->value, 1);
            $penalty += $severityWeight * $statusWeight;
        }

        return max(0, min(100, (int) round(100 - min($penalty, $maxPenalty))));
    }
}
