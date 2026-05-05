<?php

namespace Modules\WebCatalogue\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\WebCatalogue\Services\Storage\WebCatalogueStorageService;

class WebCatalogueServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $modulePath = dirname(__DIR__);

        if (file_exists($modulePath . '/Config/config.php')) {
            $this->mergeConfigFrom($modulePath . '/Config/config.php', 'webcatalogue');
        }

        if (file_exists($modulePath . '/Config/import_templates.php')) {
            $this->mergeConfigFrom($modulePath . '/Config/import_templates.php', 'webcatalogue_import_templates');
        }

        if (file_exists($modulePath . '/Config/front_layouts.php')) {
            $this->mergeConfigFrom($modulePath . '/Config/front_layouts.php', 'webcatalogue_front_layouts');
        }

        $this->app->singleton(WebCatalogueStorageService::class, fn () => new WebCatalogueStorageService());
    }

    public function boot(): void
    {
        $modulePath = dirname(__DIR__);

        $this->loadRoutesFrom($modulePath . '/Routes/web.php');
        $this->loadViewsFrom($modulePath . '/Resources/views', 'webcatalogue');
        $this->loadMigrationsFrom($modulePath . '/Database/Migrations');
        $this->loadTranslationsFrom($modulePath . '/Resources/lang', 'webcatalogue');
    }
}
