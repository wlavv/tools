<?php

namespace Modules\DataImportWizard\Services;

use Modules\DataImportWizard\Contracts\ImportableContract;
use RuntimeException;

class ImportDependencyResolver
{
    /**
     * Resolve dependency graph. Returned nodes are dependency-first and root-last.
     */
    public function resolve(string $rootClass): array
    {
        if (! is_subclass_of($rootClass, ImportableContract::class)) {
            throw new RuntimeException("Class [{$rootClass}] is not importable.");
        }

        $nodes = [];
        $edges = [];
        $visited = [];

        $this->visit(
            class: $rootClass,
            id: 'root',
            alias: $rootClass::importKey(),
            prefix: null,
            options: ['mode' => config('data-import-wizard.default_mode', 'upsert')],
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
            throw new RuntimeException("Circular import dependency detected: {$cycle}");
        }

        if (isset($visited[$id])) {
            return;
        }

        $visited[$id] = true;

        $dependencies = $this->normalizeDependencies($class::importDependencies());

        foreach ($dependencies as $dependencyAlias => $dependencyOptions) {
            $dependencyClass = $dependencyOptions['class'];
            $dependencyPrefix = $dependencyOptions['prefix'] ?? $dependencyAlias;
            $dependencyId = $id === 'root' ? $dependencyAlias : $id . '.' . $dependencyAlias;

            $edges[] = [
                'parent_id' => $id,
                'child_id' => $dependencyId,
                'alias' => $dependencyAlias,
                'foreign_key' => $dependencyOptions['foreign_key'] ?? null,
                'owner_key' => $dependencyOptions['owner_key'] ?? 'id',
                'required' => $dependencyOptions['required'] ?? true,
                'mode' => $dependencyOptions['mode'] ?? 'resolve_only',
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
            'label' => $class::importLabel(),
            'prefix' => $prefix,
            'parent_id' => $parentId,
            'level' => $level,
            'is_root' => $id === 'root',
            'required' => $options['required'] ?? true,
            'mode' => $options['mode'] ?? ($id === 'root' ? config('data-import-wizard.default_mode', 'upsert') : 'resolve_only'),
            'foreign_key' => $options['foreign_key'] ?? null,
            'owner_key' => $options['owner_key'] ?? 'id',
            'preserve_keys' => $options['preserve_keys'] ?? false,
        ];
    }

    public function normalizeDependencies(array $dependencies): array
    {
        $normalized = [];

        foreach ($dependencies as $alias => $options) {
            if (is_int($alias)) {
                $class = $options;
                $alias = method_exists($class, 'importKey') ? $class::importKey() : class_basename($class);
                $options = [];
            } elseif (is_string($alias) && class_exists($alias)) {
                $class = $alias;
                $options = is_array($options) ? $options : [];
                $alias = $options['alias'] ?? (method_exists($class, 'importKey') ? $class::importKey() : class_basename($class));
            } else {
                $options = is_array($options) ? $options : ['class' => $options];
                $class = $options['class'] ?? null;
            }

            if (! is_string($class) || ! class_exists($class)) {
                throw new RuntimeException("Import dependency [{$alias}] has an invalid class.");
            }

            if (! is_subclass_of($class, ImportableContract::class)) {
                throw new RuntimeException("Import dependency [{$class}] must implement ImportableContract.");
            }

            $normalized[(string) $alias] = array_merge($options, [
                'class' => $class,
                'alias' => (string) $alias,
            ]);
        }

        return $normalized;
    }
}
