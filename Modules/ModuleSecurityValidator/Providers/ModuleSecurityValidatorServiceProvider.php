<?php

namespace Modules\ModuleSecurityValidator\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\ModuleSecurityValidator\Services\ModuleSecurityValidatorService;

class ModuleSecurityValidatorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/module-security-validator.php', 'module-security-validator');

        $this->app->singleton(ModuleSecurityValidatorService::class, function ($app) {
            return new ModuleSecurityValidatorService($app->make(\Modules\ModuleComplianceCore\Services\ComplianceScoreCalculator::class));
        });
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'module-security-validator');
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'module-security-validator');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->publishes([
            __DIR__ . '/../Config/module-security-validator.php' => config_path('module-security-validator.php'),
        ], 'module-security-validator-config');
    }
}
