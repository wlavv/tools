<?php

namespace Modules\WebCatalogue\Console;

use Illuminate\Console\Command;
use Modules\WebCatalogue\Models\Product;
use Modules\WebCatalogue\Models\Store;
use Modules\WebCatalogue\Services\Recognition\VisualMarkerService;

class RebuildRecognitionMarkersCommand extends Command
{
    protected $signature = 'webcatalogue:recognition-rebuild-markers
        {--store= : Rebuild only one store id}
        {--product= : Rebuild only one product id}';

    protected $description = 'Rebuild OpenCV visual markers for WebCatalogue product images.';

    public function handle(VisualMarkerService $markers): int
    {
        $productId = (int) $this->option('product');
        if ($productId > 0) {
            $product = Product::find($productId);
            if (!$product) {
                $this->error('Product not found: ' . $productId);
                return self::FAILURE;
            }

            $result = $markers->rebuildProduct($product);
            $this->printResult('Product #' . $product->id . ' - ' . $product->reference, $result);
            return self::SUCCESS;
        }

        $storeId = (int) $this->option('store');
        if ($storeId > 0) {
            $store = Store::find($storeId);
            if (!$store) {
                $this->error('Store not found: ' . $storeId);
                return self::FAILURE;
            }

            $result = $markers->rebuildStore($store);
            $this->printResult('Store #' . $store->id . ' - ' . $store->name, $result);
            return self::SUCCESS;
        }

        $this->error('Use --store=ID or --product=ID.');
        return self::FAILURE;
    }

    private function printResult(string $scope, array $result): void
    {
        $this->info('Visual markers rebuilt for ' . $scope);
        $this->line('Processed: ' . $result['processed']);
        $this->line('Updated: ' . $result['updated']);
        $this->line('Failed: ' . $result['failed']);
        $this->line('Algorithm: ' . $result['algorithm']);
    }
}
