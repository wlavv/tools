<?php

namespace Modules\ModuleComplianceCenter\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use Modules\ModuleComplianceCenter\Http\Controllers\Concerns\PreparesComplianceCenterPage;
use Modules\ModuleComplianceCenter\Http\Requests\ApproveComplianceRunRequest;
use Modules\ModuleComplianceCenter\Http\Requests\RejectComplianceRunRequest;
use Modules\ModuleComplianceCenter\Http\Requests\RunComplianceRequest;
use Modules\ModuleComplianceCenter\Models\ComplianceManagedModule;
use Modules\ModuleComplianceCenter\Models\ComplianceRun;
use Modules\ModuleComplianceCenter\Services\AIConsensusComplianceBridge;
use Modules\ModuleComplianceCenter\Services\ComplianceApprovalService;
use Modules\ModuleComplianceCenter\Services\ComplianceModuleDiscoveryService;
use Modules\ModuleComplianceCenter\Services\ComplianceRunMaintenanceService;
use Modules\ModuleComplianceCenter\Services\ComplianceValidatorRegistry;
use Modules\ModuleComplianceCenter\Services\ModuleComplianceCenterGateway;
use Modules\ModuleComplianceCenter\Services\ProjectManagerComplianceBridge;

class ComplianceRunController extends Controller
{
    use PreparesComplianceCenterPage;

    public function index()
    {
        $runs = ComplianceRun::with('module')
            ->when(!request()->boolean('archived'), fn ($query) => $query->where('status', '!=', 'archived'))
            ->latest()
            ->get();

        $this->prepareCompliancePage('Compliance Runs', ['Runs'], [
            $this->actionLink('new', 'New', 'fa-solid fa-plus', 'module_compliance_center.runs.create'),
            [
                'key' => 'rerun-all',
                'label' => 'Clean & rerun all',
                'icon' => 'fa-solid fa-rotate',
                'url' => route('module_compliance_center.runs.rerun_all'),
                'type' => 'form',
                'method' => 'POST',
                'confirm' => 'Archive old compliance runs and create a new run for every active module?',
            ],
        ]);

        return view('module-compliance-center::runs.index', [
            'runs' => $runs,
        ]);
    }

    public function rerunAll(ComplianceRunMaintenanceService $maintenance): RedirectResponse
    {
        $result = $maintenance->archiveOldAndRunAll(optional(request()->user())->id);

        return redirect()
            ->route('module_compliance_center.runs.index')
            ->with('success', sprintf(
                'Archived %d old run(s) and created %d new run(s) for %d active module(s).',
                $result['archived'],
                $result['created'],
                $result['modules']
            ));
    }

    public function create(ComplianceValidatorRegistry $registry, ComplianceModuleDiscoveryService $discovery)
    {
        $discovery->discover();
        $this->prepareCompliancePage('Run Compliance', [
            ['label' => 'Runs', 'url' => route('module_compliance_center.runs.index')],
            'Create',
        ], [
            $this->actionLink('back', 'Back', 'fa-solid fa-angle-left', 'module_compliance_center.runs.index'),
        ]);

        return view('module-compliance-center::runs.create', [
            'modules' => ComplianceManagedModule::orderBy('module_name')->get(),
            'validators' => $registry->all(),
        ]);
    }

    public function store(RunComplianceRequest $request, ModuleComplianceCenterGateway $gateway): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $managedModule = !empty($data['managed_module_id'])
            ? ComplianceManagedModule::findOrFail($data['managed_module_id'])
            : null;

        $run = $gateway->run([
            'module_name' => $managedModule?->module_name ?? $data['module_name'],
            'module_path' => $managedModule?->module_path ?? $data['module_path'],
            'source_type' => $data['source_type'] ?? 'manual',
            'source_id' => $data['source_id'] ?? null,
            'validators' => $data['validators'] ?? null,
            'options' => [
                'async' => (bool) ($data['async'] ?? false),
                'generate_report' => (bool) ($data['generate_report'] ?? true),
            ],
            'requested_by' => optional($request->user())->id,
        ]);

        if ($request->expectsJson()) {
            return response()->json($run->fresh(['validators', 'results', 'report']), 201);
        }

        return redirect()->route('module_compliance_center.runs.show', $run)
            ->with('success', 'Compliance run created.');
    }

    public function show(ComplianceRun $run)
    {
        $run->load(['validators.results', 'results', 'report']);
        $this->prepareCompliancePage($run->module_name, [
            ['label' => 'Runs', 'url' => route('module_compliance_center.runs.index')],
            $run->uuid,
        ], [
            $this->actionLink('back', 'Back', 'fa-solid fa-angle-left', 'module_compliance_center.runs.index'),
            $this->actionLink('report', 'Report', 'fa-solid fa-file-lines', 'module_compliance_center.reports.show', ['run' => $run->id]),
        ]);

        return view('module-compliance-center::runs.show', compact('run'));
    }

    public function approve(ApproveComplianceRunRequest $request, ComplianceRun $run, ComplianceApprovalService $approval): RedirectResponse
    {
        $approval->approve($run, optional($request->user())->id);

        return back()->with('success', 'Run approved.');
    }

    public function reject(RejectComplianceRunRequest $request, ComplianceRun $run, ComplianceApprovalService $approval): RedirectResponse
    {
        $approval->reject($run, (string) ($request->validated()['reason'] ?? 'Rejected by compliance reviewer.'), optional($request->user())->id);

        return back()->with('success', 'Run rejected.');
    }

    public function requestChanges(RejectComplianceRunRequest $request, ComplianceRun $run, ComplianceApprovalService $approval): RedirectResponse
    {
        $approval->requestChanges($run, $request->validated()['reason'] ?? null, optional($request->user())->id);

        return back()->with('success', 'Changes requested.');
    }

    public function sendToAI(ComplianceRun $run, AIConsensusComplianceBridge $bridge): RedirectResponse
    {
        $id = $bridge->sendRunToAIConsensus($run->load('report', 'results'));

        if ($id && $run->report) {
            $run->report->update(['ai_consensus_run_id' => $id]);
        }

        return back()->with('success', $id ? 'Sent to AI Consensus.' : 'AI Consensus is not available; payload prepared only.');
    }

    public function createProjectTasks(ComplianceRun $run, ProjectManagerComplianceBridge $bridge): RedirectResponse
    {
        $tasks = $bridge->createTasksFromRun($run);

        if ($run->report) {
            $run->report->update(['project_tasks_payload' => $tasks]);
        }

        return back()->with('success', count($tasks) . ' task payloads prepared.');
    }
}
