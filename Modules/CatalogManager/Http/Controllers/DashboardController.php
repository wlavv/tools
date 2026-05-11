<?php

namespace Modules\CatalogManager\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\CatalogManager\Services\ActionPanels\ActionPanelManager;
use Modules\CatalogManager\Services\IssuePanels\IssuePanelManager;
use Modules\CatalogManager\Support\CatalogLogger;
use Modules\CatalogManager\Support\CatalogTable;

class DashboardController extends Controller
{
    public function index(IssuePanelManager $issuePanelManager, ActionPanelManager $actionPanelManager)
    {
        try {
            $stats = [
                'products' => CatalogTable::count('catalog_core_products'),
                'manufacturers' => CatalogTable::count('catalog_core_manufacturers'),
                'suppliers' => CatalogTable::count('catalog_core_suppliers'),
                'stores' => CatalogTable::count('catalog_stores'),
                'categories' => CatalogTable::count('catalog_store_categories'),
                'sync_pending' => CatalogTable::count('catalog_prestashop_sync_queue', function ($query) {
                    $query->where('status', 'pending');
                }),
            ];

            $issuePanels = $issuePanelManager->resolve();
            $actionPanels = $actionPanelManager->resolve();

            $latestProducts = CatalogTable::exists('catalog_core_products')
                ? DB::table('catalog_core_products')
                    ->select('id', 'name', 'reference', 'status', 'created_at')
                    ->orderByDesc('id')
                    ->limit(8)
                    ->get()
                : collect();

            return view('catalogmanager::dashboard.index', compact(
                'stats',
                'issuePanels',
                'actionPanels',
                'latestProducts'
            ));
        } catch (\Throwable $e) {
            CatalogLogger::exception($e, ['controller' => __CLASS__, 'method' => __METHOD__]);

            return response()->view('catalogmanager::diagnostics.crash', [
                'exception' => $e,
                'diagnosticsUrl' => route('catalog-manager.diagnostics.index'),
            ], 500);
        }
    }
}
