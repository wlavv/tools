<?php

namespace Modules\DataExportCenter\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\DataExportCenter\Console\Commands\DiagnoseExportsCommand;
use Modules\DataExportCenter\Services\DynamicQueryBuilderService;
use Modules\DataExportCenter\Services\ExportDependencyResolver;
use Modules\DataExportCenter\Services\ExportExecutorService;
use Modules\DataExportCenter\Services\ExportQueryBuilderService;
use Modules\DataExportCenter\Services\ExportReadinessService;
use Modules\DataExportCenter\Services\ExportRegistry;
use Modules\DataExportCenter\Services\ExportSchemaBuilder;
use Modules\DataExportCenter\Services\ExportWriterService;
use Modules\DataExportCenter\Services\ReportRendererService;
use Modules\DataExportCenter\Services\ReportTemplateResolver;
use Modules\DataExportCenter\Services\SelectOnlySqlGuard;

class DataExportCenterServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'data-export-center');
        $this->mergeConfigFrom(__DIR__ . '/../Config/actions.php', 'data-export-center.actions');
        $this->mergeConfigFrom(__DIR__ . '/../Config/breadcrumbs.php', 'data-export-center.breadcrumbs');
        $this->mergeConfigFrom(__DIR__ . '/../Config/page_titles.php', 'data-export-center.page_titles');

        $this->app->singleton(ExportRegistry::class);
        $this->app->singleton(ExportDependencyResolver::class);
        $this->app->singleton(ExportSchemaBuilder::class);
        $this->app->singleton(ExportQueryBuilderService::class);
        $this->app->singleton(DynamicQueryBuilderService::class);
        $this->app->singleton(SelectOnlySqlGuard::class);
        $this->app->singleton(ExportWriterService::class);
        $this->app->singleton(ReportTemplateResolver::class);
        $this->app->singleton(ReportRendererService::class);
        $this->app->singleton(ExportExecutorService::class);
        $this->app->singleton(ExportReadinessService::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'data-export-center');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'data-export-center');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        $this->publishes([
            __DIR__ . '/../Config/config.php' => config_path('data-export-center.php'),
        ], 'data-export-center-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                DiagnoseExportsCommand::class,
            ]);
        }
    }
}
