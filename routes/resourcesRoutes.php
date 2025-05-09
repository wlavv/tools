<?php

/** Main routes **/

use App\Http\Controllers\Areas\dashboardController;
use App\Http\Controllers\Areas\adminController;
use App\Http\Controllers\Areas\webController;
use App\Http\Controllers\Areas\hrController;
use App\Http\Controllers\Areas\financeController;
use App\Http\Controllers\Areas\marketingController;
use App\Http\Controllers\Areas\customerSupportController;
use App\Http\Controllers\Areas\salesController;

use App\Http\Controllers\mtg\mtgController;

/** MTG **/
Route::resources([ 'mtg'            => mtgController::class]);

Route::get('/mtg/showSet/{code}/{sub_set?}',   [mtgController::class, 'showSet'])->name('mtg.showSet');

Route::get('/mtg/front/find',   [mtgController::class, 'findCard'])->name('mtg.findCard');
Route::post('/mtg/front/postCardDetail',   [mtgController::class, 'postCardDetail'])->name('mtg.postCardDetail');



/** new approuch **/
Route::post('/mtg/front/process-image', [mtgController::class, 'processImage'])->name('mtg.processImage');

/** AREAS **/
Route::resources([ 'home'           => dashboardController::class       ]);
Route::resources([ 'dashboard'      => dashboardController::class       ]);
Route::resources([ 'administration' => adminController::class           ]);
Route::resources([ 'web'            => webController::class             ]);
Route::resources([ 'hr'             => hrController::class              ]);
Route::resources([ 'finance'        => financeController::class         ]);
Route::resources([ 'marketing'      => marketingController::class       ]);
Route::resources([ 'customerSupport'=> customerSupportController::class ]);
Route::resources([ 'sales'          => salesController::class           ]);
