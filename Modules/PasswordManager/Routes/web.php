<?php

use Illuminate\Support\Facades\Route;
use Modules\PasswordManager\Http\Controllers\PasswordManagerController;

Route::middleware(config('password-manager.middleware', ['web', 'auth']))->prefix(config('password-manager.route_prefix', 'password-manager'))->name('password_manager.')->group(function () {
        Route::get('/', [PasswordManagerController::class, 'index'])->name('index');
        Route::get('/create', [PasswordManagerController::class, 'create'])->name('create');
        Route::post('/', [PasswordManagerController::class, 'store'])->name('store');
        Route::get('/{passwordEntry}', [PasswordManagerController::class, 'show'])->name('show');
        Route::get('/{passwordEntry}/edit', [PasswordManagerController::class, 'edit'])->name('edit');
        Route::put('/{passwordEntry}', [PasswordManagerController::class, 'update'])->name('update');
        Route::delete('/{passwordEntry}', [PasswordManagerController::class, 'destroy'])->name('destroy');
    });
