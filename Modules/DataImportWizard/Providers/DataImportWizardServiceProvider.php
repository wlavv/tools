<?php

namespace Modules\DataImportWizard\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\DataImportWizard\Services\CsvParserService;
use Modules\DataImportWizard\Services\ImportExecutorService;
use Modules\DataImportWizard\Services\ImportReadinessService;
use Modules\DataImportWizard\Services\ImportRegistry;
use Modules\DataImportWizard\Services\ImportSchemaBuilder;
use Modules\DataImportWizard\Services\ImportTemplateGeneratorService;
use Modules\DataImportWizard\Services\ImportValidatorService;
use Modules\DataImportWizard\Services\ImportDependencyResolver;
use Modules\DataImportWizard\Console\Commands\DiagnoseImportsCommand;

class DataImportWizardServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'data-import-wizard');
        $this->mergeConfigFrom(__DIR__ . '/../Config/actions.php', 'data-import-wizard.actions');
        $this->mergeConfigFrom(__DIR__ . '/../Config/breadcrumbs.php', 'data-import-wizard.breadcrumbs');
        $this->mergeConfigFrom(__DIR__ . '/../Config/page_titles.php', 'data-import-wizard.page_titles');

        $this->app->singleton(ImportRegistry::class);
        $this->app->singleton(ImportDependencyResolver::class);
        $this->app->singleton(ImportSchemaBuilder::class);
        $this->app->singleton(ImportTemplateGeneratorService::class);
        $this->app->singleton(CsvParserService::class);
        $this->app->singleton(ImportValidatorService::class);
        $this->app->singleton(ImportExecutorService::class);
        $this->app->singleton(ImportReadinessService::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'data-import-wizard');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'data-import-wizard');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        $this->publishes([
            __DIR__ . '/../Config/config.php' => config_path('data-import-wizard.php'),
        ], 'data-import-wizard-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                DiagnoseImportsCommand::class,
            ]);
        }
    }
}
