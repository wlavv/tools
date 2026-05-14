<?php

namespace Modules\DatabaseExplorer\Services;

use Illuminate\Support\Facades\DB;
use Modules\DatabaseExplorer\Models\DatabaseExplorerFinding;
use Modules\DatabaseExplorer\Models\DatabaseExplorerSnapshot;
use Modules\DatabaseExplorer\Models\DatabaseExplorerTableSnapshot;

class DatabaseSnapshotService
{
    public function __construct(protected DatabaseExplorerService $explorerService)
    {
    }

    public function list(int $limit = 50): array
    {
        $limit = max(1, min(200, $limit));

        return DatabaseExplorerSnapshot::query()
            ->withCount(['tables', 'findings'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (DatabaseExplorerSnapshot $snapshot): array => [
                'id' => $snapshot->id,
                'databaseName' => $snapshot->database_name,
                'engine' => $snapshot->engine,
                'schemaCount' => $snapshot->schema_count,
                'tableCount' => $snapshot->table_count,
                'viewCount' => $snapshot->view_count,
                'indexCount' => $snapshot->index_count,
                'totalSizeBytes' => $snapshot->total_size_bytes,
                'estimatedRows' => $snapshot->estimated_rows,
                'healthScore' => $snapshot->health_score,
                'healthStatus' => $snapshot->health_status,
                'tablesCount' => $snapshot->tables_count,
                'findingsCount' => $snapshot->findings_count,
                'createdAt' => optional($snapshot->created_at)->toDateTimeString(),
            ])
            ->all();
    }

    public function collect(): array
    {
        if (! (bool) config('database-explorer.snapshots.enabled', true)) {
            abort(403, 'Database Explorer snapshots are disabled.');
        }

        $overview = $this->explorerService->overview();
        $tables = $this->explorerService->tables([]);
        $now = now();

        return DB::transaction(function () use ($overview, $tables, $now): array {
            $snapshot = DatabaseExplorerSnapshot::query()->create([
                'database_name' => $overview['databaseName'] ?? null,
                'engine' => $overview['engine'] ?? null,
                'engine_version' => $overview['version'] ?? null,
                'schema_count' => (int) ($overview['schemaCount'] ?? 0),
                'table_count' => (int) ($overview['tableCount'] ?? 0),
                'view_count' => (int) ($overview['viewCount'] ?? 0),
                'index_count' => (int) ($overview['indexCount'] ?? 0),
                'total_size_bytes' => (int) ($overview['totalSizeBytes'] ?? 0),
                'estimated_rows' => (int) ($overview['estimatedRows'] ?? 0),
                'health_score' => (int) ($overview['healthScore'] ?? 0),
                'health_status' => $overview['healthStatus'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($tables as $table) {
                DatabaseExplorerTableSnapshot::query()->create([
                    'snapshot_id' => $snapshot->id,
                    'schema_name' => $table['schemaName'],
                    'table_name' => $table['tableName'],
                    'table_type' => $table['tableType'] ?? null,
                    'estimated_rows' => (int) ($table['estimatedRows'] ?? 0),
                    'total_size_bytes' => (int) ($table['totalSizeBytes'] ?? 0),
                    'data_size_bytes' => (int) ($table['dataSizeBytes'] ?? 0),
                    'index_size_bytes' => (int) ($table['indexSizeBytes'] ?? 0),
                    'column_count' => (int) ($table['columnCount'] ?? 0),
                    'index_count' => (int) ($table['indexCount'] ?? 0),
                    'foreign_key_count' => (int) ($table['foreignKeyCount'] ?? 0),
                    'has_primary_key' => (bool) ($table['hasPrimaryKey'] ?? false),
                    'last_analyzed_at' => $table['lastAnalyzedAt'] ?? null,
                    'last_maintenance_at' => $table['lastMaintenanceAt'] ?? null,
                    'health_score' => (int) ($table['healthScore'] ?? 0),
                    'health_status' => $table['healthStatus'] ?? null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                foreach (($table['findings'] ?? []) as $finding) {
                    DatabaseExplorerFinding::query()->create([
                        'snapshot_id' => $snapshot->id,
                        'schema_name' => $finding['schemaName'] ?? $table['schemaName'],
                        'table_name' => $finding['tableName'] ?? $table['tableName'],
                        'column_name' => $finding['columnName'] ?? null,
                        'index_name' => $finding['indexName'] ?? null,
                        'severity' => $finding['severity'] ?? 'info',
                        'code' => $finding['code'] ?? 'UNKNOWN',
                        'message' => $finding['message'] ?? '',
                        'recommendation' => $finding['recommendation'] ?? null,
                        'metadata' => $finding['metadata'] ?? null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            return [
                'id' => $snapshot->id,
                'databaseName' => $snapshot->database_name,
                'healthScore' => $snapshot->health_score,
                'healthStatus' => $snapshot->health_status,
                'tableCount' => count($tables),
                'createdAt' => $snapshot->created_at?->toDateTimeString(),
            ];
        });
    }
}
