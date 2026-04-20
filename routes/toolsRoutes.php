<?php

use App\Http\Controllers\CustomTools\InvestmentsController;
use App\Http\Controllers\CustomTools\InvestmentsPositionController;
use App\Http\Controllers\CustomTools\InvestmentsAssetController;
use App\Http\Controllers\CustomTools\InvestmentsBrokerAccountController;

/****************************************** INVESTMENTS ******************************************/
Route::get('finance/investments', [investmentsController::class, 'index'])->name('investments.index');
Route::resource('finance/investments/positions', InvestmentsPositionController::class);
Route::post('finance/investments/positions/{position}/simulate-step', [InvestmentsPositionController::class, 'simulateStep'])->name('positions.simulateStep');
Route::resource('finance/investments/assets', InvestmentsAssetController::class)->only(['index', 'create', 'store']);
Route::resource('finance/investments/broker-accounts', InvestmentsBrokerAccountController::class);
Route::post('broker-accounts/{id}/ibkr/test', [InvestmentsBrokerAccountController::class, 'ibkrTest'])->name('broker-accounts.ibkr.test');
Route::post('broker-accounts/{id}/ibkr/sync', [InvestmentsBrokerAccountController::class, 'ibkrSyncAccounts'])->name('broker-accounts.ibkr.sync');
Route::post('broker-accounts/{id}/ibkr/select', [InvestmentsBrokerAccountController::class, 'ibkrSelectAccount'])->name('broker-accounts.ibkr.select');

/******************************************  READING ******************************************/
use App\Http\Controllers\ImageController;
Route::get('/camera',                                   [ImageController::class, 'camera'])->name('camera.view');
Route::post('/camera/check',                            [ImageController::class, 'cameraCheck'])->name('camera.check');
Route::get('/compare-image',                            [ImageController::class, 'compareExternalImage']);

/******************************************  MTG     ******************************************/
use App\Http\Controllers\mtg\mtgController;
Route::resources([ '/webmaster/mtg'                     => mtgController::class]);
Route::get('/webmaster/mtg/showSet/{code}/{sub_set?}',  [mtgController::class, 'showSet'])->name('mtg.showSet');
Route::get('/webmaster/mtg/front/find',                 [mtgController::class, 'findCard'])->name('mtg.findCard');
Route::post('/webmaster/mtg/front/postCardDetail',      [mtgController::class, 'postCardDetail'])->name('mtg.postCardDetail');
Route::get('/webmaster/mtg/generate/description/{id}',  [mtgController::class, 'generateDescription'])->name('mtg.generateDescription');