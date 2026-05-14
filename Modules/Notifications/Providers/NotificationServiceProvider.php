<?php

namespace Modules\Notifications\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Modules\Notifications\Services\NotificationManager;

class NotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $modulePath = dirname(__DIR__);

        if (file_exists($modulePath . '/Config/config.php')) {
            $this->mergeConfigFrom($modulePath . '/Config/config.php', 'notifications');
        }

        $this->app->singleton(NotificationManager::class, function ($app) {
            return new NotificationManager($app);
        });

        $helpers = $modulePath . '/Support/helpers.php';

        if (file_exists($helpers)) {
            require_once $helpers;
        }
    }

    public function boot(): void
    {
        $modulePath = dirname(__DIR__);

        $this->loadRoutesFrom($modulePath . '/Routes/web.php');
        $this->loadViewsFrom($modulePath . '/Resources/views', 'notifications');
        $this->loadMigrationsFrom($modulePath . '/Database/Migrations');
        $this->loadTranslationsFrom($modulePath . '/Resources/lang', 'notifications');

        Blade::component(
            'notifications::components.dropdown',
            config('notifications.component_alias', 'notifications-dropdown')
        );

        Blade::component(
            'notifications::components.topbar-bell',
            'notifications-topbar-bell'
        );
    }
}
