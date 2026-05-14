<?php

namespace Modules\DatabaseExplorer\Services;

use Illuminate\Support\Carbon;

class DatabaseHealthService
{
    public function calculateDatabaseHealth(array $overview, array $tables): array
    {
        $findings = [];
        $scores = [];

        foreach ($tables as $table) {
            $tableHealth = $this->calculateTableHealth($table);
            $scores[] = $tableHealth['score'];
            array_push($findings, ...$tableHealth['findings']);
        }

        $score = count($scores) > 0 ? (int) floor(array_sum($scores) / count($scores)) : 100;

        $criticalCount = count(array_filter($findings, fn ($finding) => $finding['severity'] === 'critical'));
        $warningCount = count(array_filter($findings, fn ($finding) => $finding['severity'] === 'warning'));

        if ($criticalCount > 0) {
            $findings[] = [
                'severity' => 'critical',
                'code' => 'DATABASE_HAS_CRITICAL_TABLE_FINDINGS',
                'message' => "Database has {$criticalCount} critical table finding(s).",
                'recommendation' => 'Review the affected tables and prioritize structural or maintenance issues.',
            ];
        }

        if ($warningCount > 0) {
            $findings[] = [
                'severity' => 'warning',
                'code' => 'DATABASE_HAS_WARNING_TABLE_FINDINGS',
                'message' => "Database has {$warningCount} warning table finding(s).",
                'recommendation' => 'Review table health findings and schedule maintenance where appropriate.',
            ];
        }

        return [
            'score' => $score,
            'status' => $this->mapScoreToStatus($score),
            'findings' => $findings,
        ];
    }

    public function calculateTableHealth(array $table): array
    {
        $score = 100;
        $findings = [];
        $schema = $table['schemaName'] ?? null;
        $name = $table['tableName'] ?? null;
        $fullName = trim(($schema ? $schema . '.' : '') . (string) $name, '.');
        $tableType = (string) ($table['tableType'] ?? '');
        $isPhysicalTable = in_array($tableType, ['BASE TABLE', 'PARTITIONED TABLE'], true);

        if ($isPhysicalTable && ! (bool) ($table['hasPrimaryKey'] ?? false)) {
            $score -= 30;
            $findings[] = $this->finding('critical', 'TABLE_WITHOUT_PRIMARY_KEY', $fullName, 'Table does not have a primary key.', 'Define a primary key or document why this table is intentionally keyless.', $schema, $name);
        }

        $lastAnalyzedAt = $this->date($table['lastAnalyzedAt'] ?? null);
        $supportsAnalyzeTimestamp = (bool) ($table['supportsAnalyzeTimestamp'] ?? true);
        $estimatedRows = (int) ($table['estimatedRows'] ?? 0);

        if ($supportsAnalyzeTimestamp && $isPhysicalTable && $estimatedRows > 0 && ! $lastAnalyzedAt) {
            $score -= 20;
            $findings[] = $this->finding('warning', 'TABLE_NEVER_ANALYZED', $fullName, 'Table has no recent analyze timestamp.', 'Run ANALYZE or verify autovacuum/analyze configuration.', $schema, $name);
        } elseif ($supportsAnalyzeTimestamp && $lastAnalyzedAt) {
            $staleDays = (int) config('database-explorer.health.stale_statistics_days', 7);
            if ($lastAnalyzedAt->lt(Carbon::now()->subDays($staleDays))) {
                $score -= 15;
                $findings[] = $this->finding('warning', 'STALE_STATISTICS', $fullName, "Statistics are older than {$staleDays} day(s).", 'Run ANALYZE or verify autovacuum/analyze frequency.', $schema, $name);
            }
        }

        $indexCount = (int) ($table['indexCount'] ?? 0);
        $maxIndexes = (int) config('database-explorer.health.max_indexes_warning', 15);
        if ($indexCount > $maxIndexes) {
            $score -= 10;
            $findings[] = $this->finding('warning', 'TOO_MANY_INDEXES', $fullName, "Table has {$indexCount} indexes.", 'Review redundant, duplicated or unused indexes.', $schema, $name);
        }

        $dataSize = (int) ($table['dataSizeBytes'] ?? 0);
        $indexSize = (int) ($table['indexSizeBytes'] ?? 0);
        $indexRatioLimit = (float) config('database-explorer.health.index_to_data_ratio_warning', 1.0);
        if ($dataSize > 0 && ($indexSize / $dataSize) > $indexRatioLimit) {
            $score -= 10;
            $findings[] = $this->finding('warning', 'HIGH_INDEX_TO_DATA_RATIO', $fullName, 'Index size is higher than the configured data-size ratio.', 'Review index usage and remove indexes that are not required.', $schema, $name);
        }

        $liveRows = (int) ($table['liveRows'] ?? 0);
        $deadRows = (int) ($table['deadRows'] ?? 0);
        $totalRows = $liveRows + $deadRows;
        if ($totalRows > 0) {
            $deadRatio = $deadRows / $totalRows;
            $criticalRatio = (float) config('database-explorer.health.dead_row_ratio_critical', 0.40);
            $warningRatio = (float) config('database-explorer.health.dead_row_ratio_warning', 0.20);

            if ($deadRatio >= $criticalRatio) {
                $score -= 25;
                $findings[] = $this->finding('critical', 'HIGH_DEAD_ROW_RATIO', $fullName, 'Dead row ratio is critical.', 'Run VACUUM, review autovacuum settings and investigate update/delete volume.', $schema, $name, ['deadRatio' => $deadRatio]);
            } elseif ($deadRatio >= $warningRatio) {
                $score -= 15;
                $findings[] = $this->finding('warning', 'ELEVATED_DEAD_ROW_RATIO', $fullName, 'Dead row ratio is elevated.', 'Review autovacuum settings and table maintenance cadence.', $schema, $name, ['deadRatio' => $deadRatio]);
            }
        }

        $largeTableBytes = (int) config('database-explorer.health.large_table_bytes', 10737418240);
        if ($isPhysicalTable && ! (bool) ($table['isPartitioned'] ?? false) && (int) ($table['totalSizeBytes'] ?? 0) >= $largeTableBytes) {
            $score -= 5;
            $findings[] = $this->finding('warning', 'LARGE_TABLE_WITHOUT_PARTITIONING', $fullName, 'Large table is not partitioned.', 'Evaluate partitioning, archiving or retention policies.', $schema, $name);
        }

        $score = max(0, min(100, $score));

        return [
            'score' => $score,
            'status' => $this->mapScoreToStatus($score),
            'findings' => $findings,
        ];
    }

