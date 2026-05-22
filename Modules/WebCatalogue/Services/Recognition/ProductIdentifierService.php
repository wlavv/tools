<?php

namespace Modules\WebCatalogue\Services\Recognition;

use Illuminate\Support\Collection;
use Modules\WebCatalogue\Models\Product;
use Modules\WebCatalogue\Models\ProductIdentifier;
use Modules\WebCatalogue\Models\Store;

class ProductIdentifierService
{
    private const PRODUCT_SYNC_SOURCE = 'product_sync';

    public function syncProduct(Product $product): array
    {
        if (!$product->id || !$product->id_store) {
            return ['synced' => 0, 'deleted' => 0];
        }

        $identifiers = $this->identifiersForProduct($product);

        $deleted = ProductIdentifier::query()
            ->where('id_product', $product->id)
            ->where('source', self::PRODUCT_SYNC_SOURCE)
            ->delete();

        $synced = 0;
        foreach ($identifiers as $identifier) {
            ProductIdentifier::updateOrCreate(
                [
                    'id_store' => (int) $product->id_store,
                    'type' => $identifier['type'],
                    'normalized_value' => $identifier['normalized_value'],
                ],
                [
                    'id_product' => (int) $product->id,
                    'value' => $identifier['value'],
                    'source' => self::PRODUCT_SYNC_SOURCE,
                    'metadata' => $identifier['metadata'] ?? null,
                ]
            );
            $synced++;
        }

        return ['synced' => $synced, 'deleted' => $deleted];
    }

    public function syncStore(Store $store): array
    {
        $processed = 0;
        $synced = 0;
        $deleted = 0;

        Product::query()
            ->where('id_store', $store->id)
            ->orderBy('id')
            ->chunkById(250, function ($products) use (&$processed, &$synced, &$deleted): void {
                foreach ($products as $product) {
                    $result = $this->syncProduct($product);
                    $processed++;
                    $synced += (int) ($result['synced'] ?? 0);
                    $deleted += (int) ($result['deleted'] ?? 0);
                }
            });

        return [
            'processed' => $processed,
            'synced' => $synced,
            'deleted' => $deleted,
        ];
    }

    public function syncAll(): array
    {
        $processed = 0;
        $synced = 0;
        $deleted = 0;

        Product::query()
            ->orderBy('id')
            ->chunkById(250, function ($products) use (&$processed, &$synced, &$deleted): void {
                foreach ($products as $product) {
                    $result = $this->syncProduct($product);
                    $processed++;
                    $synced += (int) ($result['synced'] ?? 0);
                    $deleted += (int) ($result['deleted'] ?? 0);
                }
            });

        return [
            'processed' => $processed,
            'synced' => $synced,
            'deleted' => $deleted,
        ];
    }

    public function matchDetectedIdentifiers(array $detectedIdentifiers, ?Store $store = null): ?Product
    {
        $candidates = $this->candidateValuesFromDetectedIdentifiers($detectedIdentifiers);
        if ($candidates->isEmpty()) {
            return null;
        }

        return ProductIdentifier::query()
            ->with(['product.store', 'product.mainImageResource'])
            ->whereIn('normalized_value', $candidates->pluck('normalized')->all())
            ->when($store, fn ($query) => $query->where('id_store', $store->id))
            ->when(!$store, fn ($query) => $query->whereHas('product.store', fn ($storeQuery) => $storeQuery->where('status', 'active')))
            ->orderByRaw("CASE WHEN type IN ('ean13', 'ean8', 'upc', 'gtin', 'sku', 'reference') THEN 0 ELSE 1 END")
            ->orderByDesc('id')
            ->get()
            ->map(fn (ProductIdentifier $identifier) => $identifier->product)
            ->filter(fn ($product) => $product instanceof Product)
            ->first();
    }

    public function candidateValuesFromDetectedIdentifiers(array $detectedIdentifiers): Collection
    {
        $items = collect();

        foreach ($detectedIdentifiers as $identifier) {
            if (!is_array($identifier)) {
                continue;
            }

            $format = $this->typeFromFormat((string) ($identifier['format'] ?? 'unknown'));
            $value = trim((string) ($identifier['value'] ?? $identifier['rawValue'] ?? $identifier['text'] ?? ''));
            if ($value === '') {
                continue;
            }

            foreach ($this->expandedValues($value) as $expandedValue) {
                $normalized = $this->normalizeValue($expandedValue, $format);
                if ($normalized !== null) {
                    $items->push([
                        'type' => $format,
                        'value' => $expandedValue,
                        'normalized' => $normalized,
                    ]);
                }
            }
        }

        return $items
            ->unique('normalized')
            ->values();
    }

    public function normalizeValue(string $value, string $type = 'code'): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $type = strtolower(trim($type));
        if (in_array($type, ['ean13', 'ean8', 'upc', 'gtin', 'barcode'], true)) {
            $numeric = preg_replace('/\D+/', '', $value);
            return $numeric !== '' ? $numeric : null;
        }

        if (in_array($type, ['qr_url', 'url'], true) || filter_var($value, FILTER_VALIDATE_URL)) {
            return mb_strtolower(rtrim($value, "/ \t\n\r\0\x0B"));
        }

