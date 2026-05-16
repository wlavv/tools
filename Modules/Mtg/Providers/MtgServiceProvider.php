<?php

namespace Modules\Mtg\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Mtg\Console\ImportTcgCollectorsSetCommand;

class MtgServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $modulePath = dirname(__DIR__);

        foreach (['config', 'actions', 'breadcrumbs', 'page_titles'] as $configFile) {
            $path = $modulePath . "/Config/{$configFile}.php";

            if (file_exists($path)) {
                $key = $configFile === 'config' ? 'mtg' : "mtg.{$configFile}";
                $this->mergeConfigFrom($path, $key);
            }
        }
    }

    public function boot(): void
    {
        $modulePath = dirname(__DIR__);

        $this->loadRoutesFrom($modulePath . '/Routes/web.php');
        $this->loadViewsFrom($modulePath . '/Resources/views', 'mtg');
        $this->loadTranslationsFrom($modulePath . '/Resources/lang', 'mtg');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ImportTcgCollectorsSetCommand::class,
            ]);
        }
    }
}
