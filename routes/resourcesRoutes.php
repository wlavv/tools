<?php

/** Main routes **/

use App\Http\Controllers\Areas\dashboardController;
use App\Http\Controllers\Areas\adminController;
use App\Http\Controllers\Areas\webController;
use App\Http\Controllers\Areas\hrController;
use App\Http\Controllers\Areas\financeController;
use App\Http\Controllers\Areas\logisticsController;
use App\Http\Controllers\Areas\marketingController;
use App\Http\Controllers\Areas\customerSupportController;
use App\Http\Controllers\Areas\salesController;

/** AREAS **/
Route::resources([ 'home'           => dashboardController::class       ]);
Route::resources([ 'dashboard'      => dashboardController::class       ]);
Route::resources([ 'administration' => adminController::class           ]);
Route::resources([ 'web'            => webController::class             ]);
Route::resources([ 'hr'             => hrController::class              ]);
Route::resources([ 'finance'        => financeController::class         ]);
Route::resources([ 'logistics'      => logisticsController::class       ]);
Route::resources([ 'marketing'      => marketingController::class       ]);
Route::resources([ 'customerSupport'=> customerSupportController::class ]);
Route::resources([ 'sales'          => salesController::class           ]);
