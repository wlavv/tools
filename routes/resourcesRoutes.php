<?php

use App\Http\Controllers\Areas\{
    dashboardController,
    adminController,
    webController,
    hrController,
    financeController,
    marketingController,
    customerSupportController,
    salesController,
    familyController,
    webCatalogueController,
    multiStoreController
};

Route::resources([
    'home'            => dashboardController::class,
    'dashboard'       => dashboardController::class,
    'administration'  => adminController::class,
    'web'             => webController::class,
    'finance'         => financeController::class,
    'marketing'       => marketingController::class,
    'customerSupport' => customerSupportController::class,
    'sales'           => salesController::class,
]);

Route::get('/hr/tasks/tablet', [TasksController::class, 'tablet'])->name('tasks.tablet');

Route::resource('hr', hrController::class)->only(['index']);


Route::resource('family', familyController::class)->only(['index']);
Route::resource('webCatalogue', webCatalogueController::class)->only(['index']);
Route::resource('multiStore', multiStoreController::class)->only(['index']);

