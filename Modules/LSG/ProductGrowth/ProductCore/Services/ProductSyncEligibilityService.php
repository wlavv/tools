<?php

namespace Modules\LSG\ProductGrowth\ProductCore\Services;

use Modules\LSG\ProductGrowth\ProductCore\Models\StoreProduct;

class ProductSyncEligibilityService
{
    public function canSync(StoreProduct $storeProduct): bool
    {
        $product = $storeProduct->product;
        return $product
            && in_array($product->status, ['approved','ready_to_sync','needs_resync'], true)
            && $storeProduct->active_for_sale
            && $storeProduct->sync_to_prestashop
            && !empty($storeProduct->name ?: $product->name)
            && !empty($storeProduct->sale_price ?: $product->base_price);
    }

    public function markReady(StoreProduct $storeProduct): StoreProduct
    {
        if ($this->canSync($storeProduct)) {
            $storeProduct->sync_status = 'ready_to_sync';
            $storeProduct->save();
        }
        return $storeProduct;
    }
}
