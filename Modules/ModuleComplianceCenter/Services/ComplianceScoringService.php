<?php

namespace Modules\ModuleComplianceCenter\Services;

use Modules\ModuleComplianceCenter\Models\ComplianceRun;

class ComplianceScoringService
{
    public function calculateFinalScore(ComplianceRun $run): float
    {
        $validators = $run->validators()->whereNotIn('status', ['skipped', 'unavailable'])->get();
        $totalWeight = (float) $validators->sum('weight');

        if ($totalWeight <= 0) {
            return 0.0;
        }

        return round((float) $validators->sum(fn ($validator) => ((float) $validator->score) * ((float) $validator->weight)) / $totalWeight, 2);
    }

    public function finalStatus(ComplianceRun $run, float $score): string
    {
        if ($run->results()->where('status', 'manual_review_required')->exists()) {
            return 'manual_review_required';
        }

        if ($run->results()->where('severity', 'blocker')->where('status', 'failed')->exists()) {
            return 'changes_required';
        }

        if ($score >= (float) config('module-compliance-center.scoring.approved_min_score', 90)) {
            return 'approved';
        }

        if ($score >= (float) config('module-compliance-center.scoring.approved_with_warnings_min_score', 75)) {
            return 'approved_with_warnings';
        }

        if ($score >= (float) config('module-compliance-center.scoring.changes_required_min_score', 50)) {
            return 'changes_required';
        }

        return 'rejected';
    }

    public function summarize(ComplianceRun $run): array
    {
        return [
            'total_findings' => $run->results()->count(),
            'failed_findings' => $run->results()->where('status', 'failed')->count(),
            'warning_findings' => $run->results()->where('status', 'warning')->count(),
            'blocker_findings' => $run->results()->where('severity', 'blocker')->count(),
        ];
    }
}
