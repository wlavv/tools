<?php

namespace Modules\PasswordManager\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\PasswordManager\Services\PasswordManagerService;

class PasswordManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $modulePath = dirname(__DIR__);

        if (file_exists($modulePath . '/Config/config.php')) {
            $this->mergeConfigFrom($modulePath . '/Config/config.php', 'password-manager');
        }

        $this->app->singleton(PasswordManagerService::class, fn () => new PasswordManagerService());
    }

    public function boot(): void
    {
        $modulePath = dirname(__DIR__);

        $this->loadRoutesFrom($modulePath . '/Routes/web.php');
        $this->loadViewsFrom($modulePath . '/Resources/views', 'password-manager');
        $this->loadMigrationsFrom($modulePath . '/Database/Migrations');
    }
}
