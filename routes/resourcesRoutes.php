<?php

use App\Http\Controllers\Areas\{
    dashboardController,
    adminController,
    webController,
    hrController,
    financeController,
    purchasingController,
    logisticsController,
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
Route::resource('logistics',        logisticsController::class)->only(['index']);
Route::resource('marketing',        marketingController::class)->only(['index']);
Route::resource('customerSupport',  customerSupportController::class)->only(['index']);
Route::resource('sales',            salesController::class)->only(['index']);
Route::resource('hr',               hrController::class)->only(['index']);

Route::resource('family',           familyController::class)->only(['index']);
Route::resource('webcatalogue',     webCatalogueController::class)->only(['index']);
Route::resource('multiStore',       multiStoreController::class)->only(['index']);
Route::resource('lsg',              lsgController::class)->only(['index']);
Route::get('lsg/infraestrutura', [lsgController::class, 'infrastructure'])->name('lsg.infrastructure');
Route::get('lsg/stores', [lsgController::class, 'stores'])->name('lsg.stores');
Route::get('lsg/servicos', [lsgController::class, 'services'])->name('lsg.services');
Route::get('lsg/reporting', [lsgController::class, 'reporting'])->name('lsg.reporting');

Route::resource('shortcuts',        shortcutsController::class)->only(['index']);
Route::resource('settings',         settingsController::class)->only(['index']);
