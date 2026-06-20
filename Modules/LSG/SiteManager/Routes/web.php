<?php

use Illuminate\Support\Facades\Route;
use Modules\LSG\SiteManager\Http\Controllers\PageSpeedController;
use Modules\LSG\SiteManager\Http\Controllers\SiteController;
use Modules\LSG\SiteManager\Http\Controllers\SiteManagerDashboardController;

Route::middleware(config('site-manager.middleware', ['web', 'auth']))
    ->prefix(config('site-manager.route_prefix', 'lsg/site-manager'))
    ->name(config('site-manager.route_name', 'lsg.site_manager.'))
    ->group(function () {
        Route::get('/', SiteManagerDashboardController::class)->name('dashboard');
        Route::resource('sites', SiteController::class);
        Route::post('sites/{site}/pagespeed', [PageSpeedController::class, 'run'])->name('sites.pagespeed.run');
    });
