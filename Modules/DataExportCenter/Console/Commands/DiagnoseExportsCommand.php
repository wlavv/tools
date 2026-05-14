<?php

namespace Modules\DataExportCenter\Console\Commands;

use Illuminate\Console\Command;
use Modules\DataExportCenter\Services\ExportReadinessService;

class DiagnoseExportsCommand extends Command
{
    protected $signature = 'data-export-center:diagnose';

    protected $description = 'Diagnose Data Export Center profiles and dependency-aware export readiness.';

    public function handle(ExportReadinessService $readiness): int
    {
        $summary = $readiness->summary();

        $this->info('Data Export Center readiness');
        foreach ($summary['counters'] as $key => $value) {
            $this->line(str_pad($key, 32) . $value);
        }

        $this->newLine();
        $this->table(
            ['Key', 'Label', 'Type', 'Status', 'Headers', 'Errors'],
            collect($summary['profiles'])->map(fn ($profile) => [
                $profile['key'] ?? '-',
                $profile['label'] ?? '-',
                $profile['type'] ?? '-',
                $profile['status'] ?? '-',
                $profile['headers_count'] ?? 0,
                implode('; ', $profile['errors'] ?? []),
            ])->all()
        );

        return ($summary['counters']['invalid_profiles'] ?? 0) > 0 ? self::FAILURE : self::SUCCESS;
    }
}
