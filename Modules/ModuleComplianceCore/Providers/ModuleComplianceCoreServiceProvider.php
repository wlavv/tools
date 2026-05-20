<?php

namespace Modules\ModuleComplianceCore\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\ModuleComplianceCore\Services\ComplianceScoreCalculator;

class ModuleComplianceCoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/module-compliance-core.php', 'module-compliance-core');
        $this->app->singleton(ComplianceScoreCalculator::class, fn () => new ComplianceScoreCalculator());
    }

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'module-compliance-core');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'module-compliance-core');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        if (file_exists(__DIR__ . '/../routes/web.php')) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        }
    }
}
