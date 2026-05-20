<?php

namespace Modules\ModuleComplianceCenter\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use Modules\ModuleComplianceCenter\Http\Controllers\Concerns\PreparesComplianceCenterPage;
use Modules\ModuleComplianceCenter\Models\ComplianceManagedModule;
use Modules\ModuleComplianceCenter\Services\ComplianceModuleDiscoveryService;

class ComplianceModuleController extends Controller
{
    use PreparesComplianceCenterPage;

    public function index()
    {
        $this->prepareCompliancePage('Modules', ['Modules'], [
            $this->actionLink('back', 'Back', 'fa-solid fa-angle-left', 'module_compliance_center.dashboard'),
            [
                'key' => 'discover',
                'label' => 'Discover',
                'icon' => 'fa-solid fa-magnifying-glass',
                'url' => route('module_compliance_center.modules.discover'),
                'type' => 'form',
                'method' => 'POST',
                'confirm' => 'Discover modules?',
            ],
        ]);

        return view('module-compliance-center::modules.index', [
            'modules' => ComplianceManagedModule::latest('updated_at')->get(),
        ]);
    }

    public function show(ComplianceManagedModule $module)
    {
        $module->load(['runs' => fn ($query) => $query->latest()]);
        $this->prepareCompliancePage($module->module_name, [
            ['label' => 'Modules', 'url' => route('module_compliance_center.modules.index')],
            $module->module_name,
        ], [
            $this->actionLink('back', 'Back', 'fa-solid fa-angle-left', 'module_compliance_center.modules.index'),
            $this->actionLink('new', 'Run validation', 'fa-solid fa-plus', 'module_compliance_center.runs.create', ['module' => $module->id]),
        ]);

        return view('module-compliance-center::modules.show', compact('module'));
    }

    public function discover(ComplianceModuleDiscoveryService $discovery): RedirectResponse
    {
        $modules = $discovery->discover();

        return redirect()->route('module_compliance_center.modules.index')
            ->with('success', count($modules) . ' modules discovered.');
    }
}
