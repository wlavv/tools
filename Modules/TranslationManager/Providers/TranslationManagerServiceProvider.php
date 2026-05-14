<?php

namespace Modules\TranslationManager\Providers;

use App\Support\Translations\LoadsModuleTranslationsWithOverrides;
use Illuminate\Support\ServiceProvider;

class TranslationManagerServiceProvider extends ServiceProvider
{
    use LoadsModuleTranslationsWithOverrides;

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'translation-manager');
        $this->mergeConfigFrom(__DIR__ . '/../Config/actions.php', 'translation-manager.actions');
        $this->mergeConfigFrom(__DIR__ . '/../Config/breadcrumbs.php', 'translation-manager.breadcrumbs');
        $this->mergeConfigFrom(__DIR__ . '/../Config/page_titles.php', 'translation-manager.page_titles');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'translation-manager');
        $this->loadModuleTranslationsWithOverrides(__DIR__ . '/../Resources/lang', 'translation-manager');
        $this->loadTranslationsFrom(resource_path('lang'), 'app');
    }
}
