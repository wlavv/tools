<?php

namespace Modules\TranslationManager\Services;

use Illuminate\Support\Str;
use SplFileInfo;

class ModuleTranslationDiscoveryService
{
    public function listModules(string $locale): array
    {
        $modulesPath = config('translation-manager.modules_path', base_path('Modules'));
        $modules = $this->systemSources($locale);

        if (! is_dir($modulesPath)) {
            return $modules;
        }

        foreach (glob($modulesPath . '/*', GLOB_ONLYDIR) ?: [] as $modulePath) {
            $module = $this->modulePayload($modulePath);

            if (! $module) {
                continue;
            }

            $module['files'] = $this->listTranslationFiles($module, $locale);
            $module['stats'] = $this->aggregateStats($module['files']);
            $modules[] = $module;
        }

        usort($modules, fn ($a, $b) => strcasecmp($a['name'], $b['name']));

        return $modules;
    }

    public function findModule(string $slug): ?array
    {
        foreach ($this->systemSources(config('translation-manager.default_locale', app()->getLocale())) as $source) {
            if ($source['slug'] === $slug) {
                return $source;
            }
        }

        $modulesPath = config('translation-manager.modules_path', base_path('Modules'));

        foreach (glob($modulesPath . '/*', GLOB_ONLYDIR) ?: [] as $modulePath) {
            $module = $this->modulePayload($modulePath);

            if ($module && $module['slug'] === $slug) {
                return $module;
            }
        }

        return null;
    }

    public function listTranslationFiles(array $module, string $locale): array
    {
        $files = [];

        foreach ($this->baseLocalePaths($module, $locale) as $baseLocalePath) {
            foreach (glob($baseLocalePath . '/*.php') ?: [] as $filePath) {
                $file = basename($filePath);
                $customPath = $this->overridePath($module['slug'], $locale, $file);
                $files[$file] = [
                    'file' => $file,
                    'base_path' => $filePath,
                    'custom_path' => $customPath,
                    'custom_exists' => file_exists($customPath),
                ];
            }
        }

        ksort($files);

        return array_map(function (array $file) use ($module, $locale) {
            $reader = app(ModuleTranslationReaderService::class);
            $payload = $reader->read($module, $locale, $file['file']);
            $file['stats'] = $payload['stats'];
            $file['status'] = $payload['status'];
            return $file;
        }, array_values($files));
    }

    public function basePathForFile(array $module, string $locale, string $file): ?string
    {
        foreach ($this->baseLocalePaths($module, $locale) as $baseLocalePath) {
            $candidate = $baseLocalePath . '/' . basename($file);

            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    public function overridePath(string $moduleSlug, string $locale, string $file): string
    {
        return rtrim(config('translation-manager.override_path'), '/\\')
            . '/' . $moduleSlug
            . '/' . $locale
            . '/' . basename($file);
    }

    private function modulePayload(string $modulePath): ?array
    {
        $manifestPath = $modulePath . '/module.json';
        $manifest = [];

        if (file_exists($manifestPath)) {
            $decoded = json_decode((string) file_get_contents($manifestPath), true);
            $manifest = is_array($decoded) ? $decoded : [];
        }

        $name = $manifest['name'] ?? basename($modulePath);
        $slug = $manifest['slug'] ?? Str::kebab($name);

        return [
            'name' => $name,
            'slug' => $slug,
            'enabled' => (bool) ($manifest['enabled'] ?? true),
            'version' => $manifest['version'] ?? null,
            'provider' => $manifest['provider'] ?? null,
            'path' => $modulePath,
        ];
    }

    private function baseLocalePaths(array $module, string $locale): array
    {
        $paths = [];

        foreach ($module['base_lang_paths'] ?? config('translation-manager.base_lang_paths', ['Resources/lang']) as $relativePath) {
            $candidate = rtrim($module['path'], '/\\') . '/' . trim($relativePath, '/\\') . '/' . $locale;

            if (is_dir($candidate)) {
                $paths[] = $candidate;
            }
        }

        return $paths;
    }

    private function systemSources(string $locale): array
    {
        $sources = [];

        foreach ((array) config('translation-manager.system_sources', []) as $source) {
            $sourcePath = $source['path'] ?? null;

            if (! $sourcePath || ! is_dir($sourcePath)) {
                continue;
            }

            $module = [
                'name' => $source['name'] ?? 'Application / System',
                'slug' => $source['slug'] ?? 'app',
                'enabled' => true,
                'version' => null,
                'provider' => null,
                'path' => $sourcePath,
                'base_lang_paths' => $source['base_lang_paths'] ?? ['resources/lang'],
                'system_source' => true,
            ];

            $module['files'] = $this->listTranslationFiles($module, $locale);
            $module['stats'] = $this->aggregateStats($module['files']);
            $sources[] = $module;
        }

        return $sources;
    }

    private function aggregateStats(array $files): array
    {
        $stats = [
            'files' => count($files),
            'base_total' => 0,
            'custom_total' => 0,
            'missing_total' => 0,
            'empty_total' => 0,
            'extra_total' => 0,
            'base_only_files' => 0,
            'partial_files' => 0,
            'custom_full_files' => 0,
        ];

        foreach ($files as $file) {
            $fileStats = $file['stats'] ?? [];
            $stats['base_total'] += $fileStats['base_total'] ?? 0;
            $stats['custom_total'] += $fileStats['custom_total'] ?? 0;
            $stats['missing_total'] += $fileStats['missing_total'] ?? 0;
            $stats['empty_total'] += $fileStats['empty_total'] ?? 0;
            $stats['extra_total'] += $fileStats['extra_total'] ?? 0;

            if (($file['status'] ?? '') === 'base_only') $stats['base_only_files']++;
            if (($file['status'] ?? '') === 'partial') $stats['partial_files']++;
            if (($file['status'] ?? '') === 'custom_full') $stats['custom_full_files']++;
        }

        return $stats;
    }
}
