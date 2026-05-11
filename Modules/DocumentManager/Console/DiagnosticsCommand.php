<?php

namespace Modules\DocumentManager\Console;

use Illuminate\Console\Command;
use Modules\DocumentManager\Services\DiagnosticsService;

class DiagnosticsCommand extends Command
{
    protected $signature = 'document-manager:diagnostics';
    protected $description = 'Run DocumentManager diagnostics.';

    public function handle(DiagnosticsService $diagnostics): int
    {
        $report = $diagnostics->report();

        $this->info('DocumentManager ' . $report['module_version']);
        $this->line('Storage: ' . $report['storage']['message']);
        $this->line('OCR: ' . $report['ocr']['provider']);
        $this->line('AI: ' . $report['ai']['provider']);
        $this->line('Missing tables: ' . count($report['missing_tables']));

        foreach ($report['missing_tables'] as $table) {
            $this->warn('- ' . $table);
        }

        return self::SUCCESS;
    }
}
