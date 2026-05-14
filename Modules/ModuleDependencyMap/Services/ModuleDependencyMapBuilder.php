<?php

namespace Modules\ModuleDependencyMap\Services;

use Illuminate\Support\Collection;
use Modules\ModuleDependencyMap\Models\ModuleDependency;
use Modules\ModuleDependencyMap\Models\ModuleDependencyScan;

class ModuleDependencyMapBuilder
{
    public function __construct(private readonly ModuleDependencyScanner $scanner)
    {
    }

    public function items(): Collection
    {
        $modules = $this->scanner->modules();

        if ($modules->isEmpty()) {
            return collect();
        }

        $latestSuccessfulScans = ModuleDependencyScan::query()
            ->successful()
            ->whereIn('module_name', $modules)
            ->orderByDesc('finished_at')
            ->get()
            ->unique('module_name')
            ->keyBy('module_name');

        $latestScans = ModuleDependencyScan::query()
            ->whereIn('module_name', $modules)
            ->orderByDesc('created_at')
            ->get()
            ->unique('module_name')
            ->keyBy('module_name');

        $outgoingCounts = ModuleDependency::query()
            ->active()
            ->whereIn('source_module', $modules)
            ->selectRaw('source_module, COUNT(DISTINCT target_module) as dependencies_count')
            ->groupBy('source_module')
            ->pluck('dependencies_count', 'source_module');

        $incomingCounts = ModuleDependency::query()
            ->active()
            ->whereIn('target_module', $modules)
            ->selectRaw('target_module, COUNT(DISTINCT source_module) as dependents_count')
            ->groupBy('target_module')
            ->pluck('dependents_count', 'target_module');

        return $modules->map(function (string $module) use ($latestSuccessfulScans, $latestScans, $outgoingCounts, $incomingCounts): array {
            $successfulScan = $latestSuccessfulScans->get($module);
            $latestScan = $latestScans->get($module);
            $freshness = $this->freshness($successfulScan);

            return [
                'name' => $module,
                'latest_successful_scan' => $successfulScan,
                'latest_scan' => $latestScan,
                'freshness' => $freshness,
                'row_class' => $this->rowClass($freshness),
                'health_badge_class' => $this->healthBadgeClass($successfulScan?->health_status),
                'outgoing_count' => (int) ($outgoingCounts[$module] ?? 0),
                'incoming_count' => (int) ($incomingCounts[$module] ?? 0),
            ];
        });
    }

    public function freshness(?ModuleDependencyScan $scan): string
    {
        if (! $scan || ! $scan->finished_at) {
            return 'never';
        }

        if ($scan->finished_at->isToday()) {
            return 'today';
        }

        $freshFrom = now()->subDays((int) config('module-dependency-map.fresh_days', 15))->startOfDay();

        if ($scan->finished_at->greaterThanOrEqualTo($freshFrom)) {
            return 'recent';
        }

        return 'stale';
    }

    public function rowClass(string $freshness): string
    {
        return match ($freshness) {
            'today' => 'table-success',
            'recent' => 'table-warning',
            'stale' => 'table-danger',
            default => '',
        };
    }

    public function healthBadgeClass(?string $healthStatus): string
    {
        return match ($healthStatus) {
            ModuleDependencyScan::HEALTH_HEALTHY => 'bg-success',
            ModuleDependencyScan::HEALTH_WARNING => 'bg-warning text-dark',
            ModuleDependencyScan::HEALTH_RISKY => 'bg-danger',
            ModuleDependencyScan::HEALTH_CRITICAL => 'bg-dark',
            default => 'bg-secondary',
        };
    }
}
