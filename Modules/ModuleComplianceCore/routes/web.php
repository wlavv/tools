<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])
    ->prefix('module-compliance/core')
    ->name('module_compliance.core.')
    ->group(function () {
        Route::view('/', 'module-compliance-core::index')->name('index');
    });
