<?php

namespace Modules\DatabaseExplorer\Services;

use Modules\DatabaseExplorer\Support\Identifier;
use RuntimeException;

class MySqlMetadataProvider extends PostgresMetadataProvider
{
    public function getDatabaseOverview(): array
    {
        $info = $this->row($this->db()->selectOne('SELECT DATABASE() AS database_name, VERSION() AS engine_version'));

        [$schemaPredicate, $schemaBindings] = $this->schemaPredicate('schema_name');
        $schemas = $this->row($this->db()->selectOne(
            "SELECT COUNT(*) AS schema_count FROM information_schema.schemata WHERE 1 = 1 {$schemaPredicate}",
            $schemaBindings
        ));

        [$tablePredicate, $tableBindings] = $this->schemaPredicate('table_schema');
        $tables = $this->row($this->db()->selectOne(
            "SELECT
                SUM(CASE WHEN table_type = 'BASE TABLE' THEN 1 ELSE 0 END) AS table_count,
                SUM(CASE WHEN table_type = 'VIEW' THEN 1 ELSE 0 END) AS view_count,
                COALESCE(SUM(data_length + index_length), 0) AS total_size_bytes,
                COALESCE(SUM(table_rows), 0) AS estimated_rows
             FROM information_schema.tables
             WHERE 1 = 1 {$tablePredicate}",
            $tableBindings
        ));

        [$indexPredicate, $indexBindings] = $this->schemaPredicate('table_schema');
        $indexes = $this->row($this->db()->selectOne(
            "SELECT COUNT(*) AS index_count
             FROM (
                SELECT DISTINCT table_schema, table_name, index_name
                FROM information_schema.statistics
                WHERE 1 = 1 {$indexPredicate}
             ) idx",
            $indexBindings
        ));

        return [
            'databaseName' => $info['database_name'] ?? null,
            'engine' => 'MySQL',
            'version' => $info['engine_version'] ?? null,
            'schemaCount' => (int) ($schemas['schema_count'] ?? 0),
            'tableCount' => (int) ($tables['table_count'] ?? 0),
            'viewCount' => (int) ($tables['view_count'] ?? 0),
            'indexCount' => (int) ($indexes['index_count'] ?? 0),
            'totalSizeBytes' => (int) ($tables['total_size_bytes'] ?? 0),
            'estimatedRows' => (int) ($tables['estimated_rows'] ?? 0),
            'healthScore' => 100,
            'healthStatus' => 'healthy',
            'findings' => [],
        ];
    }

    public function getSchemas(): array
    {
        [$predicate, $bindings] = $this->schemaPredicate('s.schema_name');

        $rows = $this->db()->select(
            "SELECT
                s.schema_name,
                SUM(CASE WHEN t.table_type = 'BASE TABLE' THEN 1 ELSE 0 END) AS table_count,
                SUM(CASE WHEN t.table_type = 'VIEW' THEN 1 ELSE 0 END) AS view_count,
                0 AS materialized_view_count,
                COALESCE(SUM(t.data_length + t.index_length), 0) AS total_size_bytes
             FROM information_schema.schemata s
             LEFT JOIN information_schema.tables t ON t.table_schema = s.schema_name
             WHERE 1 = 1 {$predicate}
             GROUP BY s.schema_name
             ORDER BY total_size_bytes DESC, s.schema_name ASC",
            $bindings
        );

        return array_map(function (array $row): array {
            return [
                'schemaName' => $row['schema_name'],
                'tableCount' => (int) ($row['table_count'] ?? 0),
                'viewCount' => (int) ($row['view_count'] ?? 0),
                'materializedViewCount' => 0,
                'totalSizeBytes' => (int) ($row['total_size_bytes'] ?? 0),
                'healthScore' => 100,
                'healthStatus' => 'healthy',
            ];
        }, $this->rows($rows));
    }

