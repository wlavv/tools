<?php

namespace Modules\ERP\Providers;

use Illuminate\Support\ServiceProvider;

class ERPServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'ERP';
    protected string $moduleNameLower = 'erp';

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'erp');
        $this->mergeConfigFrom(__DIR__ . '/../Config/actions.php', 'erp.actions');
        $this->mergeConfigFrom(__DIR__ . '/../Config/breadcrumbs.php', 'erp.breadcrumbs');
        $this->mergeConfigFrom(__DIR__ . '/../Config/page_titles.php', 'erp.page_titles');
        $this->mergeConfigFrom(__DIR__ . '/../Config/navigation.php', 'erp.navigation');
        $this->mergeConfigFrom(__DIR__ . '/../Config/statuses.php', 'erp.statuses');
        $this->mergeConfigFrom(__DIR__ . '/../Config/timeline.php', 'erp.timeline');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'erp');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'erp');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        $this->publishes([
            __DIR__ . '/../Config/config.php' => config_path('erp.php'),
        ], 'erp-config');

        $this->publishes([
            __DIR__ . '/../Resources/assets/css/erp.css' => public_path('modules/erp/css/erp.css'),
            __DIR__ . '/../Resources/assets/js/erp.js' => public_path('modules/erp/js/erp.js'),
        ], 'erp-assets');
    }
}
