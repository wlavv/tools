<?php

namespace Modules\ModuleDesignValidator\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\ModuleDesignValidator\Services\ModuleDesignValidatorService;

class ModuleDesignValidatorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/module-design-validator.php', 'module-design-validator');

        $this->app->singleton(ModuleDesignValidatorService::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'module-design-validator');
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'module-design-validator');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        $this->publishes([
            __DIR__ . '/../Config/module-design-validator.php' => config_path('module-design-validator.php'),
        ], 'module-design-validator-config');
    }
}
