<?php

namespace Modules\IdeaLab\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Modules\IdeaLab\Models\Idea;

class IdeaToolWorkflowService
{
    public function snapshot(Idea $idea): array
    {
        $idea->loadMissing(['aiConsensusRuns.aiConsensusRun', 'conversions']);

        $meta = $idea->meta ?? [];
        $workflow = data_get($meta, 'tool_workflow', []);
        $sandbox = data_get($workflow, 'sandbox', []);
        $compliance = data_get($workflow, 'compliance', []);
        $approval = data_get($workflow, 'approval', []);
        $issues = data_get($workflow, 'issues', []);

        $hasDiscovery = $idea->aiConsensusRuns->contains(fn ($link) => $link->purpose === 'discovery');
        $hasBlueprint = $idea->aiConsensusRuns->contains(fn ($link) => $link->purpose === 'module_blueprint');
        $hasFeedback = $idea->aiMessages()->exists();
        $hasSandbox = filled(data_get($sandbox, 'module_path')) && is_dir((string) data_get($sandbox, 'module_path'));
        $hasCompliance = filled(data_get($compliance, 'run_id'));
        $compliancePassed = in_array(data_get($compliance, 'final_status'), ['approved', 'approved_with_warnings'], true)
            && (int) data_get($compliance, 'failed_findings', 0) === 0
            && (int) data_get($compliance, 'blocker_findings', 0) === 0;
        $isApproved = filled(data_get($approval, 'approved_at'));

        return [
            'current' => $this->currentStage($hasDiscovery, $hasBlueprint, $hasSandbox, $hasCompliance, $compliancePassed, $isApproved, $issues),
            'sandbox_root' => $this->sandboxRoot(),
            'sandbox' => $sandbox,
            'compliance' => $compliance,
            'approval' => $approval,
            'issues' => is_array($issues) ? $issues : [],
            'steps' => [
                $this->step('idea', 'Idea intake', true, 'Criada no IdeaLab'),
                $this->step('discussion', 'AI discussion', $hasDiscovery, $hasDiscovery ? 'Discussao iniciada no AI Consensus' : 'Pendente'),
                $this->step('feedback', 'Feedback loop', $hasFeedback, $hasFeedback ? 'Feedback registado' : 'Opcional ate haver problemas'),
                $this->step('blueprint', 'Blueprint LSG', $hasBlueprint, $hasBlueprint ? 'Blueprint pedido' : 'Pendente'),
                $this->step('sandbox', 'Sandbox module', $hasSandbox, $hasSandbox ? data_get($sandbox, 'module_name') : 'Pendente'),
                $this->step('validation', 'Compliance validation', $hasCompliance, $hasCompliance ? (data_get($compliance, 'final_status') . ' / ' . data_get($compliance, 'final_score')) : 'Pendente'),
                $this->step('approval', 'Go live approval', $isApproved, $isApproved ? 'Aprovado para go live' : 'Bloqueado ate validacao'),
            ],
        ];
    }

    public function createSandboxModule(Idea $idea): array
    {
        $moduleName = $this->moduleNameForIdea($idea);
        $modulePath = $this->sandboxRoot() . DIRECTORY_SEPARATOR . $moduleName;

        File::ensureDirectoryExists($modulePath);

        foreach (['Config', 'Database/Migrations', 'Http/Controllers', 'Models', 'Providers', 'Resources/views', 'Services', 'routes', 'lang/pt', 'lang/en'] as $dir) {
            File::ensureDirectoryExists($modulePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $dir));
        }

        $provider = "Modules\\_sandbox\\{$moduleName}\\Providers\\{$moduleName}ServiceProvider";
        $slug = Str::kebab($moduleName);

