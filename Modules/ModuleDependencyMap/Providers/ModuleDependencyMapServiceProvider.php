<?php

namespace Modules\ModuleDependencyMap\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\ModuleDependencyMap\Console\Commands\ScanModuleDependenciesCommand;
use Modules\ModuleDependencyMap\Services\ModuleDependencyMapBuilder;
use Modules\ModuleDependencyMap\Services\ModuleDependencyScanner;

class ModuleDependencyMapServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'module-dependency-map');
        $this->mergeConfigFrom(__DIR__ . '/../Config/actions.php', 'module-dependency-map.actions');
        $this->mergeConfigFrom(__DIR__ . '/../Config/breadcrumbs.php', 'module-dependency-map.breadcrumbs');
        $this->mergeConfigFrom(__DIR__ . '/../Config/page_titles.php', 'module-dependency-map.page_titles');

        $this->app->singleton(ModuleDependencyScanner::class);
        $this->app->singleton(ModuleDependencyMapBuilder::class);
    }

    public function boot(): void
    {
        if (! config('module-dependency-map.enabled', true)) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'module-dependency-map');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'module-dependency-map');

        $this->publishes([
            __DIR__ . '/../Config/config.php' => config_path('module-dependency-map.php'),
        ], 'module-dependency-map-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ScanModuleDependenciesCommand::class,
            ]);
        }
    }
}
