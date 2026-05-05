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
            $this->mergeConfigFrom($modulePath . '/Config/config.php', 'system_tools');
        }

        if (file_exists($modulePath . '/Config/tools.php')) {
            $this->mergeConfigFrom($modulePath . '/Config/tools.php', 'system-tools.tools');
            $this->mergeConfigFrom($modulePath . '/Config/tools.php', 'system_tools.tools');
        }

        $this->app->singleton(MaintenanceActionService::class, fn () => new MaintenanceActionService());
    }

    public function boot(): void
    {
        $modulePath = dirname(__DIR__);

        $this->loadRoutesFrom($modulePath . '/Routes/web.php');
        $this->loadViewsFrom($modulePath . '/Resources/views', 'system-tools');
        $this->loadMigrationsFrom($modulePath . '/Database/Migrations');
        $this->loadTranslationsFrom($modulePath . '/Resources/lang', 'system-tools');
    }
}
