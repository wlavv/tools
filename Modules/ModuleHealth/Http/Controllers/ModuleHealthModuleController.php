<?php

namespace Modules\ModuleHealth\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\ModuleHealth\Models\ModuleHealthScanItem;
use Modules\ModuleHealth\Services\ModuleHealthScanService;

class ModuleHealthModuleController extends Controller
{
    public function index(ModuleHealthScanService $scanService)
    {
        $scan = $scanService->latestOrRun();
        $items = $scan->items()->orderByRaw("FIELD(status, 'broken', 'incomplete', 'functional', 'enhanced')")->orderBy('module_name')->get();

        return $this->view('module-health::modules.index', compact('scan', 'items'));
    }

    public function show(ModuleHealthScanItem $item)
    {
        return $this->view('module-health::modules.show', compact('item'));
    }
}
