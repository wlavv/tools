<?php

namespace Modules\DatabaseExplorer\Services;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use Modules\DatabaseExplorer\Support\Identifier;
use RuntimeException;

class PostgresMetadataProvider
{
    /** @param array<int,string> $allowedSchemas @param array<int,string> $excludedSchemas */
    public function __construct(
        protected ?string $connectionName = null,
        protected array $allowedSchemas = [],
        protected array $excludedSchemas = ['information_schema', 'pg_catalog', 'pg_toast']
    ) {
    }

    protected function db(): ConnectionInterface
    {
        return DB::connection($this->connectionName);
    }

    public function getDatabaseOverview(): array
    {
        $info = $this->row($this->db()->selectOne(<<<'SQL'
            SELECT
                current_database() AS database_name,
                version() AS engine_version,
                pg_database_size(current_database()) AS total_size_bytes
        SQL));

        [$schemaPredicate, $schemaBindings] = $this->schemaPredicate('schema_name');
        $schemas = $this->row($this->db()->selectOne(
            "SELECT COUNT(*)::int AS schema_count FROM information_schema.schemata WHERE 1 = 1 {$schemaPredicate}",
            $schemaBindings
        ));

        [$tablePredicate, $tableBindings] = $this->schemaPredicate('table_schema');
        $tables = $this->row($this->db()->selectOne(
            "SELECT
                COUNT(*) FILTER (WHERE table_type = 'BASE TABLE')::int AS table_count,
                COUNT(*) FILTER (WHERE table_type = 'VIEW')::int AS view_count
             FROM information_schema.tables
             WHERE 1 = 1 {$tablePredicate}",
            $tableBindings
        ));

        [$indexPredicate, $indexBindings] = $this->schemaPredicate('schemaname');
        $indexes = $this->row($this->db()->selectOne(
            "SELECT COUNT(*)::int AS index_count FROM pg_indexes WHERE 1 = 1 {$indexPredicate}",
            $indexBindings
        ));

        [$namespacePredicate, $namespaceBindings] = $this->schemaPredicate('n.nspname');
        $rows = $this->row($this->db()->selectOne(
            "SELECT COALESCE(SUM(GREATEST(c.reltuples, 0)), 0)::bigint AS estimated_rows
             FROM pg_class c
             JOIN pg_namespace n ON n.oid = c.relnamespace
             WHERE c.relkind IN ('r', 'p', 'm') {$namespacePredicate}",
            $namespaceBindings
        ));

        return [
            'databaseName' => $info['database_name'] ?? null,
            'engine' => 'PostgreSQL',
            'version' => $info['engine_version'] ?? null,
            'schemaCount' => (int) ($schemas['schema_count'] ?? 0),
            'tableCount' => (int) ($tables['table_count'] ?? 0),
            'viewCount' => (int) ($tables['view_count'] ?? 0),
            'indexCount' => (int) ($indexes['index_count'] ?? 0),
            'totalSizeBytes' => (int) ($info['total_size_bytes'] ?? 0),
            'estimatedRows' => (int) ($rows['estimated_rows'] ?? 0),
            'healthScore' => 100,
            'healthStatus' => 'healthy',
            'findings' => [],
        ];
    }

