<?php

namespace Modules\Investments\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Investments\Services\IbkrClient;
use Modules\Investments\Services\InvestmentPositionService;

class InvestmentsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $modulePath = dirname(__DIR__);

        foreach (['config', 'actions', 'breadcrumbs', 'page_titles'] as $configFile) {
            $path = $modulePath . "/Config/{$configFile}.php";

            if (file_exists($path)) {
                $key = $configFile === 'config' ? 'investments' : "investments.{$configFile}";
                $this->mergeConfigFrom($path, $key);
            }
        }

        $this->app->singleton(InvestmentPositionService::class, fn () => new InvestmentPositionService());
        $this->app->bind(IbkrClient::class, fn () => new IbkrClient());
    }

    public function boot(): void
    {
        $modulePath = dirname(__DIR__);

        $this->loadRoutesFrom($modulePath . '/Routes/web.php');
        $this->loadViewsFrom($modulePath . '/Resources/views', 'investments');
        $this->loadMigrationsFrom($modulePath . '/Database/Migrations');
        $this->loadTranslationsFrom($modulePath . '/Resources/lang', 'investments');
    }
}
