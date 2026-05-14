<?php

namespace Modules\DataExportCenter\Services;

use Illuminate\Support\Facades\File;
use Modules\DataExportCenter\Support\ExportProfileTypes;
use Throwable;

class ExportReadinessService
{
    public function __construct(
        private readonly ExportRegistry $registry,
        private readonly ExportSchemaBuilder $schemaBuilder,
        private readonly SelectOnlySqlGuard $sqlGuard
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
        $diagnostics = [];

        foreach ($profiles as $profile) {
            try {
                $headersCount = $this->headersCount($profile);
                $status = $headersCount > 0 ? 'valid' : 'without_fields';

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

                $diagnostics[] = array_merge($profile, [
                    'status' => $status,
                    'headers_count' => $headersCount,
                    'warnings' => [],
                    'errors' => [],
                ]);
            } catch (Throwable $exception) {
                $invalid++;

                $diagnostics[] = array_merge($profile, [
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
            'profiles' => $diagnostics,
        ];
    }

    private function headersCount(array $profile): int
    {
        if ($profile['type'] === ExportProfileTypes::MODEL) {
            $schema = $this->schemaBuilder->build($profile['class']);

            return count($schema['headers']);
        }

        if ($profile['type'] === ExportProfileTypes::SQL) {
            $this->sqlGuard->assertSelectOnly((string) ($profile['query_sql'] ?? ''));

            return 1;
        }

        if ($profile['type'] === ExportProfileTypes::BUILDER) {
            return count($profile['builder_definition']['select'] ?? []);
        }

        return 0;
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
