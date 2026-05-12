<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Modules\CatalogManager\Services\StorePageSpeedInsightsService;
use Modules\CatalogManager\Support\CatalogTable;

class multiStoreController extends Controller{

    protected bool $hasPageActions = false;

    public function index(StorePageSpeedInsightsService $pageSpeed){
        $this->addInternalToolAccess('catalog-manager.dashboard', 'Catalog Manager', 'fa-solid fa-boxes-stacked');
        $this->addInternalToolAccess('document-manager.dashboard', 'Document Manager', 'fa-solid fa-folder-tree');
        $this->addInternalToolAccess('erp.dashboard', 'ERP', 'fa-solid fa-building-columns');

        $stores = CatalogTable::exists('catalog_stores')
            ? DB::table('catalog_stores')->where('active', true)->orderBy('name')->get()
            : collect();

        return $this->view('areas/multiStore/index', [
            'stores' => $stores,
            'pageSpeedMetrics' => $pageSpeed->todayMetricsForStores($stores),
        ]);
    }

    private function addInternalToolAccess(string $routeName, string $label, string $icon): void
    {
        if (Route::has($routeName)) {
            $this->addAccess(route($routeName), $label, $icon);
        }
    }
    
}
