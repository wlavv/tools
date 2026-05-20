<?php

use Illuminate\Support\Facades\Route;
use Modules\ModuleStructureValidator\Http\Controllers\ModuleStructureValidatorController;

Route::middleware(['web', 'auth'])
    ->prefix('module-structure-validator')
    ->name('module_structure_validator.')
    ->group(function () {
        Route::get('/', [ModuleStructureValidatorController::class, 'index'])->name('index');
        Route::post('/run', [ModuleStructureValidatorController::class, 'run'])->name('run');
    });
