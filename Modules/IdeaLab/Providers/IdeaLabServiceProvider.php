<?php

namespace Modules\IdeaLab\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\IdeaLab\Services\AiConsensus\IdeaLabConsensusGateway;
use Modules\IdeaLab\Services\IdeaConversionService;
use Modules\IdeaLab\Services\IdeaScoringService;
use Modules\IdeaLab\Services\IdeaToolWorkflowService;

class IdeaLabServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/idealab.php', 'idealab');
        $this->mergeConfigFrom(__DIR__ . '/../Config/actions.php', 'idealab.actions');
        $this->mergeConfigFrom(__DIR__ . '/../Config/breadcrumbs.php', 'idealab.breadcrumbs');
        $this->mergeConfigFrom(__DIR__ . '/../Config/diagnostics.php', 'idealab.diagnostics');
        $this->mergeConfigFrom(__DIR__ . '/../Config/page_titles.php', 'idealab.page_titles');
        $this->mergeConfigFrom(__DIR__ . '/../Config/permissions.php', 'idealab.permissions');

        $this->app->singleton(IdeaScoringService::class);
        $this->app->singleton(IdeaLabConsensusGateway::class);
        $this->app->singleton(IdeaConversionService::class);
        $this->app->singleton(IdeaToolWorkflowService::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'idealab');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'idealab');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        $this->publishes([
            __DIR__ . '/../Config/idealab.php' => config_path('idealab.php'),
        ], 'idealab-config');

        $this->publishes([
            __DIR__ . '/../Resources/views' => resource_path('views/modules/idealab'),
        ], 'idealab-views');

        $this->publishes([
            __DIR__ . '/../Resources/lang' => resource_path('lang/modules/idealab'),
        ], 'idealab-lang');
    }
}
