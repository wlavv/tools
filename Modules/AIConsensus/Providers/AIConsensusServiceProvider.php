<?php

namespace Modules\AIConsensus\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\AIConsensus\Services\AIConsensusService;

class AIConsensusServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $modulePath = dirname(__DIR__);

        if (file_exists($modulePath . '/Config/config.php')) {
            $this->mergeConfigFrom($modulePath . '/Config/config.php', 'ai_consensus');
        }

        $this->app->singleton(AIConsensusService::class, fn () => new AIConsensusService());
    }

    public function boot(): void
    {
        $modulePath = dirname(__DIR__);

        $this->loadRoutesFrom($modulePath . '/Routes/web.php');
        $this->loadViewsFrom($modulePath . '/Resources/views', 'ai-consensus');
        $this->loadMigrationsFrom($modulePath . '/Database/Migrations');
        $this->loadTranslationsFrom($modulePath . '/Resources/lang', 'ai-consensus');
    }
}