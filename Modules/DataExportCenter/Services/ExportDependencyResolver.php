<?php

namespace Modules\DataExportCenter\Services;

use Modules\DataExportCenter\Contracts\ExportableContract;
use Modules\DataImportWizard\Contracts\ImportableContract;
use Modules\DataImportWizard\Services\ImportDependencyResolver;
use RuntimeException;
use Throwable;

class ExportDependencyResolver
{
    public function __construct(
        private readonly ImportDependencyResolver $importResolver
    ) {
    }

    public function resolve(string $rootClass): array
    {
        if (! $this->isSupportedClass($rootClass)) {
            throw new RuntimeException("Class [{$rootClass}] is not exportable or importable.");
        }

        if ($this->shouldReuseImportTree($rootClass)) {
            try {
                return $this->importResolver->resolve($rootClass);
            } catch (Throwable $exception) {
                if (! method_exists($rootClass, 'exportDependencies')) {
                    throw $exception;
                }
            }
        }

        $nodes = [];
        $edges = [];
        $visited = [];

        $this->visit(
            class: $rootClass,
            id: 'root',
            alias: $this->keyForClass($rootClass),
            prefix: null,
            options: [],
            parentId: null,
            level: 0,
            nodes: $nodes,
            edges: $edges,
            visited: $visited,
            classPath: []
        );

        return [
            'root_class' => $rootClass,
            'nodes' => array_values($nodes),
            'edges' => $edges,
        ];
    }

    public function normalizeDependencies(array $dependencies): array
    {
        $normalized = [];

        foreach ($dependencies as $alias => $options) {
            if (is_int($alias)) {
                $class = $options;
                $alias = method_exists($class, 'exportKey') ? $class::exportKey() : (method_exists($class, 'importKey') ? $class::importKey() : class_basename($class));
                $options = [];
            } elseif (is_string($alias) && class_exists($alias)) {
                $class = $alias;
                $options = is_array($options) ? $options : [];
                $alias = $options['alias'] ?? (method_exists($class, 'exportKey') ? $class::exportKey() : (method_exists($class, 'importKey') ? $class::importKey() : class_basename($class)));
            } else {
                $options = is_array($options) ? $options : ['class' => $options];
                $class = $options['class'] ?? null;
            }

            if (! is_string($class) || ! class_exists($class)) {
                throw new RuntimeException("Export dependency [{$alias}] has an invalid class.");
            }

            if (! $this->isSupportedClass($class)) {
                throw new RuntimeException("Export dependency [{$class}] must implement ExportableContract or ImportableContract.");
            }

            $normalized[(string) $alias] = array_merge($options, [
                'class' => $class,
                'alias' => (string) $alias,
            ]);
        }

        return $normalized;
    }

    private function visit(
        string $class,
        string $id,
        string $alias,
        ?string $prefix,
        array $options,
        ?string $parentId,
        int $level,
        array &$nodes,
        array &$edges,
        array &$visited,
        array $classPath
    ): void {
        if (in_array($class, $classPath, true)) {
            $cycle = implode(' -> ', array_merge($classPath, [$class]));
            throw new RuntimeException("Circular export dependency detected: {$cycle}");
        }

        if (isset($visited[$id])) {
            return;
        }

        $visited[$id] = true;

        $dependencies = $this->normalizeDependencies($this->dependenciesForClass($class));

        foreach ($dependencies as $dependencyAlias => $dependencyOptions) {
            $dependencyClass = $dependencyOptions['class'];
            $dependencyPrefix = $dependencyOptions['prefix'] ?? $dependencyAlias;
            $dependencyId = $id === 'root' ? $dependencyAlias : $id . '.' . $dependencyAlias;

            $edges[] = [
                'parent_id' => $id,
                'child_id' => $dependencyId,
                'alias' => $dependencyAlias,
                'foreign_key' => $dependencyOptions['foreign_key'] ?? null,
                'owner_key' => $dependencyOptions['owner_key'] ?? config('data-export-center.dependencies.default_owner_key', 'id'),
                'required' => $dependencyOptions['required'] ?? true,
                'mode' => $dependencyOptions['mode'] ?? 'export_join',
            ];

            $this->visit(
                class: $dependencyClass,
                id: $dependencyId,
                alias: $dependencyAlias,
                prefix: $dependencyPrefix,
                options: $dependencyOptions,
                parentId: $id,
                level: $level + 1,
                nodes: $nodes,
                edges: $edges,
                visited: $visited,
                classPath: array_merge($classPath, [$class])
            );
        }

        $nodes[$id] = [
            'id' => $id,
            'alias' => $alias,
            'class' => $class,
            'label' => $this->labelForClass($class),
            'prefix' => $prefix,
            'parent_id' => $parentId,
            'level' => $level,
            'is_root' => $id === 'root',
            'required' => $options['required'] ?? true,
            'mode' => $options['mode'] ?? 'export_join',
            'foreign_key' => $options['foreign_key'] ?? null,
            'owner_key' => $options['owner_key'] ?? config('data-export-center.dependencies.default_owner_key', 'id'),
            'preserve_keys' => $options['preserve_keys'] ?? false,
        ];
    }

    private function shouldReuseImportTree(string $rootClass): bool
    {
        return (bool) config('data-export-center.dependencies.reuse_import_wizard_tree', true)
            && is_subclass_of($rootClass, ImportableContract::class)
            && ! method_exists($rootClass, 'exportDependencies');
    }

    private function isSupportedClass(string $class): bool
    {
        return is_subclass_of($class, ExportableContract::class)
            || ((bool) config('data-export-center.registry.allow_importables_as_exportables', true)
                && is_subclass_of($class, ImportableContract::class));
    }

    private function keyForClass(string $class): string
    {
        if (method_exists($class, 'exportKey')) {
            return $class::exportKey();
        }

        return $class::importKey();
    }

    private function labelForClass(string $class): string
    {
        if (method_exists($class, 'exportLabel')) {
            return $class::exportLabel();
        }

        return $class::importLabel();
    }

    private function dependenciesForClass(string $class): array
    {
        if (method_exists($class, 'exportDependencies')) {
            return $class::exportDependencies();
        }

        if (method_exists($class, 'importDependencies')) {
            return $class::importDependencies();
        }

        return [];
    }
}
