<?php

namespace Modules\ModuleComplianceCenter\Services;

use Modules\ModuleComplianceCenter\Models\ComplianceReport;
use Modules\ModuleComplianceCenter\Models\ComplianceRun;

class ComplianceReportService
{
    public function generate(ComplianceRun $run): ComplianceReport
    {
        $run->loadMissing(['validators', 'results']);
        $findings = $run->results;
        $recommendations = $findings->pluck('recommendation')->filter()->unique()->values()->all();
        $payload = [
            'module' => [
                'name' => $run->module_name,
                'path' => $run->module_path,
                'source_type' => $run->source_type,
                'source_id' => $run->source_id,
            ],
            'scores' => [
                'structure' => $run->structure_score,
                'design' => $run->design_score,
                'security' => $run->security_score,
                'integration' => $run->integration_score,
                'health' => $run->health_score,
                'final' => $run->final_score,
            ],
            'summary' => [
                'final_status' => $run->final_status,
                'total_findings' => $run->total_findings,
                'failed_findings' => $run->failed_findings,
                'warning_findings' => $run->warning_findings,
                'blocker_findings' => $run->blocker_findings,
            ],
            'validators' => $run->validators->toArray(),
            'findings_by_severity' => $findings->groupBy('severity')->map->values()->toArray(),
            'findings_by_validator' => $findings->groupBy('validator_key')->map->values()->toArray(),
        ];

        return ComplianceReport::updateOrCreate(
            ['run_id' => $run->id],
            [
                'title' => 'Compliance report for ' . $run->module_name,
                'summary' => $this->summary($run),
                'final_status' => (string) $run->final_status,
                'final_score' => $run->final_score,
                'report_payload' => $payload,
                'recommendations' => $recommendations,
                'created_by' => $run->requested_by,
            ]
        );
    }

    private function summary(ComplianceRun $run): string
    {
        return sprintf(
            '%s finished with status %s, final score %s, %d findings and %d blockers.',
            $run->module_name,
            $run->final_status ?? 'pending',
            $run->final_score ?? 'n/a',
            $run->total_findings,
            $run->blocker_findings
        );
    }
}
