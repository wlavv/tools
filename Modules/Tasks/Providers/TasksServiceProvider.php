<?php

namespace Modules\Tasks\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class TasksServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $modulePath = dirname(__DIR__);

        $this->loadViewsFrom($modulePath . '/Resources/views', 'tasks');
        $this->loadMigrationsFrom($modulePath . '/Database/Migrations');

        Route::middleware(['web'])->group($modulePath . '/Routes/web.php');
    }
}
