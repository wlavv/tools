<?php

namespace Modules\DataExportCenter\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DynamicQueryBuilderService
{
    public function build(array $definition, array $runtimeFilters = []): Builder
    {
        $from = $definition['from'] ?? null;

        if (! is_array($from) || empty($from['table'])) {
            throw new RuntimeException('Dynamic export builder requires a from.table value.');
        }

        $this->assertAllowedTable($from['table']);
        $alias = $this->alias($from['alias'] ?? $from['table']);
        $query = DB::table($from['table'] . ' as ' . $alias);

        $this->applyJoins($query, $definition['joins'] ?? []);
        $this->applySelects($query, $definition['select'] ?? [], $alias);
        $this->applyStaticFilters($query, $definition['where'] ?? []);
        $this->applyRuntimeFilters($query, $definition['filters'] ?? [], $runtimeFilters);
        $this->applyOrder($query, $definition['order'] ?? []);

        if (! empty($definition['limit'])) {
            $query->limit(min((int) $definition['limit'], (int) config('data-export-center.max_rows', 50000)));
        }

        return $query;
    }

    private function applyJoins(Builder $query, array $joins): void
    {
        if (count($joins) > (int) config('data-export-center.dynamic_builder.max_joins', 10)) {
            throw new RuntimeException('Dynamic export builder exceeded max joins.');
        }

        foreach ($joins as $join) {
            $table = $join['table'] ?? null;
            if (! $table) {
                continue;
            }

            $this->assertAllowedTable($table);
            $alias = $this->alias($join['alias'] ?? $table);
            $type = strtolower($join['type'] ?? 'left');
            $method = $type === 'inner' ? 'join' : 'leftJoin';

            $query->{$method}(
                $table . ' as ' . $alias,
                $this->column($join['left'] ?? ''),
                $this->operator($join['operator'] ?? '='),
                $this->column($join['right'] ?? '')
            );
        }
    }

    private function applySelects(Builder $query, array $selects, string $defaultAlias): void
    {
        if (count($selects) > (int) config('data-export-center.dynamic_builder.max_selects', 100)) {
            throw new RuntimeException('Dynamic export builder exceeded max selects.');
        }

        $grammar = DB::connection()->getQueryGrammar();

        foreach ($selects as $select) {
            $alias = $this->alias($select['alias'] ?? $select['column'] ?? 'field');

            if (! empty($select['raw'])) {
                if (! config('data-export-center.dynamic_builder.allow_raw_selects', false)) {
                    throw new RuntimeException('Raw selects are disabled for dynamic export builder profiles.');
                }

                $query->selectRaw('(' . $select['raw'] . ') as ' . $grammar->wrap($alias));
                continue;
            }

            $column = $this->column($select['column'] ?? ($defaultAlias . '.' . $alias));
            $query->selectRaw($grammar->wrap($column) . ' as ' . $grammar->wrap($alias));
        }
    }

    private function applyStaticFilters(Builder $query, array $filters): void
    {
        foreach ($filters as $filter) {
            $this->applyFilter($query, $filter, $filter['value'] ?? null);
        }
    }

    private function applyRuntimeFilters(Builder $query, array $definitions, array $runtimeFilters): void
    {
        foreach ($definitions as $key => $definition) {
            if (! array_key_exists($key, $runtimeFilters)) {
                continue;
            }

            $this->applyFilter($query, $definition, $runtimeFilters[$key]);
        }
    }

    private function applyFilter(Builder $query, array $definition, mixed $value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $column = $this->column($definition['column'] ?? '');
        $operator = strtolower($definition['operator'] ?? '=');

        match ($operator) {
            'like' => $query->where($column, 'like', '%' . $value . '%'),
            'in' => $query->whereIn($column, is_array($value) ? $value : explode(',', (string) $value)),
            'between' => $query->whereBetween($column, array_slice(is_array($value) ? array_values($value) : explode(',', (string) $value), 0, 2)),
            '!=', '<>', '>', '>=', '<', '<=', '=' => $query->where($column, $operator, $value),
            default => throw new RuntimeException("Unsupported dynamic export filter operator [{$operator}]."),
        };
    }

    private function applyOrder(Builder $query, array $orders): void
    {
        foreach ($orders as $order) {
            $query->orderBy($this->column($order['column'] ?? ''), strtolower($order['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc');
        }
    }

    private function assertAllowedTable(string $table): void
    {
        $allowed = config('data-export-center.dynamic_builder.allowed_tables', []);

        if (! empty($allowed) && ! in_array($table, $allowed, true)) {
            throw new RuntimeException("Table [{$table}] is not allowed for dynamic exports.");
        }

        if (! preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            throw new RuntimeException("Invalid table name [{$table}].");
        }
    }

    private function alias(string $alias): string
    {
        $alias = preg_replace('/[^a-zA-Z0-9_]/', '_', $alias) ?: 'field';

        return substr($alias, 0, 60);
    }

    private function column(string $column): string
    {
        if (! preg_match('/^[a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)?$/', $column)) {
            throw new RuntimeException("Invalid column reference [{$column}].");
        }

        return $column;
    }

    private function operator(string $operator): string
    {
        $operator = strtolower(trim($operator));
        $allowed = ['=', '!=', '<>', '>', '>=', '<', '<='];

        if (! in_array($operator, $allowed, true)) {
            throw new RuntimeException("Invalid join operator [{$operator}].");
        }

        return $operator;
    }
}
