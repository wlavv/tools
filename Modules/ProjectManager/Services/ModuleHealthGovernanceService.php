<?php

namespace Modules\ProjectManager\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ModuleHealthGovernanceService
{
    private array $requiredComponents = [
        'module_json',
        'service_provider',
        'web_routes',
        'controllers',
        'models',
        'views',
        'migrations',
        'translations',
        'actions',
        'breadcrumbs',
        'permissions',
        'config',
    ];

    private array $recommendedComponents = [
        'diagnostics',
        'seeders',
        'notifications',
        'exports',
        'imports',
        'audit_logs',
    ];

    private array $optionalComponents = [
        'api_routes',
        'jobs',
        'events',
        'scheduler',
        'webhooks',
        'billing',
        'multi_tenant',
    ];

    private array $phaseThresholds = [
        'Discovery' => 0,
        'Foundation' => 35,
        'Operational' => 60,
        'Automation' => 78,
        'Productization' => 88,
        'SaaS' => 96,
    ];

    public function snapshot(): array
    {
        if (!Schema::hasTable('module_health_scans') || !Schema::hasTable('module_health_scan_items')) {
            return $this->emptySnapshot(false);
        }

        $scan = DB::table('module_health_scans')->latest('id')->first();
        if (!$scan) {
            return $this->emptySnapshot(true);
        }

        $items = DB::table('module_health_scan_items')
            ->where('scan_id', $scan->id)
            ->orderByRaw("FIELD(status, 'broken', 'incomplete', 'functional', 'enhanced')")
            ->orderBy('module_name')
            ->get()
            ->map(fn ($item) => $this->normalizeItem($item));

        $criticalModules = $items
            ->filter(fn ($item) => in_array($item->status, ['broken', 'incomplete'], true))
            ->take(8)
            ->values();

        $saasCandidates = $items
            ->filter(fn ($item) => $item->saas_score >= 85 && in_array($item->status, ['functional', 'enhanced'], true))
            ->sortByDesc('saas_score')
            ->take(6)
            ->values();

        return [
            'available' => true,
            'has_scan' => true,
            'scan' => $scan,
            'items' => $items,
            'critical_modules' => $criticalModules,
            'saas_candidates' => $saasCandidates,
            'component_groups' => [
                'required' => $this->componentCoverage($items, $this->requiredComponents),
                'recommended' => $this->componentCoverage($items, $this->recommendedComponents),
                'optional' => $this->componentCoverage($items, $this->optionalComponents),
            ],
            'phase_flow' => $this->phaseFlow($items),
            'counters' => [
                'modules' => (int) ($scan->modules_total ?? $items->count()),
                'broken' => (int) ($scan->broken_total ?? $items->where('status', 'broken')->count()),
                'incomplete' => (int) ($scan->incomplete_total ?? $items->where('status', 'incomplete')->count()),
                'functional' => (int) ($scan->functional_total ?? $items->where('status', 'functional')->count()),
                'enhanced' => (int) ($scan->enhanced_total ?? $items->where('status', 'enhanced')->count()),
                'saas_candidates' => $saasCandidates->count(),
                'dependency_impacts' => $criticalModules->count(),
            ],
        ];
    }

    private function normalizeItem(object $item): object
    {
        $components = collect($this->decodeJson($item->components ?? null));
        $missingRequired = $this->decodeJson($item->missing_required ?? null);
        $missingRecommended = $this->decodeJson($item->missing_recommended ?? null);
        $presentOptional = $this->decodeJson($item->present_optional ?? null);
        $recommendations = $this->decodeJson($item->recommendations ?? null);

        $item->components_list = $components;
        $item->missing_required_list = $missingRequired;
        $item->missing_recommended_list = $missingRecommended;
        $item->present_optional_list = $presentOptional;
        $item->recommendations_list = $recommendations;
        $item->saas_score = $this->saasScore($item, $presentOptional, $missingRequired, $missingRecommended);
        $item->execution_phase = $this->executionPhase((int) ($item->completion ?? 0), $item->status ?? 'unknown');
        $item->dependency_impact = $this->dependencyImpact($item, $missingRequired, $missingRecommended);

        return $item;
    }

