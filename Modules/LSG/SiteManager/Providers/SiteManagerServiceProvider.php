<?php

namespace Modules\LSG\SiteManager\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\LSG\SiteManager\Console\RunSitePageSpeedInsightsCommand;

class SiteManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $modulePath = dirname(__DIR__);

        $this->mergeConfigFrom($modulePath . '/Config/config.php', 'site-manager');
        $this->mergeConfigFrom($modulePath . '/Config/permissions.php', 'site-manager.permissions');
    }

    public function boot(): void
    {
        $modulePath = dirname(__DIR__);

        $this->loadRoutesFrom($modulePath . '/Routes/web.php');
        $this->loadViewsFrom($modulePath . '/Resources/views', 'site-manager');
        $this->loadMigrationsFrom($modulePath . '/Database/Migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                RunSitePageSpeedInsightsCommand::class,
            ]);
        }
    }
}
