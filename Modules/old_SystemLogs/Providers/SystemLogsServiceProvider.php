<?php

namespace Modules\SystemLogs\Providers;

use Illuminate\Support\ServiceProvider;

class SystemLogsServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $modulePath = dirname(__DIR__);

        $this->loadRoutesFrom($modulePath . '/Routes/web.php');
        $this->loadViewsFrom($modulePath . '/Resources/views', 'system-logs');
        $this->loadMigrationsFrom($modulePath . '/Database/Migrations');
    }
}
