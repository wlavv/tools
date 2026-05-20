<?php

namespace Modules\IdeaLabComplianceCenterModule\Providers;

use Illuminate\Support\ServiceProvider;

class IdeaLabComplianceCenterModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $modulePath = dirname(__DIR__);

        if (file_exists($modulePath . '/Config/config.php')) {
            $this->mergeConfigFrom($modulePath . '/Config/config.php', 'module-compliance');
        }
    }

    public function boot(): void
    {
        $modulePath = dirname(__DIR__);

        if (file_exists($modulePath . '/Routes/web.php')) {
            $this->loadRoutesFrom($modulePath . '/Routes/web.php');
        }

        if (file_exists($modulePath . '/Routes/api.php')) {
            $this->loadRoutesFrom($modulePath . '/Routes/api.php');
        }

        $this->loadViewsFrom($modulePath . '/Resources/views', 'module-compliance');
        $this->loadTranslationsFrom($modulePath . '/Resources/lang', 'module-compliance');
        $this->loadMigrationsFrom($modulePath . '/Database/Migrations');
    }
}
