<?php

namespace Modules\ModuleDependencyMap\Console\Commands;

use Illuminate\Console\Command;
use Modules\ModuleDependencyMap\Models\ModuleDependencyScan;
use Modules\ModuleDependencyMap\Services\ModuleDependencyScanner;

class ScanModuleDependenciesCommand extends Command
{
    protected $signature = 'module-dependency-map:scan {module? : Module name} {--all : Scan all modules}';

    protected $description = 'Scan module dependencies and update the Module Dependency Map database tables.';

    public function handle(ModuleDependencyScanner $scanner): int
    {
        $module = $this->argument('module');
        $scanAll = (bool) $this->option('all');

        if ($scanAll || ! $module) {
            $modules = $scanner->modules();

            if ($modules->isEmpty()) {
                $this->warn('No modules found.');
                return self::SUCCESS;
            }

            foreach ($modules as $moduleName) {
                $this->scanOne($scanner, $moduleName);
            }

            return self::SUCCESS;
        }

        $this->scanOne($scanner, (string) $module);

        return self::SUCCESS;
    }

    private function scanOne(ModuleDependencyScanner $scanner, string $module): void
    {
        $this->line("Scanning {$module}...");

        $scan = $scanner->scan($module);

        if ($scan->status === ModuleDependencyScan::STATUS_SUCCESS) {
            $this->info("{$module}: success | health={$scan->health_status} | risk={$scan->risk_score}");
            return;
        }

        $this->error("{$module}: failed | {$scan->error_message}");
    }
}