    private function componentCoverage(Collection $items, array $components): Collection
    {
        return collect($components)->map(function (string $component) use ($items) {
            $found = $items->filter(fn ($item) => $this->componentPresent($item->components_list, $component))->count();
            $total = max(1, $items->count());

            return [
                'key' => $component,
                'label' => $this->componentLabel($component),
                'found' => $found,
                'missing' => max(0, $items->count() - $found),
                'coverage' => (int) round(($found / $total) * 100),
            ];
        })->values();
    }

    private function phaseFlow(Collection $items): Collection
    {
        return collect(array_keys($this->phaseThresholds))->map(function (string $phase) use ($items) {
            $phaseItems = $items->filter(fn ($item) => $item->execution_phase === $phase);

            return [
                'label' => $phase,
                'count' => $phaseItems->count(),
                'blocked' => $phaseItems->whereIn('status', ['broken', 'incomplete'])->count(),
                'ready' => $phaseItems->whereIn('status', ['functional', 'enhanced'])->count(),
            ];
        })->values();
    }

    private function componentPresent(Collection $components, string $key): bool
    {
        return $components->contains(function ($component) use ($key) {
            return ($component['key'] ?? $component['component'] ?? null) === $key
                || ($component['label'] ?? null) === $key
                || (($component['present'] ?? false) && in_array($key, $component['matches'] ?? [], true));
        });
    }

    private function saasScore(object $item, array $presentOptional, array $missingRequired, array $missingRecommended): int
    {
        $optionalKeys = $this->componentKeys($presentOptional);
        $score = (int) ($item->completion ?? 0);
        $score += count(array_intersect($optionalKeys, ['api_routes', 'jobs', 'events', 'webhooks', 'billing', 'multi_tenant'])) * 2;
        $score -= count($missingRequired) * 8;
        $score -= count($missingRecommended) * 2;

        return max(0, min(100, $score));
    }

    private function executionPhase(int $completion, string $status): string
    {
        if ($status === 'broken') {
            return 'Discovery';
        }

        $phase = 'Discovery';
        foreach ($this->phaseThresholds as $label => $threshold) {
            if ($completion >= $threshold) {
                $phase = $label;
            }
        }

        return $phase;
    }

    private function dependencyImpact(object $item, array $missingRequired, array $missingRecommended): string
    {
        if (!empty($missingRequired)) {
            return 'Blocks closure';
        }
        if (!empty($missingRecommended)) {
            return 'Limits automation';
        }
        if ((int) ($item->completion ?? 0) >= 90) {
            return 'Productization candidate';
        }

        return 'Monitor';
    }

    private function componentLabel(string $key): string
    {
        return ucwords(str_replace(['_', '-'], ' ', $key));
    }

    private function componentKeys(array $components): array
    {
        return collect($components)
            ->map(fn ($component) => is_array($component) ? ($component['key'] ?? $component['component'] ?? null) : $component)
            ->filter()
            ->values()
            ->all();
    }

    private function decodeJson($value): array
    {
        if (is_array($value)) {
            return $value;
        }
        if (!$value) {
            return [];
        }

        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function emptySnapshot(bool $available): array
    {
        return [
            'available' => $available,
            'has_scan' => false,
            'scan' => null,
            'items' => collect(),
            'critical_modules' => collect(),
            'saas_candidates' => collect(),
            'component_groups' => [
                'required' => collect(),
                'recommended' => collect(),
                'optional' => collect(),
            ],
            'phase_flow' => collect(),
            'counters' => [
                'modules' => 0,
                'broken' => 0,
                'incomplete' => 0,
                'functional' => 0,
                'enhanced' => 0,
                'saas_candidates' => 0,
                'dependency_impacts' => 0,
            ],
        ];
    }
}
