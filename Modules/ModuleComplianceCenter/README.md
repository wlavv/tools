# ModuleComplianceCenter

Central compliance dashboard and orchestrator for LSG modules.

## Purpose

ModuleComplianceCenter manages validation runs for structural LSG validators without duplicating their logic. It discovers modules, detects available validators, runs combined checks, stores results, calculates weighted scores, creates reports, and prepares optional handoff to AI Consensus and Project Manager.

## Optional Validators

Recommended order:

1. ModuleComplianceCore
2. ModuleStructureValidator
3. ModuleDesignValidator
4. ModuleSecurityValidator
5. ModuleIntegrationValidator
6. ModuleHealthBridge or ModuleHealth

If a validator service class is missing, the run records it as unavailable and stores a skipped finding instead of breaking the application.

## Install

Run migrations:

```bash
php artisan migrate
```

Seed the validator registry:

```bash
php artisan db:seed --class="Modules\\ModuleComplianceCenter\\Database\\Seeders\\ModuleComplianceCenterSeeder"
```

Open the admin entry point:

```text
/admin/module-compliance-center
```

## Gateway Usage

```php
app(\Modules\ModuleComplianceCenter\Services\ModuleComplianceCenterGateway::class)->run([
    'module_name' => 'IdeaLab',
    'module_path' => base_path('Modules/IdeaLab'),
    'source_type' => 'manual',
    'source_id' => null,
    'validators' => [
        'structure',
        'design',
        'security',
        'integration',
        'health',
    ],
    'options' => [
        'async' => true,
        'generate_report' => true,
        'send_to_ai_consensus' => false,
        'create_project_tasks' => false,
    ],
    'requested_by' => auth()->id(),
]);
```

## Future Integrations

AI Consensus is accessed only when `Modules\AIConsensus\Services\AIConsensusGateway` exists. The payload uses the future template `modules.lsg_validation_report_analysis`.

Project Manager is accessed only when `Modules\ProjectManager\Services\ProjectManagerTaskService` exists. Otherwise, task payloads are prepared and stored on the report.