        $normalized = preg_replace('/[\s\-_]+/', '', mb_strtoupper($value));
        return $normalized !== '' ? $normalized : null;
    }

    public function identifiersForProduct(Product $product): array
    {
        $identifiers = [];

        foreach ([
            'reference' => $product->reference ?? null,
            'sku' => $product->sku ?? null,
            'ean13' => $product->ean13 ?? null,
            'external_id' => $product->external_id ?? null,
        ] as $type => $value) {
            $this->appendIdentifier($identifiers, $type, $value, ['source_field' => $type]);
        }

        foreach ($this->metadataIdentifierValues($product->metadata ?: []) as $metadataIdentifier) {
            $this->appendIdentifier(
                $identifiers,
                $metadataIdentifier['type'],
                $metadataIdentifier['value'],
                ['source_field' => $metadataIdentifier['source_field']]
            );
        }

        return array_values($identifiers);
    }

    private function appendIdentifier(array &$identifiers, string $type, mixed $value, array $metadata = []): void
    {
        if (!is_scalar($value)) {
            return;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return;
        }

        $type = $this->typeFromFormat($type);
        $normalized = $this->normalizeValue($value, $type);
        if ($normalized === null || mb_strlen($normalized) < 3) {
            return;
        }

        $key = $type . ':' . $normalized;
        $identifiers[$key] = [
            'type' => $type,
            'value' => mb_substr($value, 0, 500),
            'normalized_value' => mb_substr($normalized, 0, 190),
            'metadata' => $metadata,
        ];
    }

    private function metadataIdentifierValues(array $metadata): array
    {
        $acceptedKeys = [
            'barcode' => 'barcode',
            'barcodes' => 'barcode',
            'ean' => 'ean13',
            'ean13' => 'ean13',
            'ean_13' => 'ean13',
            'gtin' => 'gtin',
            'upc' => 'upc',
            'sku' => 'sku',
            'ref' => 'reference',
            'reference' => 'reference',
            'manufacturer_code' => 'manufacturer_code',
            'manufacturer_reference' => 'manufacturer_code',
            'mpn' => 'manufacturer_code',
            'part_number' => 'manufacturer_code',
            'collector' => 'collector_number',
            'collector_number' => 'collector_number',
            'collector_no' => 'collector_number',
            'card_number' => 'collector_number',
            'number' => 'collector_number',
            'set' => 'set_code',
            'set_code' => 'set_code',
            'expansion' => 'set_code',
            'expansion_code' => 'set_code',
            'scryfall_id' => 'external_id',
            'oracle_id' => 'external_id',
            'tcgplayer_id' => 'external_id',
            'card_id' => 'external_id',
            'qr' => 'qr',
            'qr_code' => 'qr',
            'qr_url' => 'qr_url',
            'url' => 'url',
        ];

        $found = [];
        $walk = function (array $items, string $prefix = '') use (&$walk, &$found, $acceptedKeys): void {
            foreach ($items as $key => $value) {
                $keyString = is_string($key) ? strtolower(trim($key)) : '';
                $path = $prefix === '' ? $keyString : $prefix . '.' . $keyString;
                $type = $acceptedKeys[$keyString] ?? null;

                if ($type && is_scalar($value)) {
                    $found[] = ['type' => $type, 'value' => (string) $value, 'source_field' => $path];
                    continue;
                }

                if ($type && is_array($value)) {
                    foreach ($value as $nestedValue) {
                        if (is_scalar($nestedValue)) {
                            $found[] = ['type' => $type, 'value' => (string) $nestedValue, 'source_field' => $path];
                        }
                    }
                }

                if (is_array($value) && count($value) <= 40) {
                    $walk($value, $path);
                }
            }
        };

        $walk($metadata);

        return $found;
    }

    private function expandedValues(string $value): array
    {
        $values = [$value, trim($value, " \t\n\r\0\x0B/#?")];
        $numeric = preg_replace('/\D+/', '', $value);
        if ($numeric !== '') {
            $values[] = $numeric;
        }

        $urlParts = parse_url($value);
        if (is_array($urlParts)) {
            if (!empty($urlParts['path'])) {
                foreach (array_filter(explode('/', (string) $urlParts['path'])) as $segment) {
                    $values[] = trim(urldecode($segment));
                }
            }

            if (!empty($urlParts['query'])) {
                parse_str((string) $urlParts['query'], $query);
                foreach (['sku', 'ref', 'reference', 'ean', 'ean13', 'barcode', 'code', 'id', 'mpn', 'collector', 'collector_number', 'card_number', 'set', 'set_code'] as $key) {
                    if (!empty($query[$key]) && is_scalar($query[$key])) {
                        $values[] = trim((string) $query[$key]);
                    }
                }
            }
        }

        return array_values(array_unique(array_filter($values, fn ($item) => trim((string) $item) !== '')));
    }

    private function typeFromFormat(string $format): string
    {
        $format = strtolower(trim(str_replace('-', '_', $format)));

        return match ($format) {
            'ean_13', 'ean13' => 'ean13',
            'ean_8', 'ean8' => 'ean8',
            'upc_a', 'upc_e', 'upc' => 'upc',
            'qr_code', 'qrcode' => 'qr',
            'code_128', 'code_39', 'code_93', 'itf', 'pdf417', 'aztec', 'data_matrix' => 'barcode',
            'externalid' => 'external_id',
            'mpn', 'manufacturer_reference', 'manufacturer_code', 'part_number' => 'manufacturer_code',
            'collector', 'collector_no', 'collector_number', 'card_number' => 'collector_number',
            'set', 'set_code', 'expansion', 'expansion_code' => 'set_code',
            default => $format ?: 'code',
        };
    }
}
