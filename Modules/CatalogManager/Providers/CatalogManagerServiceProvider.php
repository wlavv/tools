<?php

namespace Modules\CatalogManager\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\CatalogManager\Console\RunStorePageSpeedInsightsCatalogueAliasCommand;
use Modules\CatalogManager\Console\RunStorePageSpeedInsightsCommand;

class CatalogManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'catalogmanager');
        $this->mergeConfigFrom(__DIR__ . '/../Config/actions.php', 'catalogmanager.actions');
        $this->mergeConfigFrom(__DIR__ . '/../Config/breadcrumbs.php', 'catalogmanager.breadcrumbs');
        $this->mergeConfigFrom(__DIR__ . '/../Config/page_titles.php', 'catalogmanager.page_titles');
        $this->mergeConfigFrom(__DIR__ . '/../Config/issue_panels.php', 'catalogmanager.issue_panels');
        $this->mergeConfigFrom(__DIR__ . '/../Config/action_panels.php', 'catalogmanager.action_panels');
        $this->mergeConfigFrom(__DIR__ . '/../Config/tables.php', 'catalogmanager.tables');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'catalogmanager');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'catalogmanager');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        $this->publishes([
            __DIR__ . '/../Resources/assets/css' => public_path('modules/catalogmanager/css'),
            __DIR__ . '/../Resources/assets/js' => public_path('modules/catalogmanager/js'),
        ], 'catalogmanager-assets');

        if ($this->app->runningInConsole()) {
            $this->commands([
                RunStorePageSpeedInsightsCommand::class,
                RunStorePageSpeedInsightsCatalogueAliasCommand::class,
            ]);
        }
    }
}
