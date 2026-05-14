<?php

namespace Modules\DataExportCenter\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\DataExportCenter\Models\DataExportBatch;
use Modules\DataExportCenter\Support\ExportFormats;
use Modules\DataExportCenter\Support\ExportProfileTypes;
use RuntimeException;
use Throwable;

class ExportExecutorService
{
    public function __construct(
        private readonly ExportRegistry $registry,
        private readonly ExportSchemaBuilder $schemaBuilder,
        private readonly ExportQueryBuilderService $queryBuilder,
        private readonly DynamicQueryBuilderService $dynamicQueryBuilder,
        private readonly SelectOnlySqlGuard $sqlGuard,
        private readonly ExportWriterService $writer,
        private readonly ReportTemplateResolver $templateResolver,
        private readonly ReportRendererService $reportRenderer
    ) {
    }

    public function executeByKey(string $profileKey, array $filters = [], array $context = [], ?string $format = null): DataExportBatch
    {
        $profile = $this->registry->require($profileKey);
        $format = $format ?: ($profile['default_format'] ?? config('data-export-center.default_format', ExportFormats::CSV));
        $this->assertAllowedFormat($format);

        $disk = config('data-export-center.storage_disk', 'local');
        $uuid = (string) Str::uuid();
        $extension = $format === ExportFormats::PDF ? 'pdf' : $format;
        $path = trim(config('data-export-center.storage_path', 'data-export-center/exports'), '/')
            . '/' . now()->format('Y/m/d') . '/' . $uuid . '.' . $extension;

        $batch = DataExportBatch::query()->create([
            'uuid' => $uuid,
            'profile_key' => $profile['key'],
            'profile_type' => $profile['type'],
            'profile_class' => $profile['class'] ?? null,
            'status' => 'processing',
            'format' => $format,
            'disk' => $disk,
            'path' => $path,
            'download_name' => $this->downloadName($profile, $format),
            'filters' => $filters,
            'context' => $context,
            'metadata' => [],
            'created_by' => auth()->id(),
            'started_at' => now(),
        ]);

        try {
            $result = $this->executeProfile($profile, $filters, $context, $format, $disk, $path, $batch);

            $batch->update([
                'status' => 'completed',
                'rows_count' => $result['rows_count'] ?? 0,
                'query_sql' => $result['query_sql'] ?? null,
                'query_hash' => isset($result['query_sql']) ? hash('sha256', $result['query_sql']) : null,
                'report_template_id' => $result['report_template_id'] ?? null,
                'metadata' => $result['metadata'] ?? [],
                'finished_at' => now(),
            ]);
        } catch (Throwable $exception) {
            $batch->update([
                'status' => 'failed',
                'errors' => [$exception->getMessage()],
                'finished_at' => now(),
            ]);

            throw $exception;
        }

        return $batch->fresh();
    }

    private function executeProfile(array $profile, array $filters, array $context, string $format, string $disk, string $path, DataExportBatch $batch): array
    {
        [$rows, $headers, $querySql] = match ($profile['type']) {
            ExportProfileTypes::MODEL => $this->rowsForModelProfile($profile, $filters, $context, $format),
            ExportProfileTypes::SQL => $this->rowsForSqlProfile($profile),
            ExportProfileTypes::BUILDER => $this->rowsForBuilderProfile($profile, $filters),
            default => throw new RuntimeException("Unsupported export profile type [{$profile['type']}]."),
        };

        if (in_array($format, [ExportFormats::HTML, ExportFormats::PDF], true)) {
            return $this->writeReport($profile, $rows, $headers, $context, $format, $disk, $path, $batch, $querySql);
        }

        $writeResult = $this->writer->write($rows, $format, $disk, $path, $headers);
        $writeResult['query_sql'] = $querySql;

        return $writeResult;
    }

    private function rowsForModelProfile(array $profile, array $filters, array $context, string $format): array
    {
        $class = $profile['class'];
        $schema = $this->schemaBuilder->build($class);
        $query = $this->queryBuilder->build($class, $schema, $filters, $context);
        $limit = $this->limitForFormat($format);

        if ($limit > 0) {
            $query->limit($limit);
        }

        return [$query->cursor(), $schema['headers'], $query->toSql()];
    }

    private function rowsForSqlProfile(array $profile): array
    {
        $limit = (int) config('data-export-center.max_rows', 50000);
        $sql = $this->sqlGuard->withLimit((string) ($profile['query_sql'] ?? ''), $limit);
        $bindings = $profile['query_bindings'] ?? [];
        $rows = DB::select($sql, $bindings);
        $headers = empty($rows) ? [] : array_keys((array) $rows[0]);

        return [$rows, $headers, $sql];
    }

    private function rowsForBuilderProfile(array $profile, array $filters): array
    {
        $query = $this->dynamicQueryBuilder->build($profile['builder_definition'] ?? [], $filters);
        $limit = (int) config('data-export-center.max_rows', 50000);

        if ($limit > 0) {
            $query->limit($limit);
        }

        $rows = $query->cursor();
        $headers = collect($profile['builder_definition']['select'] ?? [])
            ->map(fn ($select) => $select['alias'] ?? null)
            ->filter()
            ->values()
            ->all();

        return [$rows, $headers, $query->toSql()];
    }

    private function writeReport(array $profile, iterable $rows, array $headers, array $context, string $format, string $disk, string $path, DataExportBatch $batch, ?string $querySql): array
    {
        $rowArray = [];
        foreach ($rows as $row) {
            $rowArray[] = (array) $row;
        }

        if (empty($headers) && ! empty($rowArray)) {
            $headers = array_keys($rowArray[0]);
        }

        $template = $this->templateResolver->resolve($profile['key'], array_merge($context, [
            'profile_key' => $profile['key'],
            'module' => $profile['module'] ?? null,
        ]));

        $html = $this->reportRenderer->renderHtml([
            'title' => $profile['label'] ?? config('data-export-center.reports.default_title', 'Export Report'),
            'profile' => $profile,
            'batch' => $batch,
            'headers' => $headers,
            'rows' => $rowArray,
            'rows_count' => count($rowArray),
            'context' => $context,
            'filters' => $batch->filters ?: [],
        ], $template);

        if ($format === ExportFormats::PDF) {
            $this->reportRenderer->storePdf($html, $disk, $path);
        } else {
            $this->reportRenderer->storeHtml($html, $disk, $path);
        }

        return [
            'rows_count' => count($rowArray),
            'query_sql' => $querySql,
            'report_template_id' => $template?->id,
            'metadata' => ['headers' => $headers],
        ];
    }

    private function limitForFormat(string $format): int
    {
        if (in_array($format, [ExportFormats::HTML, ExportFormats::PDF], true)) {
            return (int) config('data-export-center.reports.max_rows', 5000);
        }

        return (int) config('data-export-center.max_rows', 50000);
    }

    private function assertAllowedFormat(string $format): void
    {
        if (! in_array($format, config('data-export-center.allowed_formats', []), true)) {
            throw new RuntimeException("Export format [{$format}] is not allowed.");
        }
    }

    private function downloadName(array $profile, string $format): string
    {
        $key = str_replace(['/', '\\', ' '], '-', $profile['key']);

        return $key . '-' . now()->format('Ymd-His') . '.' . $format;
    }
}
