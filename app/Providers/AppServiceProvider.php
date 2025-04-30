<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;

use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    
    protected $prestashop;

    public function register(): void{ }
    
    public function boot(): void{
        Carbon::setLocale('pt');
     }
}
