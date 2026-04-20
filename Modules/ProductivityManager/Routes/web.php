<?php

use Illuminate\Support\Facades\Route;
use Modules\ProductivityManager\Http\Controllers\DashboardController;
use Modules\ProductivityManager\Http\Controllers\SettingsController;

Route::middleware(['web', 'auth'])->prefix('productivity-manager')->name('productivity_manager.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
});