    public function getTables(array $filter = []): array
    {
        $schemaName = $filter['schemaName'] ?? $filter['schema'] ?? null;
        $search = $filter['search'] ?? null;

        if ($schemaName !== null && $schemaName !== '') {
            Identifier::assertSafe((string) $schemaName, 'schema');
        } else {
            $schemaName = null;
        }

        if ($search === '') {
            $search = null;
        }

        [$schemaPredicate, $bindings] = $this->schemaPredicate('t.table_schema');
        $bindings[] = $schemaName;
        $bindings[] = $schemaName;
        $bindings[] = $search;
        $bindings[] = $search;

        $rows = $this->db()->select(
            "SELECT
                t.table_schema AS schema_name,
                t.table_name,
                t.engine AS owner_name,
                t.table_type,
                0 AS is_partitioned,
                COALESCE(t.table_rows, 0) AS estimated_rows,
                COALESCE(t.data_length + t.index_length, 0) AS total_size_bytes,
                COALESCE(t.data_length, 0) AS data_size_bytes,
                COALESCE(t.index_length, 0) AS index_size_bytes,
                COALESCE(col_stats.column_count, 0) AS column_count,
                COALESCE(idx_stats.index_count, 0) AS index_count,
                COALESCE(fk_stats.foreign_key_count, 0) AS foreign_key_count,
                CASE WHEN pk_stats.primary_key_count > 0 THEN 1 ELSE 0 END AS has_primary_key,
                NULL AS last_analyzed_at,
                t.update_time AS last_maintenance_at,
                COALESCE(t.table_rows, 0) AS live_rows,
                0 AS dead_rows
             FROM information_schema.tables t
             LEFT JOIN (
                SELECT table_schema, table_name, COUNT(*) AS column_count
                FROM information_schema.columns
                GROUP BY table_schema, table_name
             ) col_stats ON col_stats.table_schema = t.table_schema AND col_stats.table_name = t.table_name
             LEFT JOIN (
                SELECT table_schema, table_name, COUNT(DISTINCT index_name) AS index_count
                FROM information_schema.statistics
                GROUP BY table_schema, table_name
             ) idx_stats ON idx_stats.table_schema = t.table_schema AND idx_stats.table_name = t.table_name
             LEFT JOIN (
                SELECT table_schema, table_name, COUNT(*) AS foreign_key_count
                FROM information_schema.table_constraints
                WHERE constraint_type = 'FOREIGN KEY'
                GROUP BY table_schema, table_name
             ) fk_stats ON fk_stats.table_schema = t.table_schema AND fk_stats.table_name = t.table_name
             LEFT JOIN (
                SELECT table_schema, table_name, COUNT(*) AS primary_key_count
                FROM information_schema.table_constraints
                WHERE constraint_type = 'PRIMARY KEY'
                GROUP BY table_schema, table_name
             ) pk_stats ON pk_stats.table_schema = t.table_schema AND pk_stats.table_name = t.table_name
             WHERE t.table_type IN ('BASE TABLE', 'VIEW')
               {$schemaPredicate}
               AND (? IS NULL OR t.table_schema = ?)
               AND (? IS NULL OR t.table_name LIKE CONCAT('%', ?, '%'))
             ORDER BY total_size_bytes DESC, t.table_schema ASC, t.table_name ASC",
            $bindings
        );

        return array_map(function (array $row): array {
            $table = $this->mapTableRow($row);
            $table['supportsAnalyzeTimestamp'] = false;

            return $table;
        }, $this->rows($rows));
    }

    public function getTableDetail(string $schemaName, string $tableName): array
    {
        Identifier::assertSafe($schemaName, 'schema');
        Identifier::assertSafe($tableName, 'table');

        $tables = $this->getTables(['schemaName' => $schemaName, 'search' => $tableName]);
        $table = null;

        foreach ($tables as $candidate) {
            if ($candidate['schemaName'] === $schemaName && $candidate['tableName'] === $tableName) {
                $table = $candidate;
                break;
            }
        }

        if (! $table) {
            throw new RuntimeException("Table {$schemaName}.{$tableName} was not found or is not allowed.");
        }

        $columns = $this->getColumns($schemaName, $tableName);
        $indexes = $this->getIndexes($schemaName, $tableName);
        $constraints = $this->getConstraints($schemaName, $tableName);
        $relationships = $this->getRelationships($schemaName, $tableName);

        return array_merge($table, [
            'columns' => $columns,
            'indexes' => $indexes,
            'constraints' => $constraints,
            'relationships' => $relationships,
            'constraintCount' => count($constraints),
            'columnCount' => count($columns),
            'indexCount' => count($indexes),
        ]);
    }

