<?php

namespace Modules\DocumentManager\Http\Controllers;

use Modules\DocumentManager\Repositories\DocumentRepository;
use Modules\DocumentManager\Services\Panels\OperationalPanelManager;
use Modules\DocumentManager\Support\DocumentLogger;
use Modules\DocumentManager\Support\DocumentTable;

class DashboardController extends BaseDocumentController
{
    public function index(DocumentRepository $documents, OperationalPanelManager $panels)
    {
        try {
            $stats = [
                'documents' => DocumentTable::count('document_core_documents'),
                'workspaces' => DocumentTable::count('document_core_workspaces'),
                'pending_workflow' => DocumentTable::count('document_workflow_approvals', function ($query) {
                    $query->where('status', 'pending');
                }),
                'uncategorized' => DocumentTable::count('document_core_documents', function ($query) {
                    $query->whereNull('category_id');
                }),
            ];

            return view('documentmanager::dashboard.index', [
                'stats' => $stats,
                'latestDocuments' => $documents->latest(20),
                'panels' => $panels->resolve(),
                'missingTables' => DocumentTable::missingTables(),
                'workspaces' => DocumentTable::safeGet('document_core_workspaces', fn ($query) => $query->orderBy('name')),
                'categories' => DocumentTable::safeGet('document_core_categories', fn ($query) => $query->orderBy('name')),
            ]);
        } catch (\Throwable $e) {
            DocumentLogger::exception($e, ['controller' => __CLASS__]);

            return response()->view('documentmanager::diagnostics.crash', [
                'exception' => $e,
                'diagnosticsUrl' => route('document-manager.diagnostics.index'),
            ], 500);
        }
    }
}
