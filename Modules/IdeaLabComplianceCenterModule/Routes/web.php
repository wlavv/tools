<?php

use Illuminate\Support\Facades\Route;
use Modules\IdeaLabComplianceCenterModule\Http\Controllers\IdeaLabComplianceCenterModuleController;

Route::middleware(['web', 'auth'])
    ->prefix(config('module-compliance.route_prefix', 'module-compliance'))
    ->name('module-compliance.')
    ->group(function () {
        Route::get('/', [IdeaLabComplianceCenterModuleController::class, 'index'])->name('index');
    });
