<?php

namespace App\Support\Translations;

use Illuminate\Support\ServiceProvider;

class StorageTranslationOverrideServiceProvider extends ServiceProvider
{
    /**
     * Register a translation loader that supports module override files in storage.
     *
     * Add this provider to config/app.php providers, preferably before custom module providers:
     * App\Support\Translations\StorageTranslationOverrideServiceProvider::class,
     */
    public function register(): void
    {
        $this->app->extend('translation.loader', function ($loader, $app) {
            $langPath = $this->resolveLangPath($app);
            $overridePath = config('translation-manager.override_path', storage_path('app/translations/modules'));

            return new StorageOverrideFileLoader($app['files'], $langPath, $overridePath);
        });
    }

    protected function resolveLangPath($app): string
    {
        if (isset($app['path.lang'])) {
            return $app['path.lang'];
        }

        return resource_path('lang');
    }
}
