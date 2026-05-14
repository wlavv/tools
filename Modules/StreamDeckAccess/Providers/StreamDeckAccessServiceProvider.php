<?php

namespace Modules\StreamDeckAccess\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Modules\StreamDeckAccess\Services\StreamDeckAccessService;

class StreamDeckAccessServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $modulePath = dirname(__DIR__);

        $this->registerModuleConfig($modulePath);

        $this->app->singleton(StreamDeckAccessService::class, fn () => new StreamDeckAccessService());
    }

    public function boot(): void
    {
        $modulePath = dirname(__DIR__);

        $this->loadRoutesFrom($modulePath . '/Routes/web.php');
        $this->loadViewsFrom($modulePath . '/Resources/views', 'streamdeck-access');
        $this->loadMigrationsFrom($modulePath . '/Database/Migrations');
        $this->loadTranslationsFrom($modulePath . '/Resources/lang', 'streamdeck-access');
        $this->configureRateLimiting();
    }

    protected function registerModuleConfig(string $modulePath): void
    {
        $mainConfigPath = $modulePath . '/Config/config.php';
        $uiConfigPath = $modulePath . '/Config/ui.php';

        $mainConfig = file_exists($mainConfigPath) ? require $mainConfigPath : [];
        $uiConfig = file_exists($uiConfigPath) ? require $uiConfigPath : [];

        foreach (['streamdeck-access', 'streamdeck_access', 'streamDeckAccess'] as $key) {
            if (file_exists($mainConfigPath)) {
                $this->mergeConfigFrom($mainConfigPath, $key);
            }

            $existing = (array) config($key, []);
            $merged = array_replace_recursive($mainConfig, $existing);

            if (empty(Arr::get($merged, 'types')) && !empty($mainConfig['types'])) {
                $merged['types'] = $mainConfig['types'];
            }

            if (empty(Arr::get($merged, 'tasks')) && !empty($mainConfig['tasks'])) {
                $merged['tasks'] = $mainConfig['tasks'];
            }

            if (!empty($uiConfig)) {
                $merged['ui'] = array_replace_recursive($uiConfig, (array) Arr::get($merged, 'ui', []));
            }

            config([$key => $merged]);
        }
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('streamdeck-access', function (Request $request): Limit {
            $identifier = (string) $request->route('identifier');
            $key = (string) $request->ip() . '|' . $identifier;

            return Limit::perMinute((int) config('streamdeck-access.rate_limit_per_minute', 30))->by($key);
        });
    }
}
