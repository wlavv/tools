<?php

namespace Modules\Budget\Providers;

use Illuminate\Support\ServiceProvider;

class BudgetServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $modulePath = dirname(__DIR__);

        $this->loadRoutesFrom($modulePath . '/Routes/web.php');
        $this->loadViewsFrom($modulePath . '/Resources/views', 'budget');
        $this->loadMigrationsFrom($modulePath . '/Database/migrations');
    }
}
