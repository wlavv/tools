<?php

namespace Modules\DataImportWizard\Console\Commands;

use Illuminate\Console\Command;
use Modules\DataImportWizard\Services\ImportReadinessService;

class DiagnoseImportsCommand extends Command
{
    protected $signature = 'data-import-wizard:diagnose';

    protected $description = 'Diagnose Data Import Wizard registered import profiles.';

    public function handle(ImportReadinessService $readiness): int
    {
        $summary = $readiness->summary();

        $this->info('Data Import Wizard readiness');
        $this->table(['Metric', 'Value'], collect($summary['counters'])->map(fn ($value, $key) => [$key, $value])->values());

        $this->table(
            ['Key', 'Label', 'Module', 'Status', 'Fields', 'Dependencies'],
            collect($summary['profiles'])->map(fn ($profile) => [
                $profile['key'],
                $profile['label'],
                $profile['module'] ?: '-',
                $profile['status'],
                $profile['headers_count'],
                $profile['dependencies_count'],
            ])->values()
        );

        return self::SUCCESS;
    }
}
