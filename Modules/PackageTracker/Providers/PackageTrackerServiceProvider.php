<?php

namespace Modules\PackageTracker\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Modules\PackageTracker\Console\SyncPackageTrackerCommand;
use Modules\PackageTracker\Console\InstallPackageTrackerCommand;

class PackageTrackerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $modulePath = dirname(__DIR__);

        $this->mergeConfigFrom($modulePath . '/Config/config.php', 'package_tracker');
        $this->mergeConfigFrom($modulePath . '/Config/config.php', 'package-tracker');
        $this->mergeConfigFrom($modulePath . '/Config/actions.php', 'package_tracker.actions');
        $this->mergeConfigFrom($modulePath . '/Config/breadcrumbs.php', 'package_tracker.breadcrumbs');
        $this->mergeConfigFrom($modulePath . '/Config/page_titles.php', 'package_tracker.page_titles');
    }

    public function boot(): void
    {
        $modulePath = dirname(__DIR__);

        $this->loadRoutesFrom($modulePath . '/Routes/web.php');
        $this->loadViewsFrom($modulePath . '/Resources/views', 'package-tracker');
        $this->loadMigrationsFrom($modulePath . '/Database/migrations');
        $this->loadTranslationsFrom($modulePath . '/Resources/lang', 'package-tracker');

        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncPackageTrackerCommand::class,
                InstallPackageTrackerCommand::class,
            ]);

            $this->publishes([
                $modulePath . '/Config/config.php' => config_path('package_tracker.php'),
            ], 'package-tracker-config');
        }

        $this->app->booted(function () {
            if (config('package_tracker.polling.enabled')) {
                $this->app->make(Schedule::class)
                    ->command('package-tracker:sync --limit=100')
                    ->everyFifteenMinutes()
                    ->withoutOverlapping();
            }
        });
    }
}
