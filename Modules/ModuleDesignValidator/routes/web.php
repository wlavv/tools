<?php

use Illuminate\Support\Facades\Route;
use Modules\ModuleDesignValidator\Http\Controllers\ModuleDesignValidatorController;

Route::middleware(['web', 'auth'])->prefix('module-design-validator')->name('module-design-validator.')->group(function () {
    Route::get('/', [ModuleDesignValidatorController::class, 'index'])->name('index');
    Route::post('/run', [ModuleDesignValidatorController::class, 'run'])->name('run');
});