    public function calculateIndexHealth(array $index): array
    {
        $score = 100;
        $findings = [];
        $schema = $index['schemaName'] ?? null;
        $table = $index['tableName'] ?? null;
        $indexName = $index['indexName'] ?? null;

        if (! (bool) ($index['isValid'] ?? true)) {
            $score -= 40;
            $findings[] = [
                'severity' => 'critical',
                'code' => 'INVALID_INDEX',
                'message' => "Index {$indexName} is invalid.",
                'recommendation' => 'Rebuild or drop the invalid index after validating impact.',
                'schemaName' => $schema,
                'tableName' => $table,
                'indexName' => $indexName,
            ];
        }

        $minUnusedSize = (int) config('database-explorer.health.unused_index_min_size_bytes', 10485760);
        if (! (bool) ($index['isPrimary'] ?? false) && (int) ($index['scans'] ?? 0) === 0 && (int) ($index['sizeBytes'] ?? 0) >= $minUnusedSize) {
            $score -= 10;
            $findings[] = [
                'severity' => 'warning',
                'code' => 'POTENTIALLY_UNUSED_INDEX',
                'message' => "Index {$indexName} has no recorded scans and is above the configured size threshold.",
                'recommendation' => 'Confirm workload history before removing the index.',
                'schemaName' => $schema,
                'tableName' => $table,
                'indexName' => $indexName,
            ];
        }

        return [
            'score' => max(0, $score),
            'status' => $this->mapScoreToStatus(max(0, $score)),
            'findings' => $findings,
        ];
    }

    protected function finding(string $severity, string $code, string $fullName, string $message, string $recommendation, ?string $schemaName = null, ?string $tableName = null, array $metadata = []): array
    {
        return [
            'severity' => $severity,
            'code' => $code,
            'message' => trim("{$fullName}: {$message}"),
            'recommendation' => $recommendation,
            'schemaName' => $schemaName,
            'tableName' => $tableName,
            'metadata' => $metadata,
        ];
    }

    protected function date(mixed $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function mapScoreToStatus(int $score): string
    {
        if ($score >= 90) {
            return 'healthy';
        }

        if ($score >= 70) {
            return 'warning';
        }

        if ($score >= 50) {
            return 'degraded';
        }

        return 'critical';
    }
}
