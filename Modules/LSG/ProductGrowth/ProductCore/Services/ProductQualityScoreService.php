<?php

namespace Modules\LSG\ProductGrowth\ProductCore\Services;

use Modules\LSG\ProductGrowth\ProductCore\Models\Product;

class ProductQualityScoreService
{
    public function calculate(Product $product): float
    {
        $score = 0;
        $metadata = $product->metadata ?? [];
        $checks = [
            !empty($product->name), !empty($product->reference) || !empty($product->ean),
            !empty(data_get($metadata, 'product_growth.manufacturer_id')) || !empty($product->brand_id),
            !empty(data_get($metadata, 'product_growth.supplier_id')) || !empty($product->supplier_id),
            !empty($product->description), !empty($product->base_price),
            $product->assets()->exists(), $product->storeProducts()->exists(),
        ];
        foreach ($checks as $check) {
            if ($check) $score += 12.5;
        }
        return min(100, round($score, 2));
    }

    public function update(Product $product): Product
    {
        $product->data_quality_score = $this->calculate($product);
        $product->save();
        return $product;
    }
}
