<?php

namespace Modules\ModuleComplianceCenter\Providers;

use Illuminate\Support\ServiceProvider;

class ModuleComplianceCenterServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/module-compliance-center.php', 'module-compliance-center');
        $this->mergeConfigFrom(__DIR__ . '/../Config/validators.php', 'module-compliance-center.validators');
        $this->mergeConfigFrom(__DIR__ . '/../Config/scoring.php', 'module-compliance-center.scoring');
        $this->mergeConfigFrom(__DIR__ . '/../Config/statuses.php', 'module-compliance-center.statuses');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'module-compliance-center');
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'module-compliance-center');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        if (file_exists(__DIR__ . '/../routes/web.php')) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        }

        if (file_exists(__DIR__ . '/../routes/api.php')) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        }
    }
}
