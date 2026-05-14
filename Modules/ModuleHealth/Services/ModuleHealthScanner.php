<?php

namespace Modules\ModuleHealth\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Modules\ModuleHealth\Support\ModuleComponentRegistry;

class ModuleHealthScanner
{
    public function scanAll(?string $profileKey = null): array
    {
        $modulesPath = config('module-health.modules_path', base_path('Modules'));
        $profileKey = $profileKey ?: config('module-health.default_profile', 'structural');

        if (! File::isDirectory($modulesPath)) {
            return [];
        }

        $modules = collect(File::directories($modulesPath))
            ->map(fn (string $path) => $this->scanModule($path, $profileKey))
            ->filter()
            ->sortBy('module_name')
            ->values()
            ->all();

        return $modules;
    }

    public function scanModule(string $modulePath, ?string $profileKey = null): ?array
    {
        if (! File::isDirectory($modulePath)) {
            return null;
        }

        $manifest = $this->readManifest($modulePath);
        $moduleName = Arr::get($manifest, 'name', basename($modulePath));
        $moduleSlug = Arr::get($manifest, 'slug', Arr::get($manifest, 'alias'));
        $profileKey = $this->resolveProfile($manifest, $profileKey);
        $profile = config('module-health.profiles.' . $profileKey, config('module-health.profiles.structural'));

        $components = $this->detectComponents($modulePath, $profile);
        $required = $this->groupStats($components, 'required');
        $recommended = $this->groupStats($components, 'recommended');
        $optional = $this->groupStats($components, 'optional');
        $completion = $this->calculateCompletion($required, $recommended);
        $status = $this->calculateStatus($required, $recommended, $optional);
        $missingRequired = $this->missingByGroup($components, 'required');
        $missingRecommended = $this->missingByGroup($components, 'recommended');
        $presentOptional = $this->presentByGroup($components, 'optional');

        return [
            'module_name' => $moduleName,
            'module_slug' => $moduleSlug,
            'module_path' => $modulePath,
            'profile' => $profileKey,
            'status' => $status,
            'completion' => $completion,
            'required_found' => $required['found'],
            'required_total' => $required['total'],
            'recommended_found' => $recommended['found'],
            'recommended_total' => $recommended['total'],
            'optional_found' => $optional['found'],
            'optional_total' => $optional['total'],
            'components' => $components,
            'missing_required' => $missingRequired,
            'missing_recommended' => $missingRecommended,
            'present_optional' => $presentOptional,
            'recommendations' => $this->buildRecommendations($missingRequired, $missingRecommended, $presentOptional),
        ];
    }

    protected function readManifest(string $modulePath): array
    {
        $file = $modulePath . DIRECTORY_SEPARATOR . 'module.json';

        if (! File::exists($file)) {
            return [];
        }

        $decoded = json_decode(File::get($file), true);

        return is_array($decoded) ? $decoded : [];
    }

    protected function resolveProfile(array $manifest, ?string $requestedProfile): string
    {
        $available = array_keys(config('module-health.profiles', []));
        $candidate = $requestedProfile ?: Arr::get($manifest, 'health_profile');

        if ($candidate && in_array($candidate, $available, true)) {
            return $candidate;
        }

        return config('module-health.default_profile', 'structural');
    }

    protected function detectComponents(string $modulePath, array $profile): array
    {
        $registry = ModuleComponentRegistry::checks();
        $components = [];

        foreach (['required', 'recommended', 'optional'] as $group) {
            foreach (($profile[$group] ?? []) as $key) {
                $definition = $registry[$key] ?? ['label' => $key, 'paths' => []];
                $matches = $this->findMatches($modulePath, $definition['paths'] ?? []);

                $components[] = [
                    'key' => $key,
                    'label' => $definition['label'] ?? $key,
                    'group' => $group,
                    'present' => count($matches) > 0,
                    'matches' => $matches,
                ];
            }
        }

        return $components;
    }

    protected function findMatches(string $modulePath, array $patterns): array
    {
        $matches = [];

        foreach ($patterns as $pattern) {
            $fullPattern = rtrim($modulePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $pattern;
            foreach (glob($fullPattern, GLOB_BRACE) ?: [] as $match) {
                $matches[] = str_replace($modulePath . DIRECTORY_SEPARATOR, '', $match);
            }
        }

        return array_values(array_unique($matches));
    }

    protected function groupStats(array $components, string $group): array
    {
        $filtered = array_values(array_filter($components, fn ($item) => $item['group'] === $group));

        return [
            'found' => count(array_filter($filtered, fn ($item) => $item['present'] === true)),
            'total' => count($filtered),
        ];
    }

    protected function calculateCompletion(array $required, array $recommended): int
    {
        $requiredWeight = 70;
        $recommendedWeight = 30;
        $requiredScore = $required['total'] > 0 ? ($required['found'] / $required['total']) * $requiredWeight : $requiredWeight;
        $recommendedScore = $recommended['total'] > 0 ? ($recommended['found'] / $recommended['total']) * $recommendedWeight : $recommendedWeight;

        return (int) round(min(100, $requiredScore + $recommendedScore));
    }

    protected function calculateStatus(array $required, array $recommended, array $optional): string
    {
        if ($required['found'] < $required['total']) {
            return $required['found'] <= max(1, floor($required['total'] * 0.6)) ? 'broken' : 'incomplete';
        }

        if ($recommended['total'] > 0 && $recommended['found'] < $recommended['total']) {
            return 'functional';
        }

        if ($optional['found'] > 0) {
            return 'enhanced';
        }

        return 'functional';
    }

    protected function missingByGroup(array $components, string $group): array
    {
        return array_values(array_map(
            fn ($item) => ['key' => $item['key'], 'label' => $item['label']],
            array_filter($components, fn ($item) => $item['group'] === $group && ! $item['present'])
        ));
    }

    protected function presentByGroup(array $components, string $group): array
    {
        return array_values(array_map(
            fn ($item) => ['key' => $item['key'], 'label' => $item['label']],
            array_filter($components, fn ($item) => $item['group'] === $group && $item['present'])
        ));
    }

    protected function buildRecommendations(array $missingRequired, array $missingRecommended, array $presentOptional): array
    {
        $recommendations = [];

        foreach ($missingRequired as $item) {
            $recommendations[] = [
                'type' => 'critical',
                'component' => $item['key'],
                'label' => 'Add required component: ' . $item['label'],
            ];
        }

        foreach ($missingRecommended as $item) {
            $recommendations[] = [
                'type' => 'recommended',
                'component' => $item['key'],
                'label' => 'Improve module with: ' . $item['label'],
            ];
        }

        if (count($presentOptional) >= 3) {
            $recommendations[] = [
                'type' => 'opportunity',
                'component' => 'enhancement',
                'label' => 'Module already has optional capabilities and may be a candidate for advanced maturity review.',
            ];
        }

        return $recommendations;
    }
}
