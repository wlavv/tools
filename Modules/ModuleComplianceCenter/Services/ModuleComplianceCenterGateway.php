<?php

namespace Modules\ModuleComplianceCenter\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\ModuleComplianceCenter\Jobs\RunModuleComplianceCenterCheckJob;
use Modules\ModuleComplianceCenter\Models\ComplianceRun;

class ModuleComplianceCenterGateway
{
    public function __construct(
        private readonly ComplianceModuleDiscoveryService $modules,
    ) {
    }

    public function run(array $payload): ComplianceRun
    {
        $data = Validator::make($payload, [
            'module_name' => ['required', 'string', 'max:120'],
            'module_path' => ['required', 'string', 'max:500'],
            'source_type' => ['nullable', 'string', 'max:80'],
            'source_id' => ['nullable'],
            'validators' => ['nullable', 'array'],
            'validators.*' => ['string'],
            'options' => ['nullable', 'array'],
            'requested_by' => ['nullable'],
        ])->validate();

        $managedModule = $this->modules->findOrRegister($data);
        $options = array_replace([
            'async' => config('module-compliance-center.default_async', true),
            'generate_report' => config('module-compliance-center.default_generate_report', true),
            'send_to_ai_consensus' => false,
            'create_project_tasks' => false,
        ], $data['options'] ?? []);

        $run = ComplianceRun::create([
            'uuid' => (string) Str::uuid(),
            'managed_module_id' => $managedModule->id,
            'module_name' => $data['module_name'],
            'module_path' => $this->modules->assertSafeModulePath($data['module_path']),
            'source_type' => $data['source_type'] ?? 'manual',
            'source_id' => isset($data['source_id']) ? (string) $data['source_id'] : null,
            'status' => 'pending',
            'options' => array_merge($options, ['validators' => $data['validators'] ?? null]),
            'requested_by' => $data['requested_by'] ?? null,
        ]);

        $managedModule->update([
            'last_run_id' => $run->id,
            'last_status' => 'pending',
            'last_checked_at' => now(),
        ]);

        if ($options['async']) {
            RunModuleComplianceCenterCheckJob::dispatch($run->id);

            return $run;
        }

        app(ComplianceRunService::class)->execute($run);

        return $run->fresh(['validators', 'results', 'report']);
    }
}
