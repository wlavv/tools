<?php

use Illuminate\Support\Facades\Route;
use Modules\StreamDeckAccess\Http\Controllers\StreamDeckAccessController;
use Modules\StreamDeckAccess\Http\Controllers\StreamDeckTriggerController;

Route::middleware(config('streamdeck-access.middleware', ['web', 'auth']))
    ->prefix(config('streamdeck-access.route_prefix', 'streamdeck-access'))
    ->name('streamdeck_access.')
    ->group(function () {
        Route::get('/', [StreamDeckAccessController::class, 'index'])->name('index');
        Route::get('/create', [StreamDeckAccessController::class, 'create'])->name('create');
        Route::post('/', [StreamDeckAccessController::class, 'store'])->name('store');
        Route::get('/{accessPoint}', [StreamDeckAccessController::class, 'show'])->name('show');
        Route::get('/{accessPoint}/edit', [StreamDeckAccessController::class, 'edit'])->name('edit');
        Route::put('/{accessPoint}', [StreamDeckAccessController::class, 'update'])->name('update');
        Route::post('/{accessPoint}/rotate-token', [StreamDeckAccessController::class, 'rotateToken'])->name('rotate-token');
        Route::delete('/{accessPoint}', [StreamDeckAccessController::class, 'destroy'])->name('destroy');
    });

Route::middleware(config('streamdeck-access.public_middleware', ['api', 'throttle:streamdeck-access']))
    ->prefix(config('streamdeck-access.public_route_prefix', 'api/streamdeck'))
    ->name('streamdeck_access.external.')
    ->group(function () {
        Route::match(['GET', 'POST'], '/{identifier}', [StreamDeckTriggerController::class, 'trigger'])->name('trigger');
    });
