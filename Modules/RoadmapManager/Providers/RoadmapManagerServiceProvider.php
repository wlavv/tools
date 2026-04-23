<?php

namespace Modules\RoadmapManager\Providers;

use Illuminate\Support\ServiceProvider;

class RoadmapManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $modulePath = dirname(__DIR__);

        if (file_exists($modulePath . '/Config/roadmap-manager.php')) {
            $this->mergeConfigFrom($modulePath . '/Config/roadmap-manager.php', 'roadmap_manager');
        }
    }

    public function boot(): void
    {
        $modulePath = dirname(__DIR__);

        if (file_exists($modulePath . '/Routes/web.php')) {
            $this->loadRoutesFrom($modulePath . '/Routes/web.php');
        }

        if (is_dir($modulePath . '/Resources/views')) {
            $this->loadViewsFrom($modulePath . '/Resources/views', 'roadmap-manager');
        }

        if (is_dir($modulePath . '/Database/Migrations')) {
            $this->loadMigrationsFrom($modulePath . '/Database/Migrations');
        }

        if (is_dir($modulePath . '/Resources/lang')) {
            $this->loadTranslationsFrom($modulePath . '/Resources/lang', 'roadmap-manager');
        }
    }
}
