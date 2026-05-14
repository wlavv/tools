<?php

namespace Modules\ModuleHealth\Providers;

use Illuminate\Support\ServiceProvider;

class ModuleHealthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'module-health');
        $this->mergeConfigFrom(__DIR__ . '/../Config/actions.php', 'module-health.actions');
        $this->mergeConfigFrom(__DIR__ . '/../Config/breadcrumbs.php', 'module-health.breadcrumbs');
        $this->mergeConfigFrom(__DIR__ . '/../Config/page_titles.php', 'module-health.page_titles');
        $this->mergeConfigFrom(__DIR__ . '/../Config/module_profiles.php', 'module-health.profiles');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'module-health');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'module-health');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }
}
