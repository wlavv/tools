<?php

namespace Modules\DataImportWizard\Services;

use Illuminate\Support\Collection;
use Modules\DataImportWizard\Contracts\ImportableContract;
use RuntimeException;

class ImportRegistry
{
    public function all(): Collection
    {
        $classes = config('data-import-wizard.importables', []);

        return collect($classes)
            ->filter(fn ($class) => is_string($class) && class_exists($class))
            ->filter(fn ($class) => is_subclass_of($class, ImportableContract::class))
            ->mapWithKeys(function (string $class) {
                return [$class::importKey() => $this->describe($class)];
            })
            ->sortBy('label')
            ->values();
    }

    public function keyed(): Collection
    {
        return $this->all()->keyBy('key');
    }

    public function find(string $key): ?string
    {
        $profile = $this->keyed()->get($key);

        return $profile['class'] ?? null;
    }

    public function require(string $key): string
    {
        $class = $this->find($key);

        if (! $class) {
            throw new RuntimeException("Import profile [{$key}] is not registered or does not implement ImportableContract.");
        }

        return $class;
    }

    public function describe(string $class): array
    {
        $fields = $class::importFields();
        $dependencies = $class::importDependencies();

        return [
            'key' => $class::importKey(),
            'label' => $class::importLabel(),
            'class' => $class,
            'module' => $this->moduleFromClass($class),
            'fields_count' => count($fields),
            'dependencies_count' => count($dependencies),
            'has_fields' => count($fields) > 0,
            'has_dependencies' => count($dependencies) > 0,
        ];
    }

    public function moduleFromClass(string $class): ?string
    {
        $parts = explode('\\', $class);

        if (($parts[0] ?? null) === 'Modules' && isset($parts[1])) {
            return $parts[1];
        }

        return null;
    }
}
