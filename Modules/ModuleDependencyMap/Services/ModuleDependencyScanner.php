<?php

namespace Modules\ModuleDependencyMap\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Modules\ModuleDependencyMap\Models\ModuleDependency;
use Modules\ModuleDependencyMap\Models\ModuleDependencyScan;
use Symfony\Component\Finder\Finder;
use Throwable;

class ModuleDependencyScanner
{
    public function modules(): Collection
    {
        $path = $this->modulesPath();

        if (! is_dir($path)) {
            return collect();
        }

        $ignoredModules = collect(config('module-dependency-map.ignored_modules', []))
            ->map(fn (string $module): string => strtolower($module))
            ->all();

        return collect(File::directories($path))
            ->map(fn (string $directory): string => basename($directory))
            ->reject(fn (string $module): bool => in_array(strtolower($module), $ignoredModules, true))
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    public function scan(string $module, ?int $userId = null): ModuleDependencyScan
    {
        $module = trim($module);
        $this->assertValidModule($module);
        $this->configureRuntimeLimit();

        $scan = ModuleDependencyScan::create([
            'module_name' => $module,
            'status' => ModuleDependencyScan::STATUS_RUNNING,
            'health_status' => ModuleDependencyScan::HEALTH_UNKNOWN,
            'started_at' => now(),
            'triggered_by' => $userId,
        ]);

        try {
            $detectedDependencies = $this->detectDependencies($module);

            DB::transaction(function () use ($module, $scan, $detectedDependencies): void {
                ModuleDependency::query()
                    ->where('source_module', $module)
                    ->update(['is_active' => false]);

                foreach ($detectedDependencies as $dependencyData) {
                    $dependency = ModuleDependency::firstOrNew([
                        'evidence_hash' => $dependencyData['evidence_hash'],
                    ]);

                    if (! $dependency->exists) {
                        $dependency->first_detected_at = now();
                    }

                    $dependency->fill(array_merge($dependencyData, [
                        'is_active' => true,
                        'last_detected_at' => now(),
                        'latest_scan_id' => $scan->id,
                    ]));

                    $dependency->save();
                }
            });

            $metrics = $this->metricsFor($module);
            $riskScore = $this->calculateRiskScore($metrics);

            $scan->update([
                'status' => ModuleDependencyScan::STATUS_SUCCESS,
                'health_status' => $this->healthStatus($riskScore),
                'risk_score' => $riskScore,
                'direct_dependencies_count' => $metrics['direct_dependencies'],
                'dependents_count' => $metrics['dependents'],
                'circular_dependencies_count' => $metrics['circular_dependencies'],
                'critical_dependencies_count' => $metrics['critical_dependencies'],
                'stale_dependencies_count' => $metrics['stale_dependencies'],
                'finished_at' => now(),
                'metadata' => $metrics,
            ]);
        } catch (Throwable $exception) {
            $scan->update([
                'status' => ModuleDependencyScan::STATUS_FAILED,
                'error_message' => $exception->getMessage(),
                'finished_at' => now(),
            ]);
        }

        return $scan->fresh();
    }

    public function detectDependencies(string $sourceModule): Collection
    {
        $sourcePath = $this->modulePath($sourceModule);
        $targetModules = $this->modules()
            ->reject(fn (string $module): bool => $module === $sourceModule)
            ->values();

        if ($targetModules->isEmpty()) {
            return collect();
        }

        $finder = Finder::create()
            ->files()
            ->in($sourcePath);

        $extensions = config('module-dependency-map.file_extensions', ['php']);
        foreach ($extensions as $extension) {
            $finder->name('*.' . ltrim((string) $extension, '.'));
        }

        foreach (config('module-dependency-map.ignored_directories', []) as $ignoredDirectory) {
            $finder->exclude($ignoredDirectory);
        }

        $dependencies = collect();
        $namespacePrefix = trim((string) config('module-dependency-map.namespace_prefix', 'Modules'), '\\');

        foreach ($finder as $file) {
            $absolutePath = $file->getRealPath();

            if (! $absolutePath || ! is_readable($absolutePath)) {
                continue;
            }

            $relativePath = $this->relativePath($absolutePath);
            $lines = file($absolutePath, FILE_IGNORE_NEW_LINES);

            if ($lines === false) {
                continue;
            }

            $insideBlockComment = false;

            foreach ($lines as $index => $line) {
                $lineNumber = $index + 1;

                if ($this->isIgnorableLine($line, $insideBlockComment)) {
                    continue;
                }

                foreach ($targetModules as $targetModule) {
                    $needle = $namespacePrefix . '\\' . $targetModule . '\\';

                    if (! Str::contains($line, $needle)) {
                        continue;
                    }

                    $dependencyType = $this->classifyDependencyLine($line);
                    $reference = Str::limit(trim($line), (int) config('module-dependency-map.max_reference_length', 500), '...');

                    $hashPayload = implode('|', [
                        $sourceModule,
                        $targetModule,
                        $dependencyType,
                        $relativePath,
                        $lineNumber,
                        $reference,
                    ]);

                    $dependencies->push([
                        'source_module' => $sourceModule,
                        'target_module' => $targetModule,
                        'dependency_type' => $dependencyType,
                        'file_path' => $relativePath,
                        'line_number' => $lineNumber,
                        'reference' => $reference,
                        'confidence' => 100,
                        'evidence_hash' => sha1($hashPayload),
                    ]);
                }
            }
        }

        return $dependencies
            ->unique('evidence_hash')
            ->values();
    }

    public function metricsFor(string $module): array
    {
        $directTargets = ModuleDependency::query()
            ->active()
            ->where('source_module', $module)
            ->distinct()
            ->pluck('target_module')
            ->values();

        $directDependencies = $directTargets->count();

        $dependents = ModuleDependency::query()
            ->active()
            ->where('target_module', $module)
            ->distinct()
            ->count('source_module');

        $criticalModules = config('module-dependency-map.critical_modules', []);
        $criticalDependencies = $directTargets
            ->filter(fn (string $target): bool => in_array($target, $criticalModules, true))
            ->count();

        $circularTargets = $this->circularTargets($module);
        $staleDependencies = $this->staleDependencies($directTargets);

        return [
            'direct_dependencies' => $directDependencies,
            'dependents' => $dependents,
            'circular_dependencies' => $circularTargets->count(),
            'circular_targets' => $circularTargets->values()->all(),
            'critical_dependencies' => $criticalDependencies,
            'stale_dependencies' => $staleDependencies,
            'scanned_modules_count' => ModuleDependencyScan::query()->successful()->distinct()->count('module_name'),
            'known_modules_count' => $this->modules()->count(),
        ];
    }

    public function moduleExists(string $module): bool
    {
        return $this->modules()->contains($module);
    }

    private function classifyDependencyLine(string $line): string
    {
        $trimmed = trim($line);

        if (Str::startsWith($trimmed, 'use ')) {
            return 'import';
        }

        if (Str::contains($line, '::class')) {
            return 'class_reference';
        }

        if (Str::contains($line, ['app(', 'resolve(', 'make('])) {
            return 'container_resolution';
        }

        if (Str::contains($line, ['event(', 'dispatch(', 'Listener', 'Job'])) {
            return 'event_or_job';
        }

        return 'namespace_reference';
    }

    private function isIgnorableLine(string $line, bool &$insideBlockComment): bool
    {
        $trimmed = trim($line);

        if ($insideBlockComment) {
            if (Str::contains($trimmed, '*/')) {
                $insideBlockComment = false;
            }

            return true;
        }

        if ($trimmed === '') {
            return true;
        }

        if (Str::startsWith($trimmed, ['//', '#'])) {
            return true;
        }

        if (Str::startsWith($trimmed, ['/*', '*'])) {
            if (! Str::contains($trimmed, '*/')) {
                $insideBlockComment = true;
            }

            return true;
        }

        return false;
    }

    private function circularTargets(string $module): Collection
    {
        $edges = ModuleDependency::query()
            ->active()
            ->select('source_module', 'target_module')
            ->distinct()
            ->get();

        $adjacency = [];
        foreach ($edges as $edge) {
            $adjacency[$edge->source_module][] = $edge->target_module;
        }

        $directTargets = collect($adjacency[$module] ?? [])->unique()->values();

        return $directTargets
            ->filter(fn (string $target): bool => $this->canReach($target, $module, $adjacency))
            ->values();
    }

    private function canReach(string $start, string $goal, array $adjacency): bool
    {
        $stack = [$start];
        $visited = [];

        while ($stack !== []) {
            $current = array_pop($stack);

            if ($current === $goal) {
                return true;
            }

            if (isset($visited[$current])) {
                continue;
            }

            $visited[$current] = true;

            foreach ($adjacency[$current] ?? [] as $next) {
                if (! isset($visited[$next])) {
                    $stack[] = $next;
                }
            }
        }

        return false;
    }

    private function staleDependencies(Collection $directTargets): int
    {
        if ($directTargets->isEmpty()) {
            return 0;
        }

        $freshFrom = now()->subDays((int) config('module-dependency-map.fresh_days', 15))->startOfDay();
        $latestSuccessfulScans = ModuleDependencyScan::query()
            ->successful()
            ->whereIn('module_name', $directTargets)
            ->orderByDesc('finished_at')
            ->get()
            ->unique('module_name')
            ->keyBy('module_name');

        return $directTargets
            ->filter(function (string $target) use ($latestSuccessfulScans, $freshFrom): bool {
                $scan = $latestSuccessfulScans->get($target);

                return ! $scan || ! $scan->finished_at || $scan->finished_at->lt($freshFrom);
            })
            ->count();
    }

    private function calculateRiskScore(array $metrics): int
    {
        $weights = config('module-dependency-map.risk_weights', []);

        $score =
            ($metrics['direct_dependencies'] * ($weights['direct_dependency'] ?? 3)) +
            ($metrics['dependents'] * ($weights['dependent'] ?? 7)) +
            ($metrics['circular_dependencies'] * ($weights['circular_dependency'] ?? 25)) +
            ($metrics['critical_dependencies'] * ($weights['critical_dependency'] ?? 10)) +
            ($metrics['stale_dependencies'] * ($weights['stale_dependency'] ?? 5));

        return min(100, max(0, (int) $score));
    }

    private function healthStatus(int $riskScore): string
    {
        $thresholds = config('module-dependency-map.health_thresholds', []);

        return match (true) {
            $riskScore >= ($thresholds['critical'] ?? 80) => ModuleDependencyScan::HEALTH_CRITICAL,
            $riskScore >= ($thresholds['risky'] ?? 60) => ModuleDependencyScan::HEALTH_RISKY,
            $riskScore >= ($thresholds['warning'] ?? 30) => ModuleDependencyScan::HEALTH_WARNING,
            default => ModuleDependencyScan::HEALTH_HEALTHY,
        };
    }

    private function assertValidModule(string $module): void
    {
        abort_unless($this->moduleExists($module), 404, "Module [{$module}] not found.");
    }

    private function modulePath(string $module): string
    {
        return $this->modulesPath() . DIRECTORY_SEPARATOR . $module;
    }

    private function modulesPath(): string
    {
        return rtrim((string) config('module-dependency-map.modules_path', base_path('Modules')), DIRECTORY_SEPARATOR);
    }

    private function relativePath(string $absolutePath): string
    {
        $basePath = rtrim(base_path(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        return Str::after($absolutePath, $basePath);
    }

    private function configureRuntimeLimit(): void
    {
        $seconds = (int) config('module-dependency-map.scan_timeout_seconds', 120);

        if ($seconds > 0 && function_exists('set_time_limit')) {
            @set_time_limit($seconds);
        }
    }
}
