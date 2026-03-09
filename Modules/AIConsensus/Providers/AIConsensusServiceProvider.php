<?php

namespace Modules\AIConsensus\Providers;

use Illuminate\Support\ServiceProvider;

class AIConsensusServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $configPath = dirname(__DIR__) . '/Config/config.php';

        if (file_exists($configPath)) {
            $this->mergeConfigFrom($configPath, 'ai-consensus');
        }
    }

    public function boot(): void
    {
        $moduleRoot = dirname(__DIR__);

        $routesPath = $moduleRoot . '/Routes/web.php';
        $viewsPath = $moduleRoot . '/Resources/views';
        $migrationsPath = $moduleRoot . '/Database/Migrations';
        $configPath = $moduleRoot . '/Config/config.php';

        if (file_exists($routesPath)) {
            $this->loadRoutesFrom($routesPath);
        }

        if (is_dir($viewsPath)) {
            $this->loadViewsFrom($viewsPath, 'ai-consensus');
        }

        if (is_dir($migrationsPath)) {
            $this->loadMigrationsFrom($migrationsPath);
        }

        if (file_exists($configPath)) {
            $this->publishes([
                $configPath => config_path('ai-consensus.php'),
            ], 'ai-consensus-config');
        }
    }
}