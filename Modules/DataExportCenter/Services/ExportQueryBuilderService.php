<?php

namespace Modules\DataExportCenter\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ExportQueryBuilderService
{
    public function build(string $rootClass, array $schema, array $filters = [], array $context = []): Builder
    {
        $rootModel = $this->newModel($rootClass);
        $query = DB::table($rootModel->getTable() . ' as root');
        $aliases = $this->aliasesForGraph($schema['graph']);

        $this->applyJoins($query, $schema, $aliases);
        $this->applySelects($query, $schema, $aliases);
        $this->applyFilters($query, $schema, $filters, $aliases);

        if (method_exists($rootClass, 'modifyExportQuery')) {
            $modified = $rootClass::modifyExportQuery($query, $context, $schema);
            if ($modified instanceof Builder) {
                $query = $modified;
            }
        }

        return $query;
    }

    public function aliasesForGraph(array $graph): array
    {
        $aliases = ['root' => 'root'];

        foreach ($graph['nodes'] as $node) {
            $aliases[$node['id']] = $node['is_root'] ? 'root' : $this->aliasForNode($node);
        }

        return $aliases;
    }

    private function applyJoins(Builder $query, array $schema, array $aliases): void
    {
        $graph = $schema['graph'];
        $nodes = collect($graph['nodes'])->keyBy('id');
        $edgesByParent = collect($graph['edges'])->groupBy('parent_id');
        $queue = ['root'];
        $visited = [];

        while ($parentId = array_shift($queue)) {
            if (isset($visited[$parentId])) {
                continue;
            }

            $visited[$parentId] = true;

            foreach (($edgesByParent[$parentId] ?? []) as $edge) {
                $child = $nodes->get($edge['child_id']);
                if (! $child) {
                    continue;
                }

                if (empty($edge['foreign_key'])) {
                    throw new RuntimeException("Export dependency [{$edge['child_id']}] has no foreign_key.");
                }

                $childModel = $this->newModel($child['class']);
                $parentAlias = $aliases[$edge['parent_id']];
                $childAlias = $aliases[$edge['child_id']];
                $ownerKey = $edge['owner_key'] ?: config('data-export-center.dependencies.default_owner_key', 'id');
                $method = $this->joinMethod($edge);

                $query->{$method}(
                    $childModel->getTable() . ' as ' . $childAlias,
                    $parentAlias . '.' . $edge['foreign_key'],
                    '=',
                    $childAlias . '.' . $ownerKey
                );

                $queue[] = $edge['child_id'];
            }
        }
    }

    private function applySelects(Builder $query, array $schema, array $aliases): void
    {
        $grammar = DB::connection()->getQueryGrammar();

        foreach ($schema['fields'] as $field) {
            $csvKey = $field['csv_key'];
            $sourceAlias = $aliases[$field['source_id']] ?? 'root';

            if (! empty($field['select'])) {
                $query->selectRaw('(' . $field['select'] . ') as ' . $grammar->wrap($csvKey));
                continue;
            }

            $column = $field['column'] ?? $field['field_key'];
            $query->selectRaw(
                $grammar->wrap($sourceAlias . '.' . $column) . ' as ' . $grammar->wrap($csvKey)
            );
        }
    }

    private function applyFilters(Builder $query, array $schema, array $filters, array $aliases): void
    {
        foreach ($filters as $key => $value) {
            if (! array_key_exists($key, $schema['filters'] ?? [])) {
                continue;
            }

            if ($value === null || $value === '') {
                continue;
            }

            $definition = $schema['filters'][$key];
            $sourceAlias = $aliases[$definition['source_id']] ?? 'root';
            $column = $sourceAlias . '.' . ($definition['column'] ?? $key);
            $operator = strtolower($definition['operator'] ?? '=');

            match ($operator) {
                'like' => $query->where($column, 'like', '%' . $value . '%'),
                'starts_with' => $query->where($column, 'like', $value . '%'),
                'ends_with' => $query->where($column, 'like', '%' . $value),
                'in' => $query->whereIn($column, is_array($value) ? $value : explode(',', (string) $value)),
                'between' => $this->applyBetween($query, $column, $value),
                'date_between' => $this->applyBetween($query, $column, $value),
                'null' => $query->whereNull($column),
                'not_null' => $query->whereNotNull($column),
                '!=', '<>', '>', '>=', '<', '<=', '=' => $query->where($column, $operator, $value),
                default => throw new RuntimeException("Unsupported export filter operator [{$operator}]."),
            };
        }
    }

    private function applyBetween(Builder $query, string $column, mixed $value): void
    {
        $values = is_array($value) ? array_values($value) : explode(',', (string) $value);

        if (count($values) >= 2) {
            $query->whereBetween($column, [$values[0], $values[1]]);
        }
    }

    private function aliasForNode(array $node): string
    {
        $alias = 'dep_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $node['id']);

        return substr($alias, 0, 60);
    }

    private function joinMethod(array $edge): string
    {
        if (($edge['required'] ?? false) && config('data-export-center.dependencies.required_dependencies_as_inner_join', false)) {
            return 'join';
        }

        return 'leftJoin';
    }

    private function newModel(string $class): Model
    {
        $model = new $class();

        if (! $model instanceof Model) {
            throw new RuntimeException("Class [{$class}] must be an Eloquent model for automatic dependency export.");
        }

        return $model;
    }
}
