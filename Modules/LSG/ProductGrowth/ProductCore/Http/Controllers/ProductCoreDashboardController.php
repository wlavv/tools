<?php

namespace Modules\LSG\ProductGrowth\ProductCore\Http\Controllers;

use Modules\LSG\ProductGrowth\ProductCore\Models\Product;
use Modules\LSG\ProductGrowth\ProductCore\Models\StoreProduct;

class ProductCoreDashboardController extends BaseProductCoreController
{
    public function __invoke()
    {
        $this->prepareProductCorePage('Product Growth', [
            ['label' => 'Product Growth', 'url' => null],
        ]);
        $this->addNewProductAction();

        $stats = [
            'products' => Product::count(),
            'store_products' => StoreProduct::count(),
            'ready_to_sync' => StoreProduct::where('sync_status', 'ready_to_sync')->count(),
            'needs_resync' => StoreProduct::where('sync_status', 'needs_resync')->count(),
            'sync_failed' => StoreProduct::where('sync_status', 'sync_failed')->count(),
        ];

        $recentProducts = Product::with(['brand','supplier','storeProducts.store'])->latest()->limit(10)->get();
        $productsByStatus = Product::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return $this->view('product-core::dashboard.index', compact('stats', 'recentProducts', 'productsByStatus'));
    }
}
