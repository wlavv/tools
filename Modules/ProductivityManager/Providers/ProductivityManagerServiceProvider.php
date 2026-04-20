<?php

namespace Modules\ProductivityManager\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\ProductivityManager\Services\ProductivityDashboardService;

class ProductivityManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $modulePath = dirname(__DIR__);

        if (file_exists($modulePath . '/Config/config.php')) {
            $this->mergeConfigFrom($modulePath . '/Config/config.php', 'productivitymanager');
        }

        $this->app->singleton(
            ProductivityDashboardService::class,
            fn () => new ProductivityDashboardService()
        );
    }

    public function boot(): void
    {
        $modulePath = dirname(__DIR__);

        $this->loadRoutesFrom($modulePath . '/Routes/web.php');
        $this->loadViewsFrom($modulePath . '/Resources/views', 'productivitymanager');
        $this->loadMigrationsFrom($modulePath . '/Database/Migrations');
        $this->loadTranslationsFrom($modulePath . '/Resources/lang', 'productivity-manager');
    }
}
