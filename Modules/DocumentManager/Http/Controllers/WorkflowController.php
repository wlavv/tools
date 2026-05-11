<?php

namespace Modules\DocumentManager\Http\Controllers;

use Modules\DocumentManager\Services\WorkflowService;
use Modules\DocumentManager\Support\DocumentTable;

class WorkflowController extends BaseDocumentController
{
    public function index(WorkflowService $workflow)
    {
        return view('documentmanager::workflow.index', [
            'stats' => $workflow->stats(),
            'approvals' => DocumentTable::safeGet('document_workflow_approvals', function ($query) {
                $query->orderByDesc('id')->limit(30);
            }),
            'tasks' => DocumentTable::safeGet('document_workflow_tasks', function ($query) {
                $query->orderByDesc('id')->limit(30);
            }),
        ]);
    }
}
