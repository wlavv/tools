<?php

namespace Modules\ConfigInspector\Providers;

use Illuminate\Support\ServiceProvider;

class ConfigInspectorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'config-inspector');
        $this->mergeConfigFrom(__DIR__ . '/../Config/actions.php', 'config-inspector-actions');
        $this->mergeConfigFrom(__DIR__ . '/../Config/breadcrumbs.php', 'config-inspector-breadcrumbs');
        $this->mergeConfigFrom(__DIR__ . '/../Config/page_titles.php', 'config-inspector-page-titles');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'config-inspector');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'config-inspector');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }
}
