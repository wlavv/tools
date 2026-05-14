<?php

namespace Modules\IntegrationHealth\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\IntegrationHealth\Console\EvaluateIntegrationHealthCommand;

class IntegrationHealthServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'IntegrationHealth';
    protected string $moduleNameLower = 'integration-health';

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'integration-health');
        $this->mergeConfigFrom(__DIR__ . '/../Config/actions.php', 'integration-health.actions');
        $this->mergeConfigFrom(__DIR__ . '/../Config/breadcrumbs.php', 'integration-health.breadcrumbs');
        $this->mergeConfigFrom(__DIR__ . '/../Config/page_titles.php', 'integration-health.page_titles');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                EvaluateIntegrationHealthCommand::class,
            ]);
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'integration-health');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'integration-health');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }
}
