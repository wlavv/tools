<?php

namespace Modules\Calendar\Providers;

use Illuminate\Support\ServiceProvider;

class CalendarServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $modulePath = dirname(__DIR__);

        if (file_exists($modulePath . '/Config/config.php')) {
            $this->mergeConfigFrom($modulePath . '/Config/config.php', 'calendar');
        }
    }

    public function boot(): void
    {
        $modulePath = dirname(__DIR__);

        $this->loadViewsFrom($modulePath . '/Resources/views', 'calendar');
        $this->loadMigrationsFrom($modulePath . '/Database/Migrations');
        $this->loadTranslationsFrom($modulePath . '/Resources/lang', 'calendar');

        $this->loadRoutesFrom($modulePath . '/Routes/web.php');
    }
}
