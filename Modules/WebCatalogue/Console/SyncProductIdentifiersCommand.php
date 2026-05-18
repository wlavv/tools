<?php

namespace Modules\WebCatalogue\Console;

use Illuminate\Console\Command;
use Modules\WebCatalogue\Models\Product;
use Modules\WebCatalogue\Models\Store;
use Modules\WebCatalogue\Services\Recognition\ProductIdentifierService;

class SyncProductIdentifiersCommand extends Command
{
    protected $signature = 'webcatalogue:identifiers-sync
        {--store= : Sync only one store id}
        {--product= : Sync only one product id}';

    protected $description = 'Sync normalized WebCatalogue product identifiers for fast recognition lookup.';

    public function handle(ProductIdentifierService $identifiers): int
    {
        $productId = (int) $this->option('product');
        if ($productId > 0) {
            $product = Product::query()->find($productId);
            if (!$product) {
                $this->error('Product not found: ' . $productId);
                return self::FAILURE;
            }

            $result = $identifiers->syncProduct($product);
            $this->info('Identifiers synced for product #' . $product->id . ' - ' . $product->reference);
            $this->line('Synced: ' . $result['synced']);
            $this->line('Deleted old: ' . $result['deleted']);

            return self::SUCCESS;
        }

        $storeId = (int) $this->option('store');
        if ($storeId > 0) {
            $store = Store::query()->find($storeId);
            if (!$store) {
                $this->error('Store not found: ' . $storeId);
                return self::FAILURE;
            }

            $result = $identifiers->syncStore($store);
            $this->printResult('store #' . $store->id . ' - ' . $store->name, $result);

            return self::SUCCESS;
        }

        $result = $identifiers->syncAll();
        $this->printResult('all products', $result);

        return self::SUCCESS;
    }

    private function printResult(string $scope, array $result): void
    {
        $this->info('Identifiers synced for ' . $scope);
        $this->line('Products processed: ' . $result['processed']);
        $this->line('Identifiers synced: ' . $result['synced']);
        $this->line('Old generated identifiers deleted: ' . $result['deleted']);
    }
}
