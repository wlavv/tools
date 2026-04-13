<?php

namespace Modules\Calendar\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CalendarServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $modulePath = dirname(__DIR__);

        $this->loadViewsFrom($modulePath . '/Resources/views', 'calendar');
        $this->loadMigrationsFrom($modulePath . '/Database/Migrations');

        Route::middleware(['web', 'auth'])
            ->group($modulePath . '/Routes/web.php');
    }
}
