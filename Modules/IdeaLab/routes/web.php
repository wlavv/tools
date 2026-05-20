<?php

use Illuminate\Support\Facades\Route;
use Modules\IdeaLab\Http\Controllers\IdeaAiConsensusController;
use Modules\IdeaLab\Http\Controllers\IdeaConversionController;
use Modules\IdeaLab\Http\Controllers\IdeaLabController;
use Modules\IdeaLab\Http\Controllers\IdeaTemplateController;
use Modules\IdeaLab\Http\Controllers\IdeaWorkflowController;

Route::middleware(config('idealab.middleware', ['web', 'auth']))
    ->prefix(config('idealab.route_prefix', 'idealab'))
    ->name(config('idealab.route_name_prefix', 'idealab.'))
    ->group(function () {
        Route::get('/', [IdeaLabController::class, 'index'])->name('index');
        Route::get('/workflow', [IdeaWorkflowController::class, 'index'])->name('workflow.index');
        Route::get('/create', [IdeaLabController::class, 'create'])->name('create');
        Route::post('/', [IdeaLabController::class, 'store'])->name('store');

        Route::prefix('settings/templates')->name('templates.')->group(function () {
            Route::get('/', [IdeaTemplateController::class, 'index'])->name('index');
            Route::get('/create', [IdeaTemplateController::class, 'create'])->name('create');
            Route::post('/', [IdeaTemplateController::class, 'store'])->name('store');
            Route::get('/{template}/edit', [IdeaTemplateController::class, 'edit'])->name('edit');
            Route::put('/{template}', [IdeaTemplateController::class, 'update'])->name('update');
        });

        Route::post('/{idea}/ai-consensus/run', [IdeaAiConsensusController::class, 'run'])->name('ai.run');
        Route::post('/{idea}/convert', [IdeaConversionController::class, 'convert'])->name('convert');
        Route::post('/{idea}/workflow/discussion', [IdeaWorkflowController::class, 'requestDiscussion'])->name('workflow.discussion');
        Route::post('/{idea}/workflow/blueprint', [IdeaWorkflowController::class, 'requestBlueprint'])->name('workflow.blueprint');
        Route::post('/{idea}/workflow/reformulate', [IdeaWorkflowController::class, 'requestReformulation'])->name('workflow.reformulate');
        Route::post('/{idea}/workflow/sandbox', [IdeaWorkflowController::class, 'createSandbox'])->name('workflow.sandbox');
        Route::post('/{idea}/workflow/compliance', [IdeaWorkflowController::class, 'runCompliance'])->name('workflow.compliance');
        Route::post('/{idea}/workflow/request-changes', [IdeaWorkflowController::class, 'requestChanges'])->name('workflow.request_changes');
        Route::post('/{idea}/workflow/approve-go-live', [IdeaWorkflowController::class, 'approveGoLive'])->name('workflow.approve_go_live');

        Route::get('/{idea}', [IdeaLabController::class, 'show'])->name('show');
        Route::get('/{idea}/edit', [IdeaLabController::class, 'edit'])->name('edit');
        Route::put('/{idea}', [IdeaLabController::class, 'update'])->name('update');
        Route::delete('/{idea}', [IdeaLabController::class, 'destroy'])->name('destroy');
    });
