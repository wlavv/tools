<?php

namespace Modules\ModuleComplianceCenter\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use Modules\ModuleComplianceCenter\Models\ComplianceManagedModule;
use Modules\ModuleComplianceCenter\Models\ComplianceRun;
use Modules\ModuleComplianceCenter\Models\ComplianceRunResult;
use Modules\ModuleComplianceCenter\Models\ComplianceValidator;
use Modules\ModuleComplianceCenter\Http\Controllers\Concerns\PreparesComplianceCenterPage;
use Modules\ModuleComplianceCenter\Services\ComplianceValidatorRegistry;

class ModuleComplianceCenterController extends Controller
{
    use PreparesComplianceCenterPage;

    public function index(ComplianceValidatorRegistry $registry)
    {
        $registry->sync();
        $this->prepareCompliancePage(__('module-compliance-center::module-compliance-center.title'), [], [
            $this->actionLink('new', 'Run validation', 'fa-solid fa-plus', 'module_compliance_center.runs.create'),
        ]);

        return view('module-compliance-center::dashboard', [
            'modulesCount' => ComplianceManagedModule::count(),
            'validatorsAvailable' => ComplianceValidator::where('is_available', true)->count(),
            'lastRuns' => ComplianceRun::where('status', '!=', 'archived')->latest()->limit(8)->get(),
            'averageScore' => round((float) ComplianceRun::where('status', '!=', 'archived')->whereNotNull('final_score')->avg('final_score'), 2),
            'blockers' => ComplianceRunResult::whereHas('run', fn ($query) => $query->where('status', '!=', 'archived'))->where('severity', 'blocker')->latest()->limit(8)->get(),
            'changesRequired' => ComplianceRun::where('final_status', 'changes_required')->count(),
            'modulesWithProblems' => ComplianceManagedModule::whereIn('last_status', ['changes_required', 'rejected', 'manual_review_required'])->limit(8)->get(),
            'structuralModules' => $this->structuralModules(),
        ]);
    }

    protected function structuralModules(): array
    {
        $items = [
            ['name' => 'Module Compliance Core', 'aliases' => ['ModuleComplianceCore'], 'route' => 'module_compliance.core.index'],
            ['name' => 'Module Design Validator', 'aliases' => ['ModuleDesignValidator'], 'route' => 'module-design-validator.index'],
            ['name' => 'Module Security Validator', 'aliases' => ['ModuleSecurityValidator'], 'route' => 'module-security-validator.index'],
            ['name' => 'ModuleIntegrationValidator', 'aliases' => ['Module Integration Validator'], 'route' => 'module-integration-validator.index'],
            ['name' => 'Module Structure Validator', 'aliases' => ['ModuleStructureValidator'], 'route' => 'module_structure_validator.index'],
            ['name' => 'ModuleHealth', 'aliases' => ['Module Health'], 'route' => 'module_health.index'],
            ['name' => 'ModuleHealthBridge', 'aliases' => ['Module Health Bridge'], 'route' => 'module-health-bridge.index'],
        ];

        return collect($items)->map(function (array $item) {
            $names = array_merge([$item['name']], $item['aliases'] ?? []);
            $module = ComplianceManagedModule::whereIn('module_name', $names)->first();
            $route = $item['route'] ?? null;

            return [
                'label' => $item['name'],
                'module' => $module,
                'module_url' => $module ? route('module_compliance_center.modules.show', $module) : null,
                'tool_url' => $route && Route::has($route) ? route($route) : null,
            ];
        })->all();
    }
}
