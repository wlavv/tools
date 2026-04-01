<?php

namespace Modules\RoadmapManager\Providers;

use Illuminate\Support\ServiceProvider;

class RoadmapManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/roadmap-manager.php', 'roadmap-manager');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../Config/roadmap-manager.php' => config_path('roadmap-manager.php'),
        ], 'config');

        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'roadmap-manager');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }
}
