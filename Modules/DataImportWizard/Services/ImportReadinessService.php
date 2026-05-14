<?php

namespace Modules\DataImportWizard\Services;

use Illuminate\Support\Facades\File;
use Throwable;

class ImportReadinessService
{
    public function __construct(
        private readonly ImportRegistry $registry,
        private readonly ImportSchemaBuilder $schemaBuilder
    ) {
    }

    public function summary(): array
    {
        $modules = $this->discoverModules();
        $profiles = $this->registry->all();

        $valid = 0;
        $invalid = 0;
        $withoutFields = 0;
        $withDependencies = 0;
        $profileModules = [];
        $profileDiagnostics = [];

        foreach ($profiles as $profile) {
            try {
                $schema = $this->schemaBuilder->build($profile['class']);
                $status = empty($schema['headers']) ? 'without_fields' : 'valid';

                if ($status === 'valid') {
                    $valid++;
                } else {
                    $withoutFields++;
                }

                if (($profile['dependencies_count'] ?? 0) > 0) {
                    $withDependencies++;
                }

                if ($profile['module']) {
                    $profileModules[$profile['module']] = true;
                }

                $profileDiagnostics[] = array_merge($profile, [
                    'status' => $status,
                    'headers_count' => count($schema['headers']),
                    'warnings' => $schema['warnings'],
                    'errors' => [],
                ]);
            } catch (Throwable $exception) {
                $invalid++;

                $profileDiagnostics[] = array_merge($profile, [
                    'status' => 'invalid',
                    'headers_count' => 0,
                    'warnings' => [],
                    'errors' => [$exception->getMessage()],
                ]);
            }
        }

        $modulesWithoutProfile = collect($modules)
            ->reject(fn ($module) => isset($profileModules[$module['name']]))
            ->values()
            ->all();

        return [
            'counters' => [
                'total_modules' => count($modules),
                'modules_with_profiles' => count($profileModules),
                'modules_without_profiles' => count($modulesWithoutProfile),
                'registered_profiles' => $profiles->count(),
                'valid_profiles' => $valid,
                'invalid_profiles' => $invalid,
                'profiles_without_fields' => $withoutFields,
                'profiles_with_dependencies' => $withDependencies,
            ],
            'modules' => $modules,
            'modules_without_profiles' => $modulesWithoutProfile,
            'profiles' => $profileDiagnostics,
        ];
    }

    private function discoverModules(): array
    {
        $path = config('data-import-wizard.modules_path', base_path('Modules'));

        if (! is_dir($path)) {
            return [];
        }

        return collect(File::directories($path))
            ->map(function (string $directory) {
                $name = basename($directory);
                $moduleJson = $directory . DIRECTORY_SEPARATOR . 'module.json';
                $metadata = [];

                if (is_file($moduleJson)) {
                    $metadata = json_decode((string) file_get_contents($moduleJson), true) ?: [];
                }

                return [
                    'name' => $metadata['name'] ?? $name,
                    'path' => $directory,
                    'enabled' => $metadata['active'] ?? $metadata['enabled'] ?? true,
                ];
            })
            ->sortBy('name')
            ->values()
            ->all();
    }
}
