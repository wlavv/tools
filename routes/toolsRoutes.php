<?php

/******************************************  READING ******************************************/
use App\Http\Controllers\ImageController;
Route::get('/camera',                                   [ImageController::class, 'camera'])->name('camera.view');
Route::post('/camera/check',                            [ImageController::class, 'cameraCheck'])->name('camera.check');
Route::get('/compare-image',                            [ImageController::class, 'compareExternalImage']);
