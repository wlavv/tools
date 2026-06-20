<?php

namespace Modules\LSG\ProductGrowth\ProductCore\Providers;

use Illuminate\Support\ServiceProvider;

class ProductCoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $modulePath = dirname(__DIR__);
        $this->mergeConfigFrom($modulePath . '/Config/config.php', 'product-core');
        $this->mergeConfigFrom($modulePath . '/Config/actions.php', 'product-core.actions');
        $this->mergeConfigFrom($modulePath . '/Config/breadcrumbs.php', 'product-core.breadcrumbs');
        $this->mergeConfigFrom($modulePath . '/Config/page_titles.php', 'product-core.page_titles');
        $this->mergeConfigFrom($modulePath . '/Config/permissions.php', 'product-core.permissions');
        $this->mergeConfigFrom($modulePath . '/Config/ui.php', 'product-core.ui');
        $this->mergeConfigFrom($modulePath . '/Config/workflow_areas.php', 'product-core.workflow_areas');
        $this->mergeConfigFrom($modulePath . '/Config/description_prompts.php', 'product-core.description_prompts');
    }

    public function boot(): void
    {
        $modulePath = dirname(__DIR__);
        $this->loadRoutesFrom($modulePath . '/Routes/web.php');
        $this->loadViewsFrom($modulePath . '/Resources/views', 'product-core');
        $this->loadTranslationsFrom($modulePath . '/Resources/lang', 'product-core');
        $this->loadMigrationsFrom($modulePath . '/Database/Migrations');
    }
}