    public function getColumns(string $schemaName, string $tableName): array
    {
        Identifier::assertSafe($schemaName, 'schema');
        Identifier::assertSafe($tableName, 'table');

        $rows = $this->db()->select(
            "SELECT
                c.column_name,
                c.ordinal_position,
                c.data_type,
                c.column_type AS full_data_type,
                c.character_maximum_length,
                c.numeric_precision,
                c.numeric_scale,
                c.is_nullable,
                c.column_default,
                CASE WHEN kcu_pk.column_name IS NULL THEN 0 ELSE 1 END AS is_primary_key,
                CASE WHEN kcu_fk.column_name IS NULL THEN 0 ELSE 1 END AS is_foreign_key,
                CASE WHEN s_unique.column_name IS NULL THEN 0 ELSE 1 END AS is_unique,
                CASE WHEN s_any.column_name IS NULL THEN 0 ELSE 1 END AS is_indexed,
                kcu_fk.referenced_table_schema AS referenced_schema,
                kcu_fk.referenced_table_name AS referenced_table,
                kcu_fk.referenced_column_name AS referenced_column,
                c.column_comment AS comment
             FROM information_schema.columns c
             LEFT JOIN information_schema.key_column_usage kcu_pk
                ON kcu_pk.table_schema = c.table_schema
               AND kcu_pk.table_name = c.table_name
               AND kcu_pk.column_name = c.column_name
               AND kcu_pk.constraint_name = 'PRIMARY'
             LEFT JOIN information_schema.key_column_usage kcu_fk
                ON kcu_fk.table_schema = c.table_schema
               AND kcu_fk.table_name = c.table_name
               AND kcu_fk.column_name = c.column_name
               AND kcu_fk.referenced_table_name IS NOT NULL
             LEFT JOIN (
                SELECT DISTINCT table_schema, table_name, column_name
                FROM information_schema.statistics
                WHERE non_unique = 0
             ) s_unique ON s_unique.table_schema = c.table_schema AND s_unique.table_name = c.table_name AND s_unique.column_name = c.column_name
             LEFT JOIN (
                SELECT DISTINCT table_schema, table_name, column_name
                FROM information_schema.statistics
             ) s_any ON s_any.table_schema = c.table_schema AND s_any.table_name = c.table_name AND s_any.column_name = c.column_name
             WHERE c.table_schema = ?
               AND c.table_name = ?
             ORDER BY c.ordinal_position",
            [$schemaName, $tableName]
        );

        return array_map(function (array $row): array {
            return [
                'name' => $row['column_name'],
                'ordinalPosition' => (int) $row['ordinal_position'],
                'dataType' => $row['data_type'],
                'udtName' => $row['data_type'],
                'fullDataType' => $row['full_data_type'],
                'characterMaximumLength' => $this->nullableInt($row['character_maximum_length'] ?? null),
                'numericPrecision' => $this->nullableInt($row['numeric_precision'] ?? null),
                'numericScale' => $this->nullableInt($row['numeric_scale'] ?? null),
                'isNullable' => ($row['is_nullable'] ?? 'YES') === 'YES',
                'defaultValue' => $row['column_default'] ?? null,
                'isPrimaryKey' => $this->bool($row['is_primary_key'] ?? false),
                'isForeignKey' => $this->bool($row['is_foreign_key'] ?? false),
                'isUnique' => $this->bool($row['is_unique'] ?? false),
                'isIndexed' => $this->bool($row['is_indexed'] ?? false),
                'referencedSchema' => $row['referenced_schema'] ?? null,
                'referencedTable' => $row['referenced_table'] ?? null,
                'referencedColumn' => $row['referenced_column'] ?? null,
                'comment' => $row['comment'] ?? null,
            ];
        }, $this->rows($rows));
    }

    public function getIndexes(string $schemaName, string $tableName): array
    {
        Identifier::assertSafe($schemaName, 'schema');
        Identifier::assertSafe($tableName, 'table');

        $rows = $this->db()->select(
            "SELECT
                s.table_schema AS schema_name,
                s.table_name,
                s.index_name,
                COALESCE(s.index_type, 'BTREE') AS index_type,
                CASE WHEN s.index_name = 'PRIMARY' THEN 1 ELSE 0 END AS is_primary,
                CASE WHEN MIN(s.non_unique) = 0 THEN 1 ELSE 0 END AS is_unique,
                1 AS is_valid,
                0 AS size_bytes,
                0 AS scans,
                0 AS tuples_read,
                0 AS tuples_fetched,
                GROUP_CONCAT(s.column_name ORDER BY s.seq_in_index SEPARATOR ',') AS columns
             FROM information_schema.statistics s
             WHERE s.table_schema = ?
               AND s.table_name = ?
             GROUP BY s.table_schema, s.table_name, s.index_name, s.index_type
             ORDER BY s.index_name",
            [$schemaName, $tableName]
        );

        return array_map(function (array $row): array {
            $columns = array_values(array_filter(explode(',', (string) ($row['columns'] ?? ''))));

            return [
                'schemaName' => $row['schema_name'],
                'tableName' => $row['table_name'],
                'indexName' => $row['index_name'],
                'columns' => $columns,
                'indexType' => $row['index_type'],
                'isPrimary' => $this->bool($row['is_primary'] ?? false),
                'isUnique' => $this->bool($row['is_unique'] ?? false),
                'isValid' => true,
                'sizeBytes' => (int) ($row['size_bytes'] ?? 0),
                'scans' => 0,
                'tuplesRead' => 0,
                'tuplesFetched' => 0,
                'definition' => trim(($this->bool($row['is_unique'] ?? false) ? 'UNIQUE ' : '') . 'INDEX `' . $row['index_name'] . '` (`' . implode('`, `', $columns) . '`)'),
            ];
        }, $this->rows($rows));
    }

