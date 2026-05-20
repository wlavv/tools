<?php

namespace Modules\ModuleStructureValidator\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\ModuleComplianceCore\Contracts\ModuleValidatorInterface;
use Modules\ModuleStructureValidator\Services\ModuleStructureValidatorService;

class ModuleStructureValidatorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/module-structure-validator.php', 'module-structure-validator');

        $this->app->singleton(ModuleStructureValidatorService::class, fn ($app) => new ModuleStructureValidatorService(
            scoreCalculator: $app->make(\Modules\ModuleComplianceCore\Services\ComplianceScoreCalculator::class),
        ));

        $this->app->bind(ModuleValidatorInterface::class . '.structure', ModuleStructureValidatorService::class);
    }

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'module-structure-validator');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'module-structure-validator');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        if (file_exists(__DIR__ . '/../routes/web.php')) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        }
    }
}
