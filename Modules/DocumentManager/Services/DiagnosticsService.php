<?php

namespace Modules\DocumentManager\Services;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Modules\DocumentManager\Support\DocumentTable;

class DiagnosticsService
{
    public function __construct(
        private StorageService $storage,
        private OcrService $ocr,
        private AiService $ai
    ) {
    }

    public function report(): array
    {
        $tableStatus = [];

        foreach (DocumentTable::expectedTables() as $table) {
            $tableStatus[$table] = DocumentTable::exists($table);
        }

        $routes = [
            'document-manager.dashboard',
            'document-manager.documents.index',
            'document-manager.documents.create',
            'document-manager.documents.preview',
            'document-manager.documents.file',
            'document-manager.documents.download',
            'document-manager.workspaces.index',
            'document-manager.folders.index',
            'document-manager.categories.index',
            'document-manager.tags.index',
            'document-manager.workflow.index',
            'document-manager.ai.index',
            'document-manager.search.index',
            'document-manager.diagnostics.index',
        ];

        $routeStatus = [];

        foreach ($routes as $route) {
            $routeStatus[$route] = Route::has($route);
        }

        $logPath = storage_path('logs/document-manager.log');
        $logTail = is_file($logPath)
            ? implode('', array_slice(file($logPath), -((int) config('documentmanager.limits.diagnostics_log_lines', 60))))
            : 'Sem ficheiro de log proprio ainda.';

        return [
            'tables' => $tableStatus,
            'routes' => $routeStatus,
            'storage' => $this->storage->health(),
            'ocr' => $this->ocr->health(),
            'ai' => $this->ai->health(),
            'queue_names' => config('documentmanager.queues', []),
            'has_failed_jobs_table' => Schema::hasTable('failed_jobs'),
            'missing_tables' => DocumentTable::missingTables(),
            'log_path' => $logPath,
            'log_tail' => $logTail,
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'module_version' => config('documentmanager.version'),
        ];
    }
}
