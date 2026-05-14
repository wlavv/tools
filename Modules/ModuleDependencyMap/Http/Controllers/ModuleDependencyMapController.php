<?php

namespace Modules\ModuleDependencyMap\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\ModuleDependencyMap\Models\ModuleDependency;
use Modules\ModuleDependencyMap\Models\ModuleDependencyScan;
use Modules\ModuleDependencyMap\Services\ModuleDependencyMapBuilder;
use Modules\ModuleDependencyMap\Services\ModuleDependencyScanner;

class ModuleDependencyMapController extends Controller
{
    public function index(ModuleDependencyMapBuilder $builder): View
    {
        return $this->view('module-dependency-map::index', [
            'items' => $builder->items(),
            'freshDays' => (int) config('module-dependency-map.fresh_days', 15),
        ]);
    }

    public function show(string $module, ModuleDependencyScanner $scanner): View
    {
        abort_unless($scanner->moduleExists($module), 404);

        $latestSuccessfulScan = ModuleDependencyScan::query()
            ->where('module_name', $module)
            ->successful()
            ->latest('finished_at')
            ->first();

        $latestScan = ModuleDependencyScan::query()
            ->where('module_name', $module)
            ->latest('created_at')
            ->first();

        $dependencies = ModuleDependency::query()
            ->active()
            ->where('source_module', $module)
            ->orderBy('target_module')
            ->orderBy('file_path')
            ->orderBy('line_number')
            ->get();

        $dependents = ModuleDependency::query()
            ->active()
            ->where('target_module', $module)
            ->orderBy('source_module')
            ->orderBy('file_path')
            ->orderBy('line_number')
            ->get();

        return $this->view('module-dependency-map::show', [
            'module' => $module,
            'latestSuccessfulScan' => $latestSuccessfulScan,
            'latestScan' => $latestScan,
            'dependencies' => $dependencies,
            'dependents' => $dependents,
        ]);
    }

    public function run(string $module, ModuleDependencyScanner $scanner): RedirectResponse
    {
        $scan = $scanner->scan($module, auth()->id());

        return redirect()
            ->route(config('module-dependency-map.route_name', 'module-dependency-map.') . 'show', $module)
            ->with(
                $scan->status === ModuleDependencyScan::STATUS_SUCCESS ? 'success' : 'error',
                $scan->status === ModuleDependencyScan::STATUS_SUCCESS
                    ? 'Dependency scan completed.'
                    : 'Dependency scan failed: ' . $scan->error_message
            );
    }

    public function runAll(ModuleDependencyScanner $scanner): RedirectResponse
    {
        $success = 0;
        $failed = 0;

        foreach ($scanner->modules() as $module) {
            $scan = $scanner->scan($module, auth()->id());

            if ($scan->status === ModuleDependencyScan::STATUS_SUCCESS) {
                $success++;
            } else {
                $failed++;
            }
        }

        return redirect()
            ->route(config('module-dependency-map.route_name', 'module-dependency-map.') . 'index')
            ->with('success', "Run all completed. Success: {$success}. Failed: {$failed}.");
    }
}
