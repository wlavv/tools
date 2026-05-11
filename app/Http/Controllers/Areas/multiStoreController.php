<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Modules\CatalogManager\Services\StorePageSpeedInsightsService;
use Modules\CatalogManager\Support\CatalogTable;

class multiStoreController extends Controller{

    protected bool $hasPageActions = false;

    public function index(StorePageSpeedInsightsService $pageSpeed){
        $this->addAccess( route('catalog-manager.dashboard'), 'Catalog Manager', 'fa-solid fa-boxes-stacked' );

        $stores = CatalogTable::exists('catalog_stores')
            ? DB::table('catalog_stores')->where('active', true)->orderBy('name')->get()
            : collect();

        foreach ($stores as $store) {
            $this->addAccess(
                $this->storeUrl($store),
                $store->name,
                $this->storeIcon($store)
            );
        }

        return $this->view('areas/multiStore/index', [
            'stores' => $stores,
            'pageSpeedMetrics' => $pageSpeed->todayMetricsForStores($stores),
        ]);
    }

    private function storeIcon(object $store): string
    {
        $settings = json_decode((string) ($store->settings ?? ''), true);

        return is_array($settings) && !empty($settings['icon'])
            ? (string) $settings['icon']
            : 'fa-solid fa-store';
    }

    private function storeUrl(object $store): string
    {
        $domain = trim((string) ($store->domain ?? ''));

        if ($domain === '') {
            return '#';
        }

        return str_starts_with($domain, 'http://') || str_starts_with($domain, 'https://')
            ? $domain
            : 'https://' . $domain;
    }
    
}
