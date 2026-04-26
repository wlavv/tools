<?php

namespace Modules\SystemTools\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\SystemTools\Services\MaintenanceActionService;

class MaintenanceController extends Controller
{
    public function index(MaintenanceActionService $service)
    {
        $tools = $service->all();
        $sections = config('system-tools.sections', []);
        $riskLabels = config('system-tools.risk_labels', []);

        return view('system-tools::maintenance.index', compact('tools', 'sections', 'riskLabels'));
    }

    public function run(Request $request, string $action, MaintenanceActionService $service)
    {
        $result = $service->run($action, false);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }

        return back()->with('system_tools_result', $result);
    }
}
