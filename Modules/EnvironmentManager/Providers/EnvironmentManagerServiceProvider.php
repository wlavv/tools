<?php

namespace Modules\EnvironmentManager\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Modules\EnvironmentManager\Services\EnvironmentManagerService;
use Modules\EnvironmentManager\Support\SensitiveValueMasker;

class EnvironmentManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $modulePath = dirname(__DIR__);

        $this->registerModuleConfig($modulePath);

        $this->app->singleton(SensitiveValueMasker::class, fn () => new SensitiveValueMasker());
        $this->app->singleton(EnvironmentManagerService::class, fn ($app) => new EnvironmentManagerService(
            $app->make(SensitiveValueMasker::class)
        ));
    }

    public function boot(): void
    {
        $modulePath = dirname(__DIR__);

        $this->loadRoutesFrom($modulePath . '/Routes/web.php');
        $this->loadViewsFrom($modulePath . '/Resources/views', 'environment-manager');
        $this->loadTranslationsFrom($modulePath . '/Resources/lang', 'environment-manager');
        $this->loadMigrationsFrom($modulePath . '/Database/Migrations');
    }

    /**
     * Register module config using the same naming conventions already used by the BO:
     * - environment-manager => Laravel config/file convention
     * - environment_manager => route/name convention
     * - environmentManager  => camelCase convention
     */
    protected function registerModuleConfig(string $modulePath): void
    {
        $mainConfigPath = $modulePath . '/Config/config.php';
        $uiConfigPath = $modulePath . '/Config/ui.php';

        $mainConfig = file_exists($mainConfigPath) ? require $mainConfigPath : [];
        $uiConfig = file_exists($uiConfigPath) ? require $uiConfigPath : [];

        foreach (['environment-manager', 'environment_manager', 'environmentManager'] as $key) {
            if (file_exists($mainConfigPath)) {
                $this->mergeConfigFrom($mainConfigPath, $key);
            }

            $existing = (array) config($key, []);
            $merged = array_replace_recursive($mainConfig, $existing);

            if (empty(Arr::get($merged, 'sensitive_patterns')) && ! empty($mainConfig['sensitive_patterns'])) {
                $merged['sensitive_patterns'] = $mainConfig['sensitive_patterns'];
            }

            if (empty(Arr::get($merged, 'runtime_env')) && ! empty($mainConfig['runtime_env'])) {
                $merged['runtime_env'] = $mainConfig['runtime_env'];
            }

            if (empty(Arr::get($merged, 'bo_module_configs')) && ! empty($mainConfig['bo_module_configs'])) {
                $merged['bo_module_configs'] = $mainConfig['bo_module_configs'];
            }

            if (! empty($uiConfig)) {
                $merged['ui'] = array_replace_recursive($uiConfig, (array) Arr::get($merged, 'ui', []));
            }

            config([$key => $merged]);
        }
    }
}
