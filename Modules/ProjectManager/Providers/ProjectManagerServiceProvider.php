<?php

namespace Modules\ProjectManager\Providers;

use Illuminate\Support\ServiceProvider;

class ProjectManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'project-manager');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'project-manager');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }
}
