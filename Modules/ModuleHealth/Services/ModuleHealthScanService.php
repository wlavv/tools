<?php

namespace Modules\ModuleHealth\Services;

use Illuminate\Support\Carbon;
use Modules\ModuleHealth\Models\ModuleHealthScan;
use Modules\ModuleHealth\Models\ModuleHealthScanItem;

class ModuleHealthScanService
{
    public function __construct(
        protected ModuleHealthScanner $scanner
    ) {
    }

    public function run(?string $profile = null): ModuleHealthScan
    {
        $startedAt = Carbon::now();
        $results = $this->scanner->scanAll($profile);

        $scan = ModuleHealthScan::create([
            'status' => 'completed',
            'modules_total' => count($results),
            'broken_total' => collect($results)->where('status', 'broken')->count(),
            'incomplete_total' => collect($results)->where('status', 'incomplete')->count(),
            'functional_total' => collect($results)->where('status', 'functional')->count(),
            'enhanced_total' => collect($results)->where('status', 'enhanced')->count(),
            'summary' => [
                'profile' => $profile ?: config('module-health.default_profile'),
                'average_completion' => (int) round(collect($results)->avg('completion') ?: 0),
            ],
            'started_at' => $startedAt,
            'finished_at' => Carbon::now(),
        ]);

        foreach ($results as $result) {
            $result['scan_id'] = $scan->id;
            ModuleHealthScanItem::create($result);
        }

        return $scan->load('items');
    }

    public function latestScan(): ?ModuleHealthScan
    {
        return ModuleHealthScan::with('items')->latest('id')->first();
    }

    public function latestOrRun(): ModuleHealthScan
    {
        return $this->latestScan() ?: $this->run();
    }
}
