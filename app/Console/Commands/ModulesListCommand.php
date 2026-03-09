<?php

namespace App\Console\Commands;

use App\Core\Modules\ModuleManager;
use Illuminate\Console\Command;

class ModulesListCommand extends Command
{
    protected $signature = 'modules:list';
    protected $description = 'List all discovered modules';

    public function handle(ModuleManager $moduleManager): int
    {
        $modules = $moduleManager->discover();

        if (empty($modules)) {
            $this->warn('No modules found.');
            return self::SUCCESS;
        }

        $rows = [];

        foreach ($modules as $module) {
            $manifest = $module->manifest();

            $rows[] = [
                $manifest->name,
                $manifest->slug,
                $manifest->enabled ? 'yes' : 'no',
                $manifest->version,
                $manifest->provider,
            ];
        }

        $this->table(['Name', 'Slug', 'Enabled', 'Version', 'Provider'], $rows);

        return self::SUCCESS;
    }
}
