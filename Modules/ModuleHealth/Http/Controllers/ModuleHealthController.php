<?php

namespace Modules\ModuleHealth\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\ModuleHealth\Models\ModuleHealthScan;
use Modules\ModuleHealth\Services\ModuleHealthScanService;

class ModuleHealthController extends Controller
{
    public function index(ModuleHealthScanService $scanService)
    {
        $scan = $scanService->latestScan();
        $items = $scan ? $scan->items()->orderBy('module_name')->get() : collect();

        return $this->view('module-health::dashboard.index', compact('scan', 'items'));
    }

    public function runScan(ModuleHealthScanService $scanService)
    {
        $scanService->run(request('profile'));

        return redirect()->route('module_health.index')->with('success', __('module-health::messages.scan_completed'));
    }

    public function scans()
    {
        $scans = ModuleHealthScan::latest('id')->paginate(20);

        return $this->view('module-health::scans.index', compact('scans'));
    }
}
