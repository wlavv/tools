<?php

namespace App\Console\Commands;

use App\Core\Modules\ModuleManager;
use Illuminate\Console\Command;

class ModulesCheckCommand extends Command
{
    protected $signature = 'modules:check';
    protected $description = 'Validate module manifests and required structure';

    public function handle(ModuleManager $moduleManager): int
    {
        $modules = $moduleManager->discover();

        if (empty($modules)) {
            $this->warn('No modules found.');
            return self::SUCCESS;
        }

        foreach ($modules as $module) {
            $moduleManager->registry()->add($module);
        }

        $hasErrors = false;

        foreach ($modules as $module) {
            $validation = $moduleManager->validateStructure($module);
            $errors = $validation[$module->slug()] ?? [];

            if (empty($errors)) {
                $this->info("✔ {$module->name()} [{$module->slug()}]");
                continue;
            }

            $hasErrors = true;
            $this->error("✖ {$module->name()} [{$module->slug()}]");

            foreach ($errors as $error) {
                $this->line("  - {$error}");
            }
        }

        return $hasErrors ? self::FAILURE : self::SUCCESS;
    }
}
