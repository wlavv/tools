<?php

namespace Modules\ModuleHealthBridge\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\ModuleComplianceCore\Contracts\ModuleValidatorInterface;
use Modules\ModuleHealthBridge\Services\ModuleHealthBridgeService;

class ModuleHealthBridgeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/module-health-bridge.php', 'module-health-bridge');

        $this->app->singleton(ModuleHealthBridgeService::class);

        $this->app->tag([
            ModuleHealthBridgeService::class,
        ], 'module.compliance.validators');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'module-health-bridge');
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'module-health-bridge');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        $this->publishes([
            __DIR__ . '/../Config/module-health-bridge.php' => config_path('module-health-bridge.php'),
        ], 'module-health-bridge-config');
    }
}
