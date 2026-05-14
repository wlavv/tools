<?php

namespace Modules\ErrorCenter\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Modules\ErrorCenter\Http\Middleware\CaptureUnhandledExceptions;
use Modules\ErrorCenter\Services\ErrorCenterNotificationDispatcher;
use Modules\ErrorCenter\Services\ErrorCenterService;
use Modules\ErrorCenter\Services\ErrorContextSanitizer;
use Modules\ErrorCenter\Services\ErrorHashGenerator;

class ErrorCenterServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'error-center');
        $this->mergeConfigFrom(__DIR__ . '/../Config/actions.php', 'error-center.actions');
        $this->mergeConfigFrom(__DIR__ . '/../Config/breadcrumbs.php', 'error-center.breadcrumbs');
        $this->mergeConfigFrom(__DIR__ . '/../Config/page_titles.php', 'error-center.page_titles');

        $this->app->singleton(ErrorContextSanitizer::class);
        $this->app->singleton(ErrorHashGenerator::class);
        $this->app->singleton(ErrorCenterNotificationDispatcher::class);
        $this->app->singleton(ErrorCenterService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'error-center');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'error-center');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');

        $this->publishes([
            __DIR__ . '/../Config/config.php' => config_path('error-center.php'),
        ], 'error-center-config');

        $this->publishes([
            __DIR__ . '/../Resources/views' => resource_path('views/vendor/error-center'),
        ], 'error-center-views');

        $this->registerCaptureMiddleware();
    }

    private function registerCaptureMiddleware(): void
    {
        if (! config('error-center.capture.auto_register_middleware', true)) {
            return;
        }

        /** @var Router $router */
        $router = $this->app->make(Router::class);

        foreach ((array) config('error-center.capture.middleware_groups', ['web', 'api']) as $group) {
            if ($group !== '') {
                $router->pushMiddlewareToGroup($group, CaptureUnhandledExceptions::class);
            }
        }
    }
}
