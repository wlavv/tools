<?php

use Illuminate\Support\Facades\Route;
use Modules\Budget\Http\Controllers\BudgetController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/budget', [BudgetController::class, 'index'])->name('budget.index');
    Route::get('/budget/reports/category', [BudgetController::class, 'categoryReport'])->name('budget.reports.category');
    Route::get('/budget/reports/subcategory', [BudgetController::class, 'subcategoryReport'])->name('budget.reports.subcategory');
    Route::get('/budget/reports/annual', [BudgetController::class, 'annualAnalysis'])->name('budget.reports.annual');

    Route::post('/budget/update-data', [BudgetController::class, 'updateData'])->name('budget.updateData');
    Route::post('/budget/update-forecast-data', [BudgetController::class, 'updateForecastData'])->name('budget.updateForecastData');
    Route::post('/budget/update-detail', [BudgetController::class, 'updateDetail'])->name('budget.updateDetail');
    Route::post('/budget/delete-detail', [BudgetController::class, 'deleteDetail'])->name('budget.deleteDetail');
    Route::post('/budget/add-objective', [BudgetController::class, 'addObjective'])->name('budget.addObjective');
    Route::post('/budget/set-objective-as-done', [BudgetController::class, 'setObjectiveAsDone'])->name('budget.setObjectiveAsDone');
});
