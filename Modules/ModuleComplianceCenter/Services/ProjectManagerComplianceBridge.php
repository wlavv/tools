<?php

namespace Modules\ModuleComplianceCenter\Services;

use Modules\ModuleComplianceCenter\Models\ComplianceRun;

class ProjectManagerComplianceBridge
{
    public function createTasksFromRun(ComplianceRun $run): array
    {
        $serviceClass = '\\Modules\\ProjectManager\\Services\\ProjectManagerTaskService';
        $findings = $run->results()->whereIn('status', ['failed', 'warning'])->get();
        $payload = $findings->map(fn ($finding) => [
            'title' => '[' . $run->module_name . '] ' . $finding->title,
            'description' => trim(($finding->message ?? '') . "\n\n" . ($finding->recommendation ?? '')),
            'severity' => $finding->severity,
            'source' => 'module_compliance_center',
            'source_id' => $run->id,
        ])->values()->all();

        if (!class_exists($serviceClass)) {
            return $payload;
        }

        return app($serviceClass)->createMany($payload);
    }
}
