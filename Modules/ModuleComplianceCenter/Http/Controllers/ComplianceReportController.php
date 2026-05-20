<?php

namespace Modules\ModuleComplianceCenter\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\ModuleComplianceCenter\Http\Controllers\Concerns\PreparesComplianceCenterPage;
use Modules\ModuleComplianceCenter\Models\ComplianceReport;
use Modules\ModuleComplianceCenter\Models\ComplianceRun;
use Modules\ModuleComplianceCenter\Services\ComplianceReportService;

class ComplianceReportController extends Controller
{
    use PreparesComplianceCenterPage;

    public function show(ComplianceRun $run, ComplianceReportService $reports)
    {
        $report = $run->report ?: $reports->generate($run);
        $this->prepareCompliancePage($report->title, [
            ['label' => 'Runs', 'url' => route('module_compliance_center.runs.index')],
            ['label' => $run->uuid, 'url' => route('module_compliance_center.runs.show', $run)],
            'Report',
        ], [
            $this->actionLink('back', 'Back', 'fa-solid fa-angle-left', 'module_compliance_center.runs.show', ['run' => $run->id]),
        ]);

        return view('module-compliance-center::reports.show', compact('run', 'report'));
    }

    public function export(ComplianceReport $report)
    {
        return response()->json($report->report_payload);
    }
}
