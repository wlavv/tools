<?php

namespace Modules\JobQueueMonitor\Providers;

use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Modules\JobQueueMonitor\Services\JobQueueMonitorService;

class JobQueueMonitorServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'JobQueueMonitor';
    protected string $moduleNameLower = 'job-queue-monitor';

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', $this->moduleNameLower);
        $this->mergeConfigFrom(__DIR__ . '/../Config/actions.php', $this->moduleNameLower . '.actions');
        $this->mergeConfigFrom(__DIR__ . '/../Config/breadcrumbs.php', $this->moduleNameLower . '.breadcrumbs');
        $this->mergeConfigFrom(__DIR__ . '/../Config/page_titles.php', $this->moduleNameLower . '.page_titles');

        $this->app->singleton(JobQueueMonitorService::class, fn () => new JobQueueMonitorService());
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', $this->moduleNameLower);
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', $this->moduleNameLower);
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        $this->registerQueueListeners();
    }

    protected function registerQueueListeners(): void
    {
        Event::listen(JobProcessing::class, function (JobProcessing $event) {
            app(JobQueueMonitorService::class)->markStarted($event);
        });

        Event::listen(JobProcessed::class, function (JobProcessed $event) {
            app(JobQueueMonitorService::class)->markProcessed($event);
        });

        Event::listen(JobFailed::class, function (JobFailed $event) {
            app(JobQueueMonitorService::class)->markFailed($event);
        });
    }
}
