<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Modules\LSG\SiteManager\Models\Site;

class multiStoreController extends Controller{

    protected bool $hasPageActions = false;

    public function index(){
        $this->addInternalToolAccess('product_growth.product_core.dashboard', 'Product Growth', 'fa-solid fa-diagram-project');
        $this->addInternalToolAccess('catalog-manager.manufacturers.index', 'Manufacturers', 'fa-solid fa-industry');
        $this->addInternalToolAccess('catalog-manager.suppliers.index', 'Suppliers', 'fa-solid fa-truck-field');
        $this->addInternalToolAccess('catalog-manager.categories.index', 'Categorias', 'fa-solid fa-layer-group');
        $this->addInternalToolAccess('catalog-manager.currencies.index', 'Currencies', 'fa-solid fa-coins');
        $this->addInternalToolAccess('document-manager.dashboard', 'Document Manager', 'fa-solid fa-folder-tree');
        $this->addInternalToolAccess('erp.dashboard', 'ERP', 'fa-solid fa-building-columns');

        $stores = Schema::hasTable('lsg_sites')
            ? Site::query()
                ->where('site_type', 'store')
                ->where('status', 'active')
                ->orderBy('name')
                ->get()
            : collect();
        
        return $this->view('areas/multiStore/index', [
            'stores' => $stores,
            'pageSpeedMetrics' => collect(),
            'pageSpeedMetricsByStrategy' => [
                'mobile' => collect(),
                'desktop' => collect(),
            ],
        ]);
    }

    private function addInternalToolAccess(string $routeName, string $label, string $icon): void
    {
        if (!Route::has($routeName)) {
            return;
        }

        $this->addRouteAccess($routeName, $label, $icon);
    }
    
}
