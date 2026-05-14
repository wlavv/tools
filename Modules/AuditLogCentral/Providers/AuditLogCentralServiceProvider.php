<?php

namespace Modules\AuditLogCentral\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\AuditLogCentral\Services\AuditLogService;

class AuditLogCentralServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'audit-log-central');
        $this->mergeConfigFrom(__DIR__ . '/../Config/actions.php', 'audit-log-central.actions');
        $this->mergeConfigFrom(__DIR__ . '/../Config/breadcrumbs.php', 'audit-log-central.breadcrumbs');
        $this->mergeConfigFrom(__DIR__ . '/../Config/page_titles.php', 'audit-log-central.page_titles');

        $this->app->singleton('audit-log-central', function ($app) {
            return new AuditLogService();
        });
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'audit-log-central');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'audit-log-central');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }
}
