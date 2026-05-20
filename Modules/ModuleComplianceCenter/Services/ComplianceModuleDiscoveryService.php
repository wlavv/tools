<?php

namespace Modules\ModuleComplianceCenter\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Modules\ModuleComplianceCenter\Models\ComplianceManagedModule;

class ComplianceModuleDiscoveryService
{
    public function discover(): array
    {
        $basePath = $this->modulesBasePath();

        if (!File::isDirectory($basePath)) {
            return [];
        }

        return collect(File::directories($basePath))
            ->map(fn (string $path) => $this->syncFromPath($path))
            ->filter()
            ->values()
            ->all();
    }

    public function findOrRegister(array $payload): ComplianceManagedModule
    {
        $modulePath = $this->assertSafeModulePath((string) $payload['module_path']);
        $manifest = $this->readManifest($modulePath);
        $moduleName = (string) ($payload['module_name'] ?? $manifest['name'] ?? basename($modulePath));
        $slug = (string) ($manifest['slug'] ?? Str::slug($moduleName));

        return ComplianceManagedModule::updateOrCreate(
            ['module_slug' => $slug],
            [
                'module_name' => $moduleName,
                'module_path' => $modulePath,
                'module_version' => $manifest['version'] ?? null,
                'module_description' => $manifest['description'] ?? null,
                'manifest_payload' => $manifest ?: null,
                'is_active' => true,
            ]
        );
    }

    public function syncFromPath(string $path): ?ComplianceManagedModule
    {
        try {
            $path = $this->assertSafeModulePath($path);
        } catch (InvalidArgumentException) {
            return null;
        }

        $manifest = $this->readManifest($path);
        $moduleName = (string) ($manifest['name'] ?? basename($path));
        $slug = (string) ($manifest['slug'] ?? Str::slug($moduleName));

        return ComplianceManagedModule::updateOrCreate(
            ['module_slug' => $slug],
            [
                'module_name' => $moduleName,
                'module_path' => $path,
                'module_version' => $manifest['version'] ?? null,
                'module_description' => $manifest['description'] ?? null,
                'manifest_payload' => $manifest ?: null,
                'is_active' => true,
            ]
        );
    }

    public function assertSafeModulePath(string $path): string
    {
        $resolved = realpath($path) ?: $path;
        $base = $this->modulesBasePath();
        $resolvedBase = realpath($base) ?: $base;

        if (!Str::startsWith(str_replace('\\', '/', $resolved), str_replace('\\', '/', $resolvedBase))) {
            throw new InvalidArgumentException('Module path must be inside the Modules directory.');
        }

        return $resolved;
    }

    private function readManifest(string $path): array
    {
        $manifestPath = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'module.json';

        if (!File::exists($manifestPath)) {
            return [];
        }

        $decoded = json_decode(File::get($manifestPath), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function modulesBasePath(): string
    {
        return (string) config('module-compliance-center.module_base_path', base_path('Modules'));
    }
}