    public function getConstraints(string $schemaName, string $tableName): array
    {
        Identifier::assertSafe($schemaName, 'schema');
        Identifier::assertSafe($tableName, 'table');

        $rows = $this->db()->select(
            "SELECT
                tc.table_schema AS schema_name,
                tc.table_name,
                tc.constraint_name,
                tc.constraint_type,
                GROUP_CONCAT(kcu.column_name ORDER BY kcu.ordinal_position SEPARATOR ',') AS columns
             FROM information_schema.table_constraints tc
             LEFT JOIN information_schema.key_column_usage kcu
                ON kcu.constraint_schema = tc.constraint_schema
               AND kcu.constraint_name = tc.constraint_name
               AND kcu.table_schema = tc.table_schema
               AND kcu.table_name = tc.table_name
             WHERE tc.table_schema = ?
               AND tc.table_name = ?
             GROUP BY tc.table_schema, tc.table_name, tc.constraint_name, tc.constraint_type
             ORDER BY tc.constraint_type, tc.constraint_name",
            [$schemaName, $tableName]
        );

        return array_map(function (array $row): array {
            return [
                'schemaName' => $row['schema_name'],
                'tableName' => $row['table_name'],
                'constraintName' => $row['constraint_name'],
                'constraintType' => $row['constraint_type'],
                'constraintTypeCode' => $row['constraint_type'],
                'isValidated' => true,
                'columns' => array_values(array_filter(explode(',', (string) ($row['columns'] ?? '')))),
                'definition' => $row['constraint_type'],
            ];
        }, $this->rows($rows));
    }

    public function getRelationships(string $schemaName, string $tableName): array
    {
        Identifier::assertSafe($schemaName, 'schema');
        Identifier::assertSafe($tableName, 'table');

        return array_merge(
            $this->relationshipRows($schemaName, $tableName, 'outgoing'),
            $this->relationshipRows($schemaName, $tableName, 'incoming')
        );
    }

    protected function relationshipRows(string $schemaName, string $tableName, string $direction): array
    {
        $where = $direction === 'outgoing'
            ? 'kcu.table_schema = ? AND kcu.table_name = ?'
            : 'kcu.referenced_table_schema = ? AND kcu.referenced_table_name = ?';

        $rows = $this->db()->select(
            "SELECT
                ? AS direction,
                kcu.table_schema AS source_schema,
                kcu.table_name AS source_table,
                kcu.referenced_table_schema AS target_schema,
                kcu.referenced_table_name AS target_table,
                kcu.constraint_name,
                GROUP_CONCAT(kcu.column_name ORDER BY kcu.ordinal_position SEPARATOR ',') AS source_columns,
                GROUP_CONCAT(kcu.referenced_column_name ORDER BY kcu.ordinal_position SEPARATOR ',') AS target_columns
             FROM information_schema.key_column_usage kcu
             WHERE kcu.referenced_table_name IS NOT NULL
               AND {$where}
             GROUP BY kcu.table_schema, kcu.table_name, kcu.referenced_table_schema, kcu.referenced_table_name, kcu.constraint_name
             ORDER BY source_schema, source_table, target_schema, target_table, constraint_name",
            [$direction, $schemaName, $tableName]
        );

        return array_map(function (array $row): array {
            return [
                'direction' => $row['direction'],
                'sourceSchema' => $row['source_schema'],
                'sourceTable' => $row['source_table'],
                'sourceColumns' => array_values(array_filter(explode(',', (string) ($row['source_columns'] ?? '')))),
                'targetSchema' => $row['target_schema'],
                'targetTable' => $row['target_table'],
                'targetColumns' => array_values(array_filter(explode(',', (string) ($row['target_columns'] ?? '')))),
                'constraintName' => $row['constraint_name'],
                'definition' => 'FOREIGN KEY',
            ];
        }, $this->rows($rows));
    }
}
