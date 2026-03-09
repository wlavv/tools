<?php

namespace Modules\AssetLibrary\Providers;

use Illuminate\Support\ServiceProvider;

class AssetLibraryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $modulePath = dirname(__DIR__);

        if (file_exists($modulePath . '/Config/config.php')) {
            $this->mergeConfigFrom($modulePath . '/Config/config.php', 'asset-library');
        }
    }

    public function boot(): void
    {
        $modulePath = dirname(__DIR__);

        $this->loadRoutesFrom($modulePath . '/Routes/web.php');
        $this->loadViewsFrom($modulePath . '/Resources/views', 'asset-library');
        $this->loadMigrationsFrom($modulePath . '/Database/Migrations');
    }
}
