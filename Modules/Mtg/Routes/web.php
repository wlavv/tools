<?php

use Illuminate\Support\Facades\Route;
use Modules\Mtg\Http\Controllers\MtgController;

Route::middleware(config('mtg.middleware', ['web', 'auth']))->group(function () {
    Route::prefix(config('mtg.route_prefix', 'webmaster/mtg'))->name('mtg.')->group(function () {
        Route::get('/', [MtgController::class, 'index'])->name('index');
        Route::get('/showSet/{code}/{sub_set?}', [MtgController::class, 'showSet'])->name('showSet');
        Route::get('/front/find', [MtgController::class, 'findCard'])->name('findCard');
        Route::post('/front/postCardDetail', [MtgController::class, 'postCardDetail'])->name('postCardDetail');
        Route::get('/generate/description/{id}', [MtgController::class, 'generateDescription'])->name('generateDescription');
    });
});
