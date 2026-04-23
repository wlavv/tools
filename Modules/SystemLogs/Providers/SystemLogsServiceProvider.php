<?php

namespace Modules\SystemLogs\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\SystemLogs\Services\SystemLogsService;

class SystemLogsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $modulePath = dirname(__DIR__);

        if (file_exists($modulePath . '/Config/config.php')) {
            $this->mergeConfigFrom($modulePath . '/Config/config.php', 'system-logs');
        }

        $this->app->singleton(SystemLogsService::class, fn () => new SystemLogsService());
    }

    public function boot(): void
    {
        $modulePath = dirname(__DIR__);

        $this->loadRoutesFrom($modulePath . '/Routes/web.php');
        $this->loadViewsFrom($modulePath . '/Resources/views', 'system-logs');
        $this->loadMigrationsFrom($modulePath . '/Database/Migrations');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'system-logs');
    }
}