        $this->putIfMissing($modulePath . DIRECTORY_SEPARATOR . 'module.json', json_encode([
            'name' => $moduleName,
            'slug' => $slug,
            'enabled' => false,
            'version' => '0.1.0-sandbox',
            'description' => 'Sandbox module generated from IdeaLab idea #' . $idea->id . '.',
            'provider' => $provider,
            'permissions' => [
                'permission_' . str_replace('-', '_', $slug) . '_view',
                'permission_' . str_replace('-', '_', $slug) . '_manage',
            ],
            'menu' => [
                'label' => $moduleName,
                'icon' => 'fa-solid fa-flask',
                'route' => $slug . '.index',
                'group' => 'Sandbox',
                'order' => 999,
            ],
            'lsg' => [
                'area' => 'sandbox',
                'type' => 'sandbox_module',
                'managed_by' => 'idealab-tool-workflow',
                'source_idea_id' => $idea->id,
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);

        $this->putIfMissing($modulePath . DIRECTORY_SEPARATOR . 'README.md', "# {$moduleName}\n\nSandbox module generated from IdeaLab idea #{$idea->id}.\n\nThis module must remain under `Modules/_sandbox` until Compliance Center approval.\n");
        $this->putIfMissing($modulePath . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'web.php', "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n\nRoute::middleware(['web', 'auth'])->prefix('{$slug}')->name('{$slug}.')->group(function () {\n    Route::get('/', fn () => view('{$slug}::index'))->name('index');\n});\n");
        $this->putIfMissing($modulePath . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'index.blade.php', "@extends('layouts.app')\n\n@section('content')\n<div class=\"card shadow-sm\">\n    <div class=\"card-body\">\n        <h1 class=\"h4\"><i class=\"fa-solid fa-flask me-2\"></i>{$moduleName}</h1>\n        <p class=\"text-muted mb-0\">Sandbox module generated from IdeaLab.</p>\n    </div>\n</div>\n@endsection\n");
        $this->putIfMissing($modulePath . DIRECTORY_SEPARATOR . 'Providers' . DIRECTORY_SEPARATOR . "{$moduleName}ServiceProvider.php", "<?php\n\nnamespace Modules\\_sandbox\\{$moduleName}\\Providers;\n\nuse Illuminate\\Support\\ServiceProvider;\n\nclass {$moduleName}ServiceProvider extends ServiceProvider\n{\n    public function register(): void\n    {\n    }\n\n    public function boot(): void\n    {\n        \$this->loadRoutesFrom(__DIR__ . '/../routes/web.php');\n        \$this->loadViewsFrom(__DIR__ . '/../Resources/views', '{$slug}');\n        \$this->loadTranslationsFrom(__DIR__ . '/../lang', '{$slug}');\n        \$this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');\n    }\n}\n");

        $this->mergeWorkflowMeta($idea, [
            'sandbox' => [
                'module_name' => $moduleName,
                'module_slug' => $slug,
                'module_path' => $modulePath,
                'created_at' => now()->toIso8601String(),
                'created_by' => auth()->id(),
                'status' => 'created',
            ],
        ]);

        if (!in_array($idea->status, ['approved', 'converted', 'archived'], true)) {
            $idea->update(['status' => 'sandbox_generated']);
        }

        return ['module_name' => $moduleName, 'module_path' => $modulePath];
    }

    public function runCompliance(Idea $idea): ?object
    {
        $snapshot = $this->snapshot($idea);
        $modulePath = data_get($snapshot, 'sandbox.module_path');
        $moduleName = data_get($snapshot, 'sandbox.module_name');

        if (!$modulePath || !$moduleName || !is_dir($modulePath)) {
            return null;
        }

        if (!class_exists(\Modules\ModuleComplianceCenter\Services\ModuleComplianceCenterGateway::class)) {
            $this->mergeWorkflowMeta($idea, [
                'issues' => [[
                    'source' => 'compliance',
                    'severity' => 'blocker',
                    'message' => 'ModuleComplianceCenterGateway is not available.',
                    'created_at' => now()->toIso8601String(),
                ]],
            ]);
            return null;
        }

        $run = app(\Modules\ModuleComplianceCenter\Services\ModuleComplianceCenterGateway::class)->run([
            'module_name' => $moduleName,
            'module_path' => $modulePath,
            'source_type' => 'idealab_sandbox',
            'source_id' => $idea->id,
            'validators' => ['structure', 'design', 'security', 'integration', 'health'],
            'options' => [
                'async' => false,
                'generate_report' => true,
                'send_to_ai_consensus' => false,
                'create_project_tasks' => false,
            ],
            'requested_by' => auth()->id(),
        ]);

        $issues = [];
        if (($run->failed_findings ?? 0) > 0 || ($run->warning_findings ?? 0) > 0 || ($run->blocker_findings ?? 0) > 0) {
            $run->loadMissing('results');
            $issues = $run->results()
                ->whereIn('status', ['failed', 'warning', 'manual_review_required'])
                ->limit(30)
                ->get()
                ->map(fn ($finding) => [
                    'source' => 'compliance',
                    'severity' => $finding->severity,
                    'code' => $finding->code,
                    'message' => $finding->title . ': ' . $finding->message,
                    'file_path' => $finding->file_path,
                    'created_at' => now()->toIso8601String(),
                ])
                ->values()
                ->all();
        }

        $this->mergeWorkflowMeta($idea, [
            'compliance' => [
                'run_id' => $run->id,
                'uuid' => $run->uuid,
                'status' => $run->status,
                'final_status' => $run->final_status,
                'final_score' => $run->final_score,
                'failed_findings' => $run->failed_findings,
                'warning_findings' => $run->warning_findings,
                'blocker_findings' => $run->blocker_findings,
                'checked_at' => now()->toIso8601String(),
            ],
            'issues' => $issues,
        ]);

        if (!empty($issues)) {
            $idea->update(['status' => 'needs_revision']);
        } elseif (in_array($run->final_status, ['approved', 'approved_with_warnings'], true)) {
            $idea->update(['status' => 'validation_passed']);
        }

        return $run;
    }

    public function approveGoLive(Idea $idea): void
    {
        $snapshot = $this->snapshot($idea);
        if (!data_get($snapshot, 'compliance.run_id') || !empty($snapshot['issues'])) {
            return;
        }

        $this->mergeWorkflowMeta($idea, [
            'approval' => [
                'approved_at' => now()->toIso8601String(),
                'approved_by' => auth()->id(),
                'note' => 'Approved for go live. Module must be promoted out of sandbox by controlled deployment.',
            ],
        ]);

        $idea->update(['status' => 'approved']);
    }

    public function requestChanges(Idea $idea, ?string $reason = null): void
    {
        $issues = data_get($idea->meta ?? [], 'tool_workflow.issues', []);
        $issues = is_array($issues) ? $issues : [];

        if (filled($reason)) {
            array_unshift($issues, [
                'source' => 'manual_review',
                'severity' => 'medium',
                'code' => 'CHANGES_REQUESTED',
                'message' => $reason,
                'created_at' => now()->toIso8601String(),
                'created_by' => auth()->id(),
            ]);
        }

        $this->mergeWorkflowMeta($idea, [
            'issues' => $issues,
            'approval' => [
                'changes_requested_at' => now()->toIso8601String(),
                'changes_requested_by' => auth()->id(),
                'reason' => $reason,
            ],
        ]);

        $idea->update(['status' => 'needs_revision']);
    }

    public function feedbackPrompt(Idea $idea): string
    {
        $issues = data_get($idea->meta ?? [], 'tool_workflow.issues', []);
        if (empty($issues)) {
            return 'Reavaliar a ideia e propor o proximo incremento do blueprint LSG.';
        }

        return "Reformular o blueprint/modulo com base nestes problemas encontrados na validacao e revisao:\n\n"
            . collect($issues)->map(fn ($issue) => '- [' . data_get($issue, 'severity', 'info') . '] ' . data_get($issue, 'code', 'ISSUE') . ': ' . data_get($issue, 'message'))->implode("\n");
    }

    protected function currentStage(bool $hasDiscovery, bool $hasBlueprint, bool $hasSandbox, bool $hasCompliance, bool $compliancePassed, bool $isApproved, array $issues): string
    {
        if ($isApproved) {
            return 'approved_for_go_live';
        }
        if (!empty($issues)) {
            return 'changes_required';
        }
        if ($compliancePassed) {
            return 'validation_passed';
        }
        if ($hasCompliance) {
            return 'validation_running';
        }
        if ($hasSandbox) {
            return 'sandbox_generated';
        }
        if ($hasBlueprint) {
            return 'blueprint_ready';
        }
        if ($hasDiscovery) {
            return 'ai_discussion';
        }

        return 'draft';
    }

    protected function step(string $key, string $label, bool $done, string $detail): array
    {
        return compact('key', 'label', 'done', 'detail');
    }

    protected function moduleNameForIdea(Idea $idea): string
    {
        $moduleName = Str::studly(Str::limit(Str::slug($idea->title, ' '), 60, ''));

        if (!preg_match('/^[A-Za-z]/', $moduleName)) {
            $moduleName = 'Idea' . $moduleName;
        }

        return $moduleName ?: 'IdeaModule' . $idea->id;
    }

    protected function sandboxRoot(): string
    {
        return base_path('Modules/_sandbox');
    }

    protected function putIfMissing(string $path, string $contents): void
    {
        if (!File::exists($path)) {
            File::put($path, $contents);
        }
    }

    protected function mergeWorkflowMeta(Idea $idea, array $data): void
    {
        $meta = $idea->meta ?? [];
        $workflow = data_get($meta, 'tool_workflow', []);
        $workflow = array_replace_recursive($workflow, $data);
        data_set($meta, 'tool_workflow', $workflow);
        $idea->forceFill(['meta' => $meta])->save();
        $idea->refresh();
    }
}
