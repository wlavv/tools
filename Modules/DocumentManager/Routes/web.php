<?php

use Illuminate\Support\Facades\Route;
use Modules\DocumentManager\Http\Controllers\AiController;
use Modules\DocumentManager\Http\Controllers\CategoryController;
use Modules\DocumentManager\Http\Controllers\DashboardController;
use Modules\DocumentManager\Http\Controllers\DiagnosticsController;
use Modules\DocumentManager\Http\Controllers\DocumentAiController;
use Modules\DocumentManager\Http\Controllers\DocumentController;
use Modules\DocumentManager\Http\Controllers\DocumentOcrController;
use Modules\DocumentManager\Http\Controllers\FolderController;
use Modules\DocumentManager\Http\Controllers\SearchController;
use Modules\DocumentManager\Http\Controllers\TagController;
use Modules\DocumentManager\Http\Controllers\WorkflowController;
use Modules\DocumentManager\Http\Controllers\WorkspaceController;

Route::middleware(['web', 'auth'])
    ->prefix(config('documentmanager.route_prefix', 'document-manager'))
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('document-manager.dashboard');
        Route::get('/diagnostics', [DiagnosticsController::class, 'index'])->name('document-manager.diagnostics.index');
        Route::get('/workflow', [WorkflowController::class, 'index'])->name('document-manager.workflow.index');
        Route::get('/ai', [AiController::class, 'index'])->name('document-manager.ai.index');
        Route::get('/search', [SearchController::class, 'index'])->name('document-manager.search.index');

        Route::resource('workspaces', WorkspaceController::class)
            ->names('document-manager.workspaces')
            ->except(['show', 'destroy']);

        Route::resource('folders', FolderController::class)
            ->names('document-manager.folders')
            ->except(['show', 'destroy']);

        Route::resource('categories', CategoryController::class)
            ->names('document-manager.categories')
            ->except(['show', 'destroy']);

        Route::resource('tags', TagController::class)
            ->names('document-manager.tags')
            ->except(['show', 'destroy']);

        Route::get('/documents/{document}/preview', [DocumentController::class, 'preview'])->name('document-manager.documents.preview');
        Route::get('/documents/{document}/file', [DocumentController::class, 'file'])->name('document-manager.documents.file');
        Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('document-manager.documents.download');
        Route::get('/documents/{document}/ocr', [DocumentOcrController::class, 'show'])->name('document-manager.documents.ocr.show');
        Route::post('/documents/{document}/ocr/process', [DocumentOcrController::class, 'process'])->name('document-manager.documents.ocr.process');
        Route::post('/documents/{document}/ai/extract-expense', [DocumentAiController::class, 'extractExpense'])->name('document-manager.documents.ai.extract-expense');
        Route::get('/documents/{document}/ai/results', [DocumentAiController::class, 'showAiResults'])->name('document-manager.documents.ai.results');
        Route::get('/documents/{document}/ai-results/{aiResult}/create-expense', [DocumentAiController::class, 'createExpenseFromSuggestion'])->name('document-manager.documents.ai.create-expense');
        Route::post('/documents/{document}/process/{operation}', [DocumentController::class, 'process'])->name('document-manager.documents.process');
        Route::post('/documents/{document}/workflow', [DocumentController::class, 'workflow'])->name('document-manager.documents.workflow');
        Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('document-manager.documents.destroy');

        Route::resource('documents', DocumentController::class)
            ->names('document-manager.documents')
            ->except(['destroy']);
    });
