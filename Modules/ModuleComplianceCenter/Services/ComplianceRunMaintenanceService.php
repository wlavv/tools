<?php

namespace Modules\ModuleComplianceCenter\Services;

use Modules\ModuleComplianceCenter\Models\ComplianceManagedModule;
use Modules\ModuleComplianceCenter\Models\ComplianceRun;

class ComplianceRunMaintenanceService
{
    public function __construct(
        private readonly ComplianceModuleDiscoveryService $discovery,
        private readonly ModuleComplianceCenterGateway $gateway,
    ) {
    }

    public function archiveOldAndRunAll(?int $requestedBy = null): array
    {
        $archived = ComplianceRun::query()
            ->where('status', '!=', 'archived')
            ->update([
                'status' => 'archived',
                'updated_at' => now(),
            ]);

        $this->discovery->discover();

        $modules = ComplianceManagedModule::query()
            ->where('is_active', true)
            ->orderBy('module_name')
            ->get();

        $createdRuns = $modules
            ->map(fn (ComplianceManagedModule $module) => $this->gateway->run([
                'module_name' => $module->module_name,
                'module_path' => $module->module_path,
                'source_type' => 'maintenance_rerun_all',
                'source_id' => now()->format('YmdHis'),
                'options' => [
                    'async' => config('module-compliance-center.default_async', true),
                    'generate_report' => config('module-compliance-center.default_generate_report', true),
                ],
                'requested_by' => $requestedBy,
            ]));

        return [
            'archived' => $archived,
            'modules' => $modules->count(),
            'created' => $createdRuns->count(),
            'run_ids' => $createdRuns->pluck('id')->all(),
        ];
    }
}
