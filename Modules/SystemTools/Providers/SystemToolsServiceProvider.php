<?php

namespace Modules\SystemTools\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\SystemTools\Services\MaintenanceActionService;

class SystemToolsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $modulePath = dirname(__DIR__);

        if (file_exists($modulePath . '/Config/config.php')) {
            $this->mergeConfigFrom($modulePath . '/Config/config.php', 'system-tools');
        }

        if (file_exists($modulePath . '/Config/tools.php')) {
            $this->mergeConfigFrom($modulePath . '/Config/tools.php', 'system-tools.tools');
        }

        if (file_exists($modulePath . '/Config/actions.php')) {
            $this->mergeConfigFrom($modulePath . '/Config/actions.php', 'system-tools.actions');
        }

        if (file_exists($modulePath . '/Config/breadcrumbs.php')) {
            $this->mergeConfigFrom($modulePath . '/Config/breadcrumbs.php', 'system-tools.breadcrumbs');
        }

        if (file_exists($modulePath . '/Config/page_titles.php')) {
            $this->mergeConfigFrom($modulePath . '/Config/page_titles.php', 'system-tools.page_titles');
        }

        $this->app->singleton(MaintenanceActionService::class, fn () => new MaintenanceActionService());
    }

    public function boot(): void
    {
        $modulePath = dirname(__DIR__);

        $this->loadRoutesFrom($modulePath . '/Routes/web.php');
        $this->loadViewsFrom($modulePath . '/Resources/views', 'system-tools');
        $this->loadMigrationsFrom($modulePath . '/Database/Migrations');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'system-tools');
    }
}
