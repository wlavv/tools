<?php

namespace Modules\AIConsensus\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\AIConsensus\Services\AIConsensusChatService;
use Modules\AIConsensus\Services\AIConsensusContextBuilder;
use Modules\AIConsensus\Services\AIConsensusEngine;
use Modules\AIConsensus\Services\AIConsensusGateway;
use Modules\AIConsensus\Services\AIConsensusModuleBlueprintService;
use Modules\AIConsensus\Services\AIConsensusOutputNormalizer;
use Modules\AIConsensus\Services\AIConsensusPromptBuilder;
use Modules\AIConsensus\Services\AIConsensusProviderOrchestrator;
use Modules\AIConsensus\Services\AIConsensusRunService;
use Modules\AIConsensus\Services\AIConsensusSchemaValidator;
use Modules\AIConsensus\Services\AIConsensusScoringService;
use Modules\AIConsensus\Services\AIConsensusService;
use Modules\AIConsensus\Services\AIConsensusTemplateResolver;

class AIConsensusServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $modulePath = dirname(__DIR__);

        if (file_exists($modulePath . '/Config/config.php')) {
            $this->mergeConfigFrom($modulePath . '/Config/config.php', 'ai-consensus');
            $this->mergeConfigFrom($modulePath . '/Config/config.php', 'ai_consensus');
        }

        foreach ([
            'templates' => 'ai-consensus-templates',
            'providers' => 'ai-consensus-providers',
            'output-types' => 'ai-consensus-output-types',
            'consensus-rules' => 'ai-consensus-rules',
            'lsg-module-standard' => 'ai-consensus-lsg',
        ] as $file => $key) {
            $path = $modulePath . "/Config/{$file}.php";
            if (file_exists($path)) {
                $this->mergeConfigFrom($path, $key);
                $this->mergeConfigFrom($path, str_replace('-', '_', $key));
            }
        }

        $this->app->singleton(AIConsensusService::class, fn () => new AIConsensusService());
        $this->app->singleton(AIConsensusGateway::class);
        $this->app->singleton(AIConsensusRunService::class);
        $this->app->singleton(AIConsensusTemplateResolver::class);
        $this->app->singleton(AIConsensusContextBuilder::class);
        $this->app->singleton(AIConsensusProviderOrchestrator::class);
        $this->app->singleton(AIConsensusEngine::class);
        $this->app->singleton(AIConsensusOutputNormalizer::class);
        $this->app->singleton(AIConsensusPromptBuilder::class);
        $this->app->singleton(AIConsensusSchemaValidator::class);
        $this->app->singleton(AIConsensusChatService::class);
        $this->app->singleton(AIConsensusModuleBlueprintService::class);
        $this->app->singleton(AIConsensusScoringService::class);
    }

    public function boot(): void
    {
        $modulePath = dirname(__DIR__);

        $this->loadRoutesFrom($modulePath . '/Routes/web.php');
        if (file_exists($modulePath . '/Routes/api.php')) {
            $this->loadRoutesFrom($modulePath . '/Routes/api.php');
        }
        $this->loadViewsFrom($modulePath . '/Resources/views', 'ai-consensus');
        $this->loadMigrationsFrom($modulePath . '/Database/Migrations');
        $this->loadTranslationsFrom($modulePath . '/Resources/lang', 'ai-consensus');
    }
}
