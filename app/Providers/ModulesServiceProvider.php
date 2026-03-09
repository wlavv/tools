<?php

namespace App\Providers;

use App\Console\Commands\MakeCustomModuleCommand;
use App\Console\Commands\ModulesCheckCommand;
use App\Console\Commands\ModulesListCommand;
use App\Core\Modules\ModuleManager;
use App\Core\Modules\ModuleRegistry;
use Illuminate\Support\ServiceProvider;

class ModulesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(config_path('modules.php'), 'modules');

        $this->app->singleton(ModuleRegistry::class, fn () => new ModuleRegistry());
        $this->app->singleton(ModuleManager::class, fn ($app) => new ModuleManager($app['files'], $app[ModuleRegistry::class]));

        $this->commands([
            ModulesListCommand::class,
            ModulesCheckCommand::class,
            MakeCustomModuleCommand::class,
        ]);
    }

    public function boot(ModuleManager $moduleManager): void
    {
        $moduleManager->bootstrap();
    }
}
