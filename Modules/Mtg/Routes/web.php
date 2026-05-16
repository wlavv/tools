<?php

use Illuminate\Support\Facades\Route;
use Modules\Mtg\Http\Controllers\MtgController;
use Modules\Mtg\Http\Controllers\TcgCollectorsController;

Route::middleware(config('mtg.middleware', ['web', 'auth']))->group(function () {
    Route::prefix(config('mtg.route_prefix', 'webmaster/mtg'))->name('mtg.')->group(function () {
        Route::get('/', [MtgController::class, 'index'])->name('index');
        Route::get('/showSet/{code}/{sub_set?}', [MtgController::class, 'showSet'])->name('showSet');
        Route::get('/front/find', [MtgController::class, 'findCard'])->name('findCard');
        Route::post('/front/postCardDetail', [MtgController::class, 'postCardDetail'])->name('postCardDetail');
        Route::get('/generate/description/{id}', [MtgController::class, 'generateDescription'])->name('generateDescription');
        Route::post('/showSet/{code}/send-webcatalogue', [TcgCollectorsController::class, 'importFromSet'])->name('showSet.send_webcatalogue');
        Route::get('/tcg-collectors/sets', [TcgCollectorsController::class, 'index'])->name('tcg_collectors.index');
        Route::post('/tcg-collectors/sets/{setCode}/import', [TcgCollectorsController::class, 'import'])->name('tcg_collectors.import');
    });
});
