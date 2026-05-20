<?php

namespace Modules\ModuleIntegrationValidator\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\ModuleIntegrationValidator\Services\ModuleIntegrationValidatorService;

class ModuleIntegrationValidatorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/module-integration-validator.php', 'module-integration-validator');

        $this->app->singleton(ModuleIntegrationValidatorService::class, function ($app) {
            return new ModuleIntegrationValidatorService(
                scoreCalculator: $app->make(\Modules\ModuleComplianceCore\Services\ComplianceScoreCalculator::class),
            );
        });
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'module-integration-validator');
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'module-integration-validator');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }
}
