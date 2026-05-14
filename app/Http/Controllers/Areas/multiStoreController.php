<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Modules\CatalogManager\Services\StorePageSpeedInsightsService;
use Modules\CatalogManager\Support\CatalogTable;

class multiStoreController extends Controller{

    protected bool $hasPageActions = false;

    public function index(StorePageSpeedInsightsService $pageSpeed){
        $this->addInternalToolAccess('catalog-manager.dashboard', 'Catalog Manager', 'fa-solid fa-boxes-stacked');
        $this->addInternalToolAccess('document-manager.dashboard', 'Document Manager', 'fa-solid fa-folder-tree');
        $this->addInternalToolAccess('erp.dashboard', 'ERP', 'fa-solid fa-building-columns');

        $stores = CatalogTable::exists('catalog_stores')
            ? DB::table('catalog_stores')
                ->where('active', true)
                ->where(function ($query) {
                    $query->where('record_type', 'store')->orWhereNull('record_type');
                })
                ->orderBy('name')
                ->get()
            : collect();
        
        return $this->view('areas/multiStore/index', [
            'stores' => $stores,
            'pageSpeedMetrics' => $pageSpeed->todayMetricsForStores($stores, 'mobile'),
            'pageSpeedMetricsByStrategy' => [
                'mobile' => $pageSpeed->todayMetricsForStores($stores, 'mobile'),
                'desktop' => $pageSpeed->todayMetricsForStores($stores, 'desktop'),
            ],
        ]);
    }

    private function addInternalToolAccess(string $routeName, string $label, string $icon): void
    {
        $this->addRouteAccess($routeName, $label, $icon);
    }
    
}
