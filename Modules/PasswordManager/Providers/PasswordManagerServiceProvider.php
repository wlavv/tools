<?php

namespace Modules\PasswordManager\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Modules\PasswordManager\Services\PasswordManagerService;

class PasswordManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $modulePath = dirname(__DIR__);

        $this->registerModuleConfig($modulePath);

        $this->app->singleton(PasswordManagerService::class, fn () => new PasswordManagerService());
    }

    public function boot(): void
    {
        $modulePath = dirname(__DIR__);

        $this->loadRoutesFrom($modulePath . '/Routes/web.php');
        $this->loadViewsFrom($modulePath . '/Resources/views', 'password-manager');
        $this->loadMigrationsFrom($modulePath . '/Database/Migrations');
        $this->loadTranslationsFrom($modulePath . '/Resources/lang', 'password-manager');
    }

    /**
     * Register module config using both naming conventions used in the BO:
     * - password-manager  => Laravel config/file convention for this module
     * - password_manager  => route/name convention used by older module code
     * - passwordManager   => camelCase convention used in some legacy views/tests
     *
     * mergeConfigFrom() alone is not enough when config is cached or when an empty
     * key already exists, because it preserves existing values. For critical UI
     * defaults such as categories we explicitly fill missing/empty values.
     */
    protected function registerModuleConfig(string $modulePath): void
    {
        $mainConfigPath = $modulePath . '/Config/config.php';
        $uiConfigPath = $modulePath . '/Config/ui.php';

        $mainConfig = file_exists($mainConfigPath) ? require $mainConfigPath : [];
        $uiConfig = file_exists($uiConfigPath) ? require $uiConfigPath : [];

        foreach (['password-manager', 'password_manager', 'passwordManager'] as $key) {
            if (file_exists($mainConfigPath)) {
                $this->mergeConfigFrom($mainConfigPath, $key);
            }

            $existing = (array) config($key, []);
            $merged = array_replace_recursive($mainConfig, $existing);

            // Guard against empty cached config values.
            if (empty(Arr::get($merged, 'categories')) && !empty($mainConfig['categories'])) {
                $merged['categories'] = $mainConfig['categories'];
            }

            if (!empty($uiConfig)) {
                $merged['ui'] = array_replace_recursive($uiConfig, (array) Arr::get($merged, 'ui', []));
            }

            config([$key => $merged]);
        }
    }
}
