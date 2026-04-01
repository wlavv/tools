<?php

use Illuminate\Support\Facades\Route;
use Modules\ProductivityManager\Http\Controllers\ApiController;

Route::prefix('productivity-manager')->name('productivityManager.api.')->group(function () {
    Route::get('/dashboard', [ApiController::class, 'dashboard'])->name('dashboard');
    Route::post('/task/store', [ApiController::class, 'storeTask'])->name('task.store');
    Route::post('/task/complete', [ApiController::class, 'completeTask'])->name('task.complete');
    Route::post('/task/block', [ApiController::class, 'blockTask'])->name('task.block');
    Route::post('/alert/store', [ApiController::class, 'createAlert'])->name('alert.store');
});
