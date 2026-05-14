<?php

use Illuminate\Support\Facades\Route;
use Modules\TranslationManager\Http\Controllers\TranslationManagerController;

Route::middleware(config('translation-manager.route_middleware', ['web', 'auth']))
    ->prefix(config('translation-manager.route_prefix', 'settings/translation-manager'))
    ->name('translation_manager.')
    ->group(function () {
        Route::get('/', [TranslationManagerController::class, 'index'])->name('index');
        Route::post('/save', [TranslationManagerController::class, 'save'])->name('save');
        Route::post('/remove-override', [TranslationManagerController::class, 'removeOverride'])->name('remove_override');
        Route::post('/remove-extra-key', [TranslationManagerController::class, 'removeExtraKey'])->name('remove_extra_key');
    });
