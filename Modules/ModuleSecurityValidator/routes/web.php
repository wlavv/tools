<?php

use Illuminate\Support\Facades\Route;
use Modules\ModuleSecurityValidator\Http\Controllers\ModuleSecurityValidatorController;

Route::middleware(['web', 'auth'])
    ->prefix('module-security-validator')
    ->name('module-security-validator.')
    ->group(function () {
        Route::get('/', [ModuleSecurityValidatorController::class, 'index'])->name('index');
        Route::post('/run', [ModuleSecurityValidatorController::class, 'run'])->name('run');
    });
