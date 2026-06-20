<?php

namespace Modules\LSG\ProductGrowth\ProductCore\Services;

use Modules\LSG\ProductGrowth\ProductCore\Models\Product;

class ProductSkuGenerator
{
    public function generate(): string
    {
        $prefix = 'LSG-' . now()->format('Y') . '-';
        $last = Product::where('internal_sku', 'like', $prefix . '%')->orderByDesc('id')->value('internal_sku');
        $next = 1;
        if ($last && preg_match('/(\d+)$/', $last, $m)) {
            $next = ((int) $m[1]) + 1;
        }
        return $prefix . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }
}