    public function getSchemas(): array
    {
        [$predicate, $bindings] = $this->schemaPredicate('n.nspname');

        $rows = $this->db()->select(<<<SQL
            SELECT
                n.nspname AS schema_name,
                COUNT(c.oid) FILTER (WHERE c.relkind IN ('r', 'p'))::int AS table_count,
                COUNT(c.oid) FILTER (WHERE c.relkind = 'v')::int AS view_count,
                COUNT(c.oid) FILTER (WHERE c.relkind = 'm')::int AS materialized_view_count,
                COALESCE(SUM(pg_total_relation_size(c.oid)) FILTER (WHERE c.relkind IN ('r', 'p', 'm')), 0)::bigint AS total_size_bytes
            FROM pg_namespace n
            LEFT JOIN pg_class c ON c.relnamespace = n.oid
            WHERE 1 = 1 {$predicate}
            GROUP BY n.nspname
            ORDER BY total_size_bytes DESC, n.nspname ASC
        SQL, $bindings);

        return array_map(function (array $row): array {
            return [
                'schemaName' => $row['schema_name'],
                'tableCount' => (int) $row['table_count'],
                'viewCount' => (int) $row['view_count'],
                'materializedViewCount' => (int) $row['materialized_view_count'],
                'totalSizeBytes' => (int) $row['total_size_bytes'],
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

        [$schemaPredicate, $bindings] = $this->schemaPredicate('n.nspname');
        $bindings[] = $schemaName;
        $bindings[] = $schemaName;
        $bindings[] = $search;
        $bindings[] = $search;

        $rows = $this->db()->select(<<<SQL
            SELECT
                n.nspname AS schema_name,
                c.relname AS table_name,
                pg_get_userbyid(c.relowner) AS owner_name,
                CASE c.relkind
                    WHEN 'r' THEN 'BASE TABLE'
                    WHEN 'p' THEN 'PARTITIONED TABLE'
                    WHEN 'm' THEN 'MATERIALIZED VIEW'
                    ELSE c.relkind::text
                END AS table_type,
                (c.relkind = 'p') AS is_partitioned,
                GREATEST(c.reltuples::bigint, 0) AS estimated_rows,
                pg_total_relation_size(c.oid) AS total_size_bytes,
                pg_table_size(c.oid) AS data_size_bytes,
                pg_indexes_size(c.oid) AS index_size_bytes,
                COALESCE(col_stats.column_count, 0)::int AS column_count,
                COALESCE(idx_stats.index_count, 0)::int AS index_count,
                COALESCE(fk_stats.foreign_key_count, 0)::int AS foreign_key_count,
                EXISTS (
                    SELECT 1
                    FROM pg_index i
                    WHERE i.indrelid = c.oid
                      AND i.indisprimary = true
                ) AS has_primary_key,
                GREATEST(s.last_analyze, s.last_autoanalyze) AS last_analyzed_at,
                GREATEST(s.last_vacuum, s.last_autovacuum) AS last_maintenance_at,
                COALESCE(s.n_live_tup, 0)::bigint AS live_rows,
                COALESCE(s.n_dead_tup, 0)::bigint AS dead_rows
            FROM pg_class c
            JOIN pg_namespace n ON n.oid = c.relnamespace
            LEFT JOIN pg_stat_user_tables s ON s.relid = c.oid
            LEFT JOIN (
                SELECT table_schema, table_name, COUNT(*) AS column_count
                FROM information_schema.columns
                GROUP BY table_schema, table_name
            ) col_stats ON col_stats.table_schema = n.nspname AND col_stats.table_name = c.relname
            LEFT JOIN (
                SELECT indrelid, COUNT(*) AS index_count
                FROM pg_index
                GROUP BY indrelid
            ) idx_stats ON idx_stats.indrelid = c.oid
            LEFT JOIN (
                SELECT conrelid, COUNT(*) AS foreign_key_count
                FROM pg_constraint
                WHERE contype = 'f'
                GROUP BY conrelid
            ) fk_stats ON fk_stats.conrelid = c.oid
            WHERE c.relkind IN ('r', 'p', 'm')
              {$schemaPredicate}
              AND (CAST(? AS text) IS NULL OR n.nspname = ?)
              AND (CAST(? AS text) IS NULL OR c.relname ILIKE '%' || ? || '%')
            ORDER BY pg_total_relation_size(c.oid) DESC, n.nspname ASC, c.relname ASC
        SQL, $bindings);

        return array_map(fn (array $row): array => $this->mapTableRow($row), $this->rows($rows));
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

        $rows = $this->db()->select(<<<'SQL'
            SELECT
                ic.column_name,
                ic.ordinal_position,
                ic.data_type,
                ic.udt_name,
                format_type(a.atttypid, a.atttypmod) AS full_data_type,
                ic.character_maximum_length,
                ic.numeric_precision,
                ic.numeric_scale,
                ic.is_nullable,
                ic.column_default,
                EXISTS (
                    SELECT 1 FROM pg_index ix
                    WHERE ix.indrelid = pc.oid
                      AND ix.indisprimary
                      AND a.attnum = ANY(ix.indkey)
                ) AS is_primary_key,
                EXISTS (
                    SELECT 1 FROM pg_constraint con
                    JOIN unnest(con.conkey) AS src(attnum) ON true
                    WHERE con.conrelid = pc.oid
                      AND con.contype = 'f'
                      AND src.attnum = a.attnum
                ) AS is_foreign_key,
                EXISTS (
                    SELECT 1 FROM pg_index ix
                    WHERE ix.indrelid = pc.oid
                      AND ix.indisunique
                      AND a.attnum = ANY(ix.indkey)
                ) AS is_unique,
                EXISTS (
                    SELECT 1 FROM pg_index ix
                    WHERE ix.indrelid = pc.oid
                      AND a.attnum = ANY(ix.indkey)
                ) AS is_indexed,
                fk.foreign_schema AS referenced_schema,
                fk.foreign_table AS referenced_table,
                fk.foreign_column AS referenced_column,
                col_description(pc.oid, a.attnum) AS comment
            FROM information_schema.columns ic
            JOIN pg_namespace n ON n.nspname = ic.table_schema
            JOIN pg_class pc ON pc.relnamespace = n.oid AND pc.relname = ic.table_name
            JOIN pg_attribute a ON a.attrelid = pc.oid AND a.attname = ic.column_name
            LEFT JOIN LATERAL (
                SELECT
                    nr.nspname AS foreign_schema,
                    cr.relname AS foreign_table,
                    ar.attname AS foreign_column
                FROM pg_constraint con
                JOIN unnest(con.conkey) WITH ORDINALITY AS src(attnum, ord) ON true
                JOIN unnest(con.confkey) WITH ORDINALITY AS tgt(attnum, ord) ON tgt.ord = src.ord
                JOIN pg_class cr ON cr.oid = con.confrelid
                JOIN pg_namespace nr ON nr.oid = cr.relnamespace
                JOIN pg_attribute ar ON ar.attrelid = con.confrelid AND ar.attnum = tgt.attnum
                WHERE con.conrelid = pc.oid
                  AND con.contype = 'f'
                  AND src.attnum = a.attnum
                LIMIT 1
            ) fk ON true
            WHERE ic.table_schema = ?
              AND ic.table_name = ?
            ORDER BY ic.ordinal_position
        SQL, [$schemaName, $tableName]);

        return array_map(function (array $row): array {
            return [
                'name' => $row['column_name'],
                'ordinalPosition' => (int) $row['ordinal_position'],
                'dataType' => $row['data_type'],
                'udtName' => $row['udt_name'],
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

        $rows = $this->db()->select(<<<'SQL'
            SELECT
                ns.nspname AS schema_name,
                tbl.relname AS table_name,
                idx.relname AS index_name,
                am.amname AS index_type,
                i.indisprimary AS is_primary,
                i.indisunique AS is_unique,
                i.indisvalid AS is_valid,
                pg_relation_size(idx.oid) AS size_bytes,
                COALESCE(stat.idx_scan, 0)::bigint AS scans,
                COALESCE(stat.idx_tup_read, 0)::bigint AS tuples_read,
                COALESCE(stat.idx_tup_fetch, 0)::bigint AS tuples_fetched,
                pg_get_indexdef(idx.oid) AS definition,
                ARRAY_AGG(att.attname ORDER BY array_position(i.indkey, att.attnum)) FILTER (WHERE att.attname IS NOT NULL) AS columns
            FROM pg_index i
            JOIN pg_class tbl ON tbl.oid = i.indrelid
            JOIN pg_namespace ns ON ns.oid = tbl.relnamespace
            JOIN pg_class idx ON idx.oid = i.indexrelid
            JOIN pg_am am ON am.oid = idx.relam
            LEFT JOIN pg_stat_user_indexes stat ON stat.indexrelid = idx.oid
            LEFT JOIN pg_attribute att ON att.attrelid = tbl.oid AND att.attnum = ANY(i.indkey)
            WHERE ns.nspname = ?
              AND tbl.relname = ?
            GROUP BY
                ns.nspname,
                tbl.relname,
                idx.relname,
                am.amname,
                i.indisprimary,
                i.indisunique,
                i.indisvalid,
                idx.oid,
                stat.idx_scan,
                stat.idx_tup_read,
                stat.idx_tup_fetch
            ORDER BY idx.relname
        SQL, [$schemaName, $tableName]);

        return array_map(function (array $row): array {
            return [
                'schemaName' => $row['schema_name'],
                'tableName' => $row['table_name'],
                'indexName' => $row['index_name'],
                'columns' => $this->parsePgArray($row['columns'] ?? ''),
                'indexType' => $row['index_type'],
                'isPrimary' => $this->bool($row['is_primary'] ?? false),
                'isUnique' => $this->bool($row['is_unique'] ?? false),
                'isValid' => $this->bool($row['is_valid'] ?? true),
                'sizeBytes' => (int) ($row['size_bytes'] ?? 0),
                'scans' => (int) ($row['scans'] ?? 0),
                'tuplesRead' => (int) ($row['tuples_read'] ?? 0),
                'tuplesFetched' => (int) ($row['tuples_fetched'] ?? 0),
                'definition' => $row['definition'] ?? '',
            ];
        }, $this->rows($rows));
    }

    public function getConstraints(string $schemaName, string $tableName): array
    {
        Identifier::assertSafe($schemaName, 'schema');
        Identifier::assertSafe($tableName, 'table');

        $rows = $this->db()->select(<<<'SQL'
            SELECT
                n.nspname AS schema_name,
                c.relname AS table_name,
                con.conname AS constraint_name,
                con.contype AS constraint_type_code,
                con.convalidated AS is_validated,
                pg_get_constraintdef(con.oid, true) AS definition,
                ARRAY_AGG(a.attname ORDER BY cols.ord) FILTER (WHERE a.attname IS NOT NULL) AS columns
            FROM pg_constraint con
            JOIN pg_class c ON c.oid = con.conrelid
            JOIN pg_namespace n ON n.oid = c.relnamespace
            LEFT JOIN unnest(con.conkey) WITH ORDINALITY AS cols(attnum, ord) ON true
            LEFT JOIN pg_attribute a ON a.attrelid = c.oid AND a.attnum = cols.attnum
            WHERE n.nspname = ?
              AND c.relname = ?
            GROUP BY n.nspname, c.relname, con.conname, con.contype, con.convalidated, con.oid
            ORDER BY con.contype, con.conname
        SQL, [$schemaName, $tableName]);

        return array_map(function (array $row): array {
            return [
                'schemaName' => $row['schema_name'],
                'tableName' => $row['table_name'],
                'constraintName' => $row['constraint_name'],
                'constraintType' => $this->constraintType($row['constraint_type_code'] ?? ''),
                'constraintTypeCode' => $row['constraint_type_code'] ?? '',
                'isValidated' => $this->bool($row['is_validated'] ?? true),
                'columns' => $this->parsePgArray($row['columns'] ?? ''),
                'definition' => $row['definition'] ?? '',
            ];
        }, $this->rows($rows));
    }

    public function getRelationships(string $schemaName, string $tableName): array
    {
        Identifier::assertSafe($schemaName, 'schema');
        Identifier::assertSafe($tableName, 'table');

        $outgoing = $this->relationshipRows($schemaName, $tableName, 'outgoing');
        $incoming = $this->relationshipRows($schemaName, $tableName, 'incoming');

        return array_merge($outgoing, $incoming);
    }

    protected function relationshipRows(string $schemaName, string $tableName, string $direction): array
    {
        $where = $direction === 'outgoing'
            ? 'src_ns.nspname = ? AND src.relname = ?'
            : 'target_ns.nspname = ? AND target.relname = ?';

        $rows = $this->db()->select(<<<SQL
            SELECT
                '{$direction}' AS direction,
                src_ns.nspname AS source_schema,
                src.relname AS source_table,
                target_ns.nspname AS target_schema,
                target.relname AS target_table,
                con.conname AS constraint_name,
                pg_get_constraintdef(con.oid, true) AS definition,
                ARRAY_AGG(src_att.attname ORDER BY src_cols.ord) AS source_columns,
                ARRAY_AGG(target_att.attname ORDER BY src_cols.ord) AS target_columns
            FROM pg_constraint con
            JOIN pg_class src ON src.oid = con.conrelid
            JOIN pg_namespace src_ns ON src_ns.oid = src.relnamespace
            JOIN pg_class target ON target.oid = con.confrelid
            JOIN pg_namespace target_ns ON target_ns.oid = target.relnamespace
            JOIN unnest(con.conkey) WITH ORDINALITY AS src_cols(attnum, ord) ON true
            JOIN unnest(con.confkey) WITH ORDINALITY AS target_cols(attnum, ord) ON target_cols.ord = src_cols.ord
            JOIN pg_attribute src_att ON src_att.attrelid = src.oid AND src_att.attnum = src_cols.attnum
            JOIN pg_attribute target_att ON target_att.attrelid = target.oid AND target_att.attnum = target_cols.attnum
            WHERE con.contype = 'f'
              AND {$where}
            GROUP BY src_ns.nspname, src.relname, target_ns.nspname, target.relname, con.conname, con.oid
            ORDER BY source_schema, source_table, target_schema, target_table, constraint_name
        SQL, [$schemaName, $tableName]);

        return array_map(function (array $row): array {
            return [
                'direction' => $row['direction'],
                'sourceSchema' => $row['source_schema'],
                'sourceTable' => $row['source_table'],
                'sourceColumns' => $this->parsePgArray($row['source_columns'] ?? ''),
                'targetSchema' => $row['target_schema'],
                'targetTable' => $row['target_table'],
                'targetColumns' => $this->parsePgArray($row['target_columns'] ?? ''),
                'constraintName' => $row['constraint_name'],
                'definition' => $row['definition'] ?? '',
            ];
        }, $this->rows($rows));
    }

    protected function schemaPredicate(string $column): array
    {
        $clauses = [];
        $bindings = [];

        if (! empty($this->excludedSchemas)) {
            $placeholders = implode(',', array_fill(0, count($this->excludedSchemas), '?'));
            $clauses[] = "{$column} NOT IN ({$placeholders})";
            array_push($bindings, ...$this->excludedSchemas);
        }

        if (! empty($this->allowedSchemas)) {
            $placeholders = implode(',', array_fill(0, count($this->allowedSchemas), '?'));
            $clauses[] = "{$column} IN ({$placeholders})";
            array_push($bindings, ...$this->allowedSchemas);
        }

        return [empty($clauses) ? '' : ' AND ' . implode(' AND ', $clauses), $bindings];
    }

    protected function mapTableRow(array $row): array
    {
        return [
            'schemaName' => $row['schema_name'],
            'tableName' => $row['table_name'],
            'ownerName' => $row['owner_name'] ?? null,
            'tableType' => $row['table_type'],
            'isPartitioned' => $this->bool($row['is_partitioned'] ?? false),
            'estimatedRows' => (int) ($row['estimated_rows'] ?? 0),
            'totalSizeBytes' => (int) ($row['total_size_bytes'] ?? 0),
            'dataSizeBytes' => (int) ($row['data_size_bytes'] ?? 0),
            'indexSizeBytes' => (int) ($row['index_size_bytes'] ?? 0),
            'columnCount' => (int) ($row['column_count'] ?? 0),
            'indexCount' => (int) ($row['index_count'] ?? 0),
            'foreignKeyCount' => (int) ($row['foreign_key_count'] ?? 0),
            'hasPrimaryKey' => $this->bool($row['has_primary_key'] ?? false),
            'lastAnalyzedAt' => $row['last_analyzed_at'] ?? null,
            'supportsAnalyzeTimestamp' => true,
            'lastMaintenanceAt' => $row['last_maintenance_at'] ?? null,
            'liveRows' => (int) ($row['live_rows'] ?? 0),
            'deadRows' => (int) ($row['dead_rows'] ?? 0),
            'healthScore' => 100,
            'healthStatus' => 'healthy',
            'warningsCount' => 0,
            'findings' => [],
        ];
    }

    protected function constraintType(string $code): string
    {
        return match ($code) {
            'p' => 'PRIMARY KEY',
            'f' => 'FOREIGN KEY',
            'u' => 'UNIQUE',
            'c' => 'CHECK',
            'x' => 'EXCLUDE',
            default => strtoupper($code),
        };
    }

    protected function rows(array $rows): array
    {
        return array_map(fn ($row): array => (array) $row, $rows);
    }

    protected function row(mixed $row): array
    {
        return $row ? (array) $row : [];
    }

    protected function bool(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    protected function nullableInt(mixed $value): ?int
    {
        return $value === null ? null : (int) $value;
    }

    protected function parsePgArray(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter($value, fn ($item) => $item !== null && $item !== ''));
        }

        $value = trim((string) $value);

        if ($value === '' || $value === '{}') {
            return [];
        }

        if (str_starts_with($value, '{') && str_ends_with($value, '}')) {
            $value = substr($value, 1, -1);
        }

        if ($value === '') {
            return [];
        }

        return array_map(fn ($item) => trim($item, ' "'), str_getcsv($value));
    }
}
