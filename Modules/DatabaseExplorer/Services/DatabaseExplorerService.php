<?php

namespace Modules\DatabaseExplorer\Services;

class DatabaseExplorerService
{
    public function __construct(
        protected PostgresMetadataProvider $metadataProvider,
        protected DatabaseHealthService $healthService
    ) {
    }

    public function overview(): array
    {
        $overview = $this->metadataProvider->getDatabaseOverview();
        $tables = $this->tables([]);
        $health = $this->healthService->calculateDatabaseHealth($overview, $tables);

        $overview['healthScore'] = $health['score'];
        $overview['healthStatus'] = $health['status'];
        $overview['findings'] = $health['findings'];

        return $overview;
    }

    public function schemas(): array
    {
        $schemas = $this->metadataProvider->getSchemas();
        $tables = $this->tables([]);

        return array_map(function (array $schema) use ($tables): array {
            $schemaTables = array_values(array_filter($tables, fn (array $table): bool => $table['schemaName'] === $schema['schemaName']));
            $scores = array_column($schemaTables, 'healthScore');
            $score = count($scores) > 0 ? (int) floor(array_sum($scores) / count($scores)) : 100;
            $schema['healthScore'] = $score;
            $schema['healthStatus'] = $this->mapScoreToStatus($score);

            return $schema;
        }, $schemas);
    }

    public function tables(array $filter = []): array
    {
        $tables = $this->metadataProvider->getTables($filter);

        $tables = array_map(function (array $table): array {
            $health = $this->healthService->calculateTableHealth($table);
            $table['healthScore'] = $health['score'];
            $table['healthStatus'] = $health['status'];
            $table['warningsCount'] = count($health['findings']);
            $table['findings'] = $health['findings'];

            return $table;
        }, $tables);

        $healthFilter = $filter['health'] ?? null;
        if ($healthFilter) {
            $tables = array_values(array_filter($tables, fn (array $table): bool => $table['healthStatus'] === $healthFilter));
        }

        return $tables;
    }

    public function table(string $schemaName, string $tableName): array
    {
        $table = $this->metadataProvider->getTableDetail($schemaName, $tableName);
        $tableHealth = $this->healthService->calculateTableHealth($table);

        $indexFindings = [];
        $indexes = array_map(function (array $index) use (&$indexFindings): array {
            $health = $this->healthService->calculateIndexHealth($index);
            $index['healthScore'] = $health['score'];
            $index['healthStatus'] = $health['status'];
            $index['findings'] = $health['findings'];
            array_push($indexFindings, ...$health['findings']);

            return $index;
        }, $table['indexes'] ?? []);

        $table['indexes'] = $indexes;
        $table['healthScore'] = $tableHealth['score'];
        $table['healthStatus'] = $tableHealth['status'];
        $table['findings'] = array_merge($tableHealth['findings'], $indexFindings);

        return $table;
    }

    public function healthFindings(array $filter = []): array
    {
        $tables = $this->tables($filter);
        $findings = [];

        foreach ($tables as $table) {
            array_push($findings, ...($table['findings'] ?? []));
        }

        $severity = $filter['severity'] ?? null;
        if ($severity) {
            $findings = array_values(array_filter($findings, fn (array $finding): bool => $finding['severity'] === $severity));
        }

        return $findings;
    }

    public function columns(string $schemaName, string $tableName): array
    {
        return $this->metadataProvider->getColumns($schemaName, $tableName);
    }

    public function indexes(string $schemaName, string $tableName): array
    {
        return $this->metadataProvider->getIndexes($schemaName, $tableName);
    }

    public function constraints(string $schemaName, string $tableName): array
    {
        return $this->metadataProvider->getConstraints($schemaName, $tableName);
    }

    public function relationships(string $schemaName, string $tableName): array
    {
        return $this->metadataProvider->getRelationships($schemaName, $tableName);
    }

    public function formatBytes(?int $bytes): string
    {
        $bytes = max(0, (int) $bytes);
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $index = 0;
        $value = (float) $bytes;

        while ($value >= 1024 && $index < count($units) - 1) {
            $value /= 1024;
            $index++;
        }

        return $index === 0
            ? number_format($value, 0) . ' ' . $units[$index]
            : number_format($value, 2) . ' ' . $units[$index];
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
