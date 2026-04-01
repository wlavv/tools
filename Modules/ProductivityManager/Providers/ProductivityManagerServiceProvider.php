<?php

namespace Modules\ProductivityManager\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ProductivityManagerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerConfig();
        $this->registerViews();
        $this->registerMigrations();
        $this->registerRoutes();
    }

    public function register(): void
    {
        $this->app->singleton(
            \Modules\ProductivityManager\Services\ProductivityDashboardService::class,
            fn () => new \Modules\ProductivityManager\Services\ProductivityDashboardService()
        );
    }

    protected function registerConfig(): void
    {
        $configPath = __DIR__ . '/../Config/config.php';
        if (file_exists($configPath)) {
            $this->mergeConfigFrom($configPath, 'productivitymanager');
            $this->publishes([$configPath => config_path('productivitymanager.php')], 'config');
        }
    }

    protected function registerViews(): void
    {
        $viewPath = __DIR__ . '/../Resources/views';
        $this->loadViewsFrom($viewPath, 'productivitymanager');
        $this->publishes([$viewPath => resource_path('views/modules/productivitymanager')], 'views');
    }

    protected function registerMigrations(): void
    {
        $migrationPath = __DIR__ . '/../Database/Migrations';
        if (is_dir($migrationPath)) {
            $this->loadMigrationsFrom($migrationPath);
        }
    }

    protected function registerRoutes(): void
    {
        $webPath = __DIR__ . '/../Routes/web.php';
        $apiPath = __DIR__ . '/../Routes/api.php';

        if (file_exists($webPath)) {
            Route::middleware(['web', 'auth'])
                ->group($webPath);
        }

        if (file_exists($apiPath)) {
            Route::prefix('api')
                ->middleware(['api', 'auth'])
                ->group($apiPath);
        }
    }
}
