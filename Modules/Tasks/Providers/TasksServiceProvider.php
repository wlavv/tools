<?php

namespace Modules\Tasks\Providers;

use Illuminate\Support\ServiceProvider;

class TasksServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $modulePath = dirname(__DIR__);

        if (file_exists($modulePath . '/Config/config.php')) {
            $this->mergeConfigFrom($modulePath . '/Config/config.php', 'tasks');
        }
    }

    public function boot(): void
    {
        $modulePath = dirname(__DIR__);

        $this->loadViewsFrom($modulePath . '/Resources/views', 'tasks');
        $this->loadMigrationsFrom($modulePath . '/Database/Migrations');
        $this->loadTranslationsFrom($modulePath . '/Resources/lang', 'tasks');

        $this->loadRoutesFrom($modulePath . '/Routes/web.php');
    }
}
