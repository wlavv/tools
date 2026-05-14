<?php

namespace Modules\PermissionRoleManager\Providers;

use Illuminate\Support\ServiceProvider;

class PermissionRoleManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'permission-role-manager');
        $this->mergeConfigFrom(__DIR__ . '/../Config/actions.php', 'permission-role-manager.actions');
        $this->mergeConfigFrom(__DIR__ . '/../Config/breadcrumbs.php', 'permission-role-manager.breadcrumbs');
        $this->mergeConfigFrom(__DIR__ . '/../Config/page_titles.php', 'permission-role-manager.page_titles');
        $this->mergeConfigFrom(__DIR__ . '/../Config/permissions.php', 'permission-role-manager.permissions');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'permission-role-manager');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'permission-role-manager');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }
}
