<?php

namespace Modules\CatalogManager\Http\Controllers;

use Illuminate\Support\Facades\Route;
use Modules\CatalogManager\Support\CatalogLogger;
use Modules\CatalogManager\Support\CatalogTable;

class DiagnosticsController extends BaseCatalogController
{
    public function index()
    {
        $tables = CatalogTable::expectedTables();

        $tableStatus = [];

        foreach ($tables as $table) {
            try {
                $tableStatus[$table] = CatalogTable::exists($table);
            } catch (\Throwable $e) {
                $tableStatus[$table] = false;
                CatalogLogger::exception($e, ['table' => $table]);
            }
        }

        $routes = [
            'catalog-manager.dashboard',
            'catalog-manager.manufacturers.index',
            'catalog-manager.suppliers.index',
            'catalog-manager.categories.index',
            'catalog-manager.sync.index',
            'catalog-manager.diagnostics.index',
        ];

        $routeStatus = [];

        foreach ($routes as $route) {
            $routeStatus[$route] = Route::has($route);
        }

        $logPath = storage_path('logs/catalog-manager.log');
        $logTail = is_file($logPath)
            ? implode('', array_slice(file($logPath), -40))
            : 'Sem ficheiro de log próprio ainda.';

        return response()->view('catalogmanager::diagnostics.index', [
            'tableStatus' => $tableStatus,
            'routeStatus' => $routeStatus,
            'logPath' => $logPath,
            'logTail' => $logTail,
            'phpVersion' => PHP_VERSION,
            'laravelVersion' => app()->version(),
            'moduleVersion' => config('catalogmanager.version'),
        ]);
    }
}
