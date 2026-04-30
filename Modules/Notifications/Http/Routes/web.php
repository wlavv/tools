<?php

use Illuminate\Support\Facades\Route;
use Modules\Notifications\Http\Controllers\NotificationController;

Route::middleware(['web', 'auth'])->prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/create', [NotificationController::class, 'create'])->name('notifications.create');
    Route::post('/', [NotificationController::class, 'store'])->name('notifications.store');
    Route::get('/settings', [NotificationController::class, 'settings'])->name('notifications.settings');
    Route::post('/settings', [NotificationController::class, 'saveSettings'])->name('notifications.settings.save');
    Route::get('/test', [NotificationController::class, 'test'])->name('notifications.test');
    Route::post('/test', [NotificationController::class, 'sendTest'])->name('notifications.test.send');
    Route::post('/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
    Route::get('/dropdown-data', [NotificationController::class, 'dropdownData'])->name('notifications.dropdownData');
    Route::post('/{notification}/mark-read', [NotificationController::class, 'markRead'])->name('notifications.markRead');
    Route::post('/{notification}/dismiss', [NotificationController::class, 'dismiss'])->name('notifications.dismiss');
    Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::get('/{notification}', [NotificationController::class, 'show'])->name('notifications.show');
});
