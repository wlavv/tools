<?php

use App\Http\Controllers\Areas\{
    dashboardController,
    adminController,
    webController,
    hrController,
    financeController,
    purchasingController,
    marketingController,
    customerSupportController,
    salesController,
    
    familyController,
    webCatalogueController,
    multiStoreController,
    lsgController,

    shortcutsController,
    settingsController
};


Route::resource('home',             dashboardController::class)->only(['index']);
Route::resource('dashboard',        dashboardController::class)->only(['index']);
Route::resource('administration',   adminController::class)->only(['index']);
Route::resource('web',              webController::class)->only(['index']);
Route::resource('finance',          financeController::class)->only(['index']);
Route::resource('purchasing',       purchasingController::class)->only(['index']);
Route::resource('marketing',        marketingController::class)->only(['index']);
Route::resource('customerSupport',  customerSupportController::class)->only(['index']);
Route::resource('sales',            salesController::class)->only(['index']);
Route::resource('hr',               hrController::class)->only(['index']);

Route::resource('family',           familyController::class)->only(['index']);
Route::resource('webcatalogue',     webCatalogueController::class)->only(['index']);
Route::resource('multiStore',       multiStoreController::class)->only(['index']);
Route::resource('lsg',              lsgController::class)->only(['index']);

Route::resource('shortcuts',        shortcutsController::class)->only(['index']);
Route::resource('settings',         settingsController::class)->only(['index']);
