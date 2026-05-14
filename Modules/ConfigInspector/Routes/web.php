<?php

use Illuminate\Support\Facades\Route;
use Modules\ConfigInspector\Http\Controllers\ConfigInspectorController;

Route::middleware(['web', 'auth'])
    ->prefix(config('config-inspector.route_prefix', 'config-inspector'))
    ->name(config('config-inspector.route_name', 'config_inspector') . '.')
    ->group(function () {
        Route::get('/', [ConfigInspectorController::class, 'index'])->name('index');
    });
