<?php

namespace Modules\DatabaseExplorer\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\DatabaseExplorer\Services\DatabaseExplorerService;
use Modules\DatabaseExplorer\Services\DatabaseSnapshotService;
use Modules\DatabaseExplorer\Support\Identifier;

class DatabaseExplorerController extends Controller
{
    public function __construct(
        protected DatabaseExplorerService $service,
        protected DatabaseSnapshotService $snapshotService
    ) {
        parent::__construct();
        $this->pageTitle = $this->resolvePageTitle();
    }

    public function index(Request $request): View
    {
        $filters = [
            'schemaName' => $request->query('schema'),
            'search' => $request->query('q'),
            'health' => $request->query('health'),
        ];

        $overview = $this->service->overview();
        $schemas = $this->service->schemas();
        $tables = $this->service->tables($filters);

        return $this->view('database-explorer::Index', [
            'overview' => $overview,
            'schemas' => $schemas,
            'tables' => $tables,
            'filters' => $filters,
            'formatBytes' => fn (?int $bytes): string => $this->service->formatBytes($bytes),
        ]);
    }

    public function show(string $schemaName, string $tableName): View
    {
        Identifier::assertSafe($schemaName, 'schema');
        Identifier::assertSafe($tableName, 'table');

        $table = $this->service->table($schemaName, $tableName);

        return $this->view('database-explorer::pages.show', [
            'table' => $table,
            'formatBytes' => fn (?int $bytes): string => $this->service->formatBytes($bytes),
        ]);
    }

    public function health(Request $request): View
    {
        $filters = [
            'schemaName' => $request->query('schema'),
            'search' => $request->query('q'),
            'health' => $request->query('health'),
            'severity' => $request->query('severity'),
        ];

        return $this->view('database-explorer::pages.health', [
            'overview' => $this->service->overview(),
            'schemas' => $this->service->schemas(),
            'findings' => $this->service->healthFindings($filters),
            'filters' => $filters,
            'formatBytes' => fn (?int $bytes): string => $this->service->formatBytes($bytes),
        ]);
    }

    public function snapshots(Request $request): View
    {
        $limit = min(max((int) $request->query('limit', 50), 1), 200);

        return $this->view('database-explorer::pages.snapshots', [
            'snapshots' => $this->snapshotService->list($limit),
            'limit' => $limit,
            'formatBytes' => fn (?int $bytes): string => $this->service->formatBytes($bytes),
        ]);
    }

    public function collectSnapshot(): RedirectResponse
    {
        $snapshot = $this->snapshotService->collect();

        return redirect()
            ->route('database_explorer.snapshots')
            ->with('success', 'Snapshot #' . $snapshot['id'] . ' collected successfully.');
    }
}
