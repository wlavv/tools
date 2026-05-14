<?php

namespace Modules\DataExportCenter\Services;

use Illuminate\Support\Collection;
use Modules\DataExportCenter\Contracts\ExportableContract;
use Modules\DataExportCenter\Models\DataExportProfile;
use Modules\DataExportCenter\Support\ExportProfileTypes;
use Modules\DataImportWizard\Contracts\ImportableContract;
use RuntimeException;
use Throwable;

class ExportRegistry
{
    public function all(): Collection
    {
        $classProfiles = collect(config('data-export-center.exportables', []))
            ->filter(fn ($class) => is_string($class) && class_exists($class))
            ->filter(fn ($class) => $this->isSupportedClass($class))
            ->map(fn (string $class) => $this->describeClass($class));

        $databaseProfiles = $this->databaseProfiles();

        return $classProfiles
            ->merge($databaseProfiles)
            ->unique('key')
            ->sortBy('label')
            ->values();
    }

    public function keyed(): Collection
    {
        return $this->all()->keyBy('key');
    }

    public function find(string $key): ?array
    {
        return $this->keyed()->get($key);
    }

    public function require(string $key): array
    {
        $profile = $this->find($key);

        if (! $profile) {
            throw new RuntimeException("Export profile [{$key}] is not registered.");
        }

        return $profile;
    }

    public function isSupportedClass(string $class): bool
    {
        if (is_subclass_of($class, ExportableContract::class)) {
            return true;
        }

        return (bool) config('data-export-center.registry.allow_importables_as_exportables', true)
            && is_subclass_of($class, ImportableContract::class);
    }

    public function describeClass(string $class): array
    {
        $fields = $this->fieldsForClass($class);
        $dependencies = $this->dependenciesForClass($class);

        return [
            'key' => $this->keyForClass($class),
            'label' => $this->labelForClass($class),
            'type' => ExportProfileTypes::MODEL,
            'class' => $class,
            'module' => $this->moduleFromClass($class),
            'fields_count' => count($fields),
            'dependencies_count' => count($dependencies),
            'has_fields' => count($fields) > 0,
            'has_dependencies' => count($dependencies) > 0,
            'source' => 'config',
            'default_format' => config('data-export-center.default_format', 'csv'),
            'metadata' => [],
        ];
    }

    public function keyForClass(string $class): string
    {
        if (method_exists($class, 'exportKey')) {
            return $class::exportKey();
        }

        return $class::importKey();
    }

    public function labelForClass(string $class): string
    {
        if (method_exists($class, 'exportLabel')) {
            return $class::exportLabel();
        }

        return $class::importLabel();
    }

    public function fieldsForClass(string $class): array
    {
        if (method_exists($class, 'exportFields')) {
            return $class::exportFields();
        }

        if (method_exists($class, 'importFields')) {
            return $class::importFields();
        }

        return [];
    }

    public function dependenciesForClass(string $class): array
    {
        if (method_exists($class, 'exportDependencies')) {
            return $class::exportDependencies();
        }

        if (method_exists($class, 'importDependencies')) {
            return $class::importDependencies();
        }

        return [];
    }

    public function moduleFromClass(string $class): ?string
    {
        $parts = explode('\\', $class);

        if (($parts[0] ?? null) === 'Modules' && isset($parts[1])) {
            return $parts[1];
        }

        return null;
    }

    private function databaseProfiles(): Collection
    {
        if (! config('data-export-center.registry.include_database_profiles', true)) {
            return collect();
        }

        try {
            return DataExportProfile::query()
                ->where('status', 'active')
                ->get()
                ->map(function (DataExportProfile $profile) {
                    return [
                        'key' => $profile->key,
                        'label' => $profile->label,
                        'type' => $profile->type,
                        'class' => $profile->class_name,
                        'module' => $profile->module,
                        'fields_count' => count($profile->builder_definition['select'] ?? []),
                        'dependencies_count' => 0,
                        'has_fields' => true,
                        'has_dependencies' => false,
                        'source' => 'database',
                        'default_format' => $profile->default_format,
                        'query_sql' => $profile->query_sql,
                        'query_bindings' => $profile->query_bindings ?: [],
                        'builder_definition' => $profile->builder_definition ?: [],
                        'metadata' => $profile->metadata ?: [],
                    ];
                });
        } catch (Throwable) {
            return collect();
        }
    }
}
