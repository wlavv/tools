<?php

namespace Modules\DocumentManager\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\DocumentManager\Console\DiagnosticsCommand;
use Modules\DocumentManager\Console\ProcessDocumentsCommand;
use Modules\DocumentManager\Services\AiService;
use Modules\DocumentManager\Services\AuditService;
use Modules\DocumentManager\Services\DiagnosticsService;
use Modules\DocumentManager\Services\DocumentService;
use Modules\DocumentManager\Services\EmbeddingService;
use Modules\DocumentManager\Services\OcrService;
use Modules\DocumentManager\Services\PermissionService;
use Modules\DocumentManager\Services\PreviewService;
use Modules\DocumentManager\Services\RelationService;
use Modules\DocumentManager\Services\SearchService;
use Modules\DocumentManager\Services\ShareService;
use Modules\DocumentManager\Services\StorageService;
use Modules\DocumentManager\Services\TextExtractionService;
use Modules\DocumentManager\Services\TimelineService;
use Modules\DocumentManager\Services\WorkflowService;

class DocumentManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'documentmanager');
        $this->mergeConfigFrom(__DIR__ . '/../Config/actions.php', 'documentmanager.actions');
        $this->mergeConfigFrom(__DIR__ . '/../Config/breadcrumbs.php', 'documentmanager.breadcrumbs');
        $this->mergeConfigFrom(__DIR__ . '/../Config/page_titles.php', 'documentmanager.page_titles');
        $this->mergeConfigFrom(__DIR__ . '/../Config/tables.php', 'documentmanager.tables');
        $this->mergeConfigFrom(__DIR__ . '/../Config/panels.php', 'documentmanager.panels');

        $this->app->singleton(DocumentService::class);
        $this->app->singleton(StorageService::class);
        $this->app->singleton(TextExtractionService::class);
        $this->app->singleton(OcrService::class);
        $this->app->singleton(AiService::class);
        $this->app->singleton(WorkflowService::class);
        $this->app->singleton(PermissionService::class);
        $this->app->singleton(SearchService::class);
        $this->app->singleton(TimelineService::class);
        $this->app->singleton(PreviewService::class);
        $this->app->singleton(AuditService::class);
        $this->app->singleton(DiagnosticsService::class);
        $this->app->singleton(RelationService::class);
        $this->app->singleton(ShareService::class);
        $this->app->singleton(EmbeddingService::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'documentmanager');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'documentmanager');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'document-manager');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        $this->publishes([
            __DIR__ . '/../Resources/assets/css' => public_path('modules/documentmanager/css'),
            __DIR__ . '/../Resources/assets/js' => public_path('modules/documentmanager/js'),
        ], 'documentmanager-assets');

        if ($this->app->runningInConsole()) {
            $this->commands([
                DiagnosticsCommand::class,
                ProcessDocumentsCommand::class,
            ]);
        }
    }
}
