<?php

namespace Modules\WebCatalogue\Http\Controllers\Imports;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Modules\WebCatalogue\Models\Catalogue;
use Modules\WebCatalogue\Models\CatalogueProduct;
use Modules\WebCatalogue\Models\ImportBatch;
use Modules\WebCatalogue\Models\ImportBatchRow;
use Modules\WebCatalogue\Models\Product;
use Modules\WebCatalogue\Models\ProductPrice;
use Modules\WebCatalogue\Models\Promotion;
use Modules\WebCatalogue\Models\PromotionProduct;
use Modules\WebCatalogue\Models\Resource;
use Modules\WebCatalogue\Models\Setting;
use Modules\WebCatalogue\Models\Store;
use Modules\WebCatalogue\Models\StoreEnvironment;
use Modules\WebCatalogue\Models\StoreTheme;

class ImportCenterController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = $this->resolvePageTitle();
    }

    public function index(): View
    {
        return $this->view('webcatalogue::imports.index', [
            'templates' => $this->templates(),
            'batches' => ImportBatch::query()->latest('id')->limit(20)->get(),
            'stores' => Store::query()->orderBy('name')->get(),
        ]);
    }

    public function show(string $type): View
    {
        $template = $this->templateOrFail($type);

        return $this->view('webcatalogue::imports.show', [
            'type' => $type,
            'template' => $template,
            'stores' => Store::query()->orderBy('name')->get(),
            'batches' => ImportBatch::query()->where('source_type', 'csv:' . $type)->latest('id')->limit(20)->get(),
        ]);
    }

    public function template(string $type): Response
    {
        $template = $this->templateOrFail($type);
        $columns = Arr::get($template, 'columns', []);
        $sample = Arr::get($template, 'sample', array_fill(0, count($columns), ''));

        $handle = fopen('php://temp', 'r+');
        $delimiter = (string) config('webcatalogue.csv_delimiter', ';');
        $delimiter = in_array($delimiter, [',', ';', "\t", '|'], true) ? $delimiter : ';';

        fputcsv($handle, $columns, $delimiter);
        fputcsv($handle, $sample, $delimiter);
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="webcatalogue_' . Str::slug($type) . '_template.csv"',
        ]);
    }

    public function upload(Request $request, string $type): RedirectResponse
    {
        $template = $this->templateOrFail($type);

        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt'],
            'id_store' => ['nullable', 'integer'],
        ]);

        $path = $request->file('csv_file')->store(
            'webcatalogue/temp/imports/' . $type,
            config('webcatalogue.storage_disk', 'public')
        );

        $batch = ImportBatch::create([
            'id_store' => $request->input('id_store'),
            'source_type' => 'csv:' . $type,
            'filename' => $request->file('csv_file')->getClientOriginalName(),
            'file_path' => $path,
            'status' => 'preview_ready',
            'metadata' => [
                'import_type' => $type,
                'expected_columns' => Arr::get($template, 'columns', []),
            ],
        ]);

        $this->parseBatchRows($batch, $template);

        return redirect()
            ->route('webcatalogue.imports.preview', $batch)
            ->with('success', 'CSV uploaded. Validate the preview before importing.');
    }

    public function preview(ImportBatch $batch): View
    {
        $type = $this->batchType($batch);
        $template = $this->templateOrFail($type);

        return $this->view('webcatalogue::imports.preview', [
            'batch' => $batch,
            'type' => $type,
            'template' => $template,
            'rows' => $batch->rows()->orderBy('row_number')->get(),
        ]);
    }

    public function confirm(ImportBatch $batch): RedirectResponse
    {
        $type = $this->batchType($batch);
        $template = $this->templateOrFail($type);

        $created = 0;
        $updated = 0;
        $failed = 0;

        foreach ($batch->rows()->orderBy('row_number')->get() as $row) {
            if ($row->status === 'invalid') {
                $failed++;
                continue;
            }

            try {
                $result = $this->importRow($type, $this->sanitizePayloadForJson($row->raw_payload ?? []), $batch);
                $row->update([
                    'status' => 'imported',
                    'id_store' => $result['id_store'] ?? $row->id_store,
                    'id_catalogue' => $result['id_catalogue'] ?? $row->id_catalogue,
                    'id_product' => $result['id_product'] ?? $row->id_product,
                    'message' => $result['message'] ?? 'Imported successfully.',
                ]);

                ($result['created'] ?? false) ? $created++ : $updated++;
            } catch (\Throwable $e) {
                $failed++;
                $row->update([
                    'status' => 'failed',
                    'message' => $e->getMessage(),
                ]);
            }
        }

        $batch->update([
            'status' => $failed > 0 ? 'imported_with_errors' : 'imported',
            'created_rows' => $created,
            'updated_rows' => $updated,
            'failed_rows' => $failed,
        ]);

        return redirect()
            ->route($this->redirectRouteForType($type))
            ->with('success', 'Import finished. Created: ' . $created . ' · Updated: ' . $updated . ' · Failed: ' . $failed);
    }

    protected function parseBatchRows(ImportBatch $batch, array $template): void
    {
        $disk = config('webcatalogue.storage_disk', 'public');
        $absolutePath = Storage::disk($disk)->path($batch->file_path);

        $this->normalizeCsvFileToUtf8($absolutePath);

        $handle = fopen($absolutePath, 'r');
        if (!$handle) {
            $batch->update(['status' => 'failed', 'failed_rows' => 1]);
            return;
        }

        $delimiter = $this->detectCsvDelimiter($absolutePath);

        $headers = $this->normalizeHeaders($this->sanitizeCsvRow(fgetcsv($handle, 0, $delimiter) ?: []));
        $expected = Arr::get($template, 'columns', []);
        $required = Arr::get($template, 'required', []);

        // If the user selected a store in the Import Center, store_code is optional in the CSV.
        if ($batch->id_store) {
            $required = array_values(array_filter($required, fn ($column) => $column !== 'store_code'));
        }
        $rowNumber = 1;
        $valid = 0;
        $invalid = 0;

        while (($values = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rowNumber++;
            $values = $this->sanitizeCsvRow($values);

            if ($this->isEmptyCsvRow($values)) {
                continue;
            }
            $payload = [];
            foreach ($headers ?: [] as $index => $header) {
                $header = trim((string) $header);
                if ($header === '') {
                    continue;
                }
                $payload[$header] = $values[$index] ?? null;
            }

            $missing = [];
            foreach ($required as $column) {
                if (!array_key_exists($column, $payload) || trim((string) $payload[$column]) === '') {
                    $missing[] = $column;
                }
            }

            $status = empty($missing) ? 'valid' : 'invalid';
            $message = empty($missing) ? 'Ready to import.' : 'Missing required columns: ' . implode(', ', $missing);

            $payload = $this->sanitizePayloadForJson($payload);

            ImportBatchRow::create([
                'id_batch' => $batch->id,
                'id_store' => $batch->id_store,
                'row_number' => $rowNumber,
                'reference' => $payload['reference'] ?? $payload['product_reference'] ?? $payload['code'] ?? null,
                'status' => $status,
                'message' => $message,
                'raw_payload' => $payload,
            ]);

            $status === 'valid' ? $valid++ : $invalid++;
        }

        fclose($handle);

        $batch->update([
            'total_rows' => $valid + $invalid,
            'failed_rows' => $invalid,
            'status' => $invalid > 0 ? 'preview_with_errors' : 'preview_ready',
        ]);
    }

    protected function importRow(string $type, array $data, ImportBatch $batch): array
    {
        return match ($type) {
            'stores' => $this->importStore($data),
            'catalogues' => $this->importCatalogue($data, $batch),
            'products' => $this->importProduct($data, $batch),
            'resources' => $this->importResource($data, $batch),
            'prices' => $this->importPrice($data, $batch),
            'promotions' => $this->importPromotion($data, $batch),
            'promotion_products' => $this->importPromotionProduct($data, $batch),
            'themes' => $this->importTheme($data, $batch),
            'environments' => $this->importEnvironment($data, $batch),
            'settings' => $this->importSetting($data, $batch),
            default => throw new \RuntimeException('Unsupported import type: ' . $type),
        };
    }

    protected function importStore(array $data): array
    {
        $values = $this->onlyFilled($data, ['name','slug','code','domain','status']);
        $values['slug'] = $values['slug'] ?? Str::slug($values['name'] ?? $values['code']);
        $values['status'] = $values['status'] ?? 'active';
        $values['metadata'] = $this->jsonValue($data['metadata'] ?? null);

        $item = Store::query()->where('code', $values['code'])->first();
        $created = !$item;
        $item = Store::updateOrCreate(['code' => $values['code']], $values);

        return ['created' => $created, 'id_store' => $item->id, 'message' => $created ? 'Store created.' : 'Store updated.'];
    }

    protected function importCatalogue(array $data, ImportBatch $batch): array
    {
        $store = $this->storeFrom($data, $batch);
        $values = $this->onlyFilled($data, ['name','slug','description','catalogue_type','visibility','price_mode','status']);
        $values['id_store'] = $store->id;
        $values['slug'] = $values['slug'] ?? Str::slug($values['name']);
        $values['show_prices'] = $this->boolValue($data['show_prices'] ?? 0);
        $values['show_promotions'] = $this->boolValue($data['show_promotions'] ?? 0);
        $values['metadata'] = $this->jsonValue($data['metadata'] ?? null);

        $item = Catalogue::query()->where('id_store', $store->id)->where('slug', $values['slug'])->first();
        $created = !$item;
        $item = Catalogue::updateOrCreate(['id_store' => $store->id, 'slug' => $values['slug']], $values);

        return ['created' => $created, 'id_store' => $store->id, 'id_catalogue' => $item->id, 'message' => $created ? 'Catalogue created.' : 'Catalogue updated.'];
    }

    protected function importProduct(array $data, ImportBatch $batch): array
    {
        $store = $this->storeFrom($data, $batch);
        $values = $this->onlyFilled($data, ['external_id','external_source','reference','sku','ean13','name','slug','short_description','description','brand','category','price','currency','stock','status']);
        $values['id_store'] = $store->id;
        $values['slug'] = $values['slug'] ?? Str::slug($values['name'] ?? $values['reference']);
        $values['metadata'] = $this->jsonValue($data['metadata'] ?? null);

        $item = Product::query()->where('id_store', $store->id)->where('reference', $values['reference'])->first();
        $created = !$item;
        $item = Product::updateOrCreate(['id_store' => $store->id, 'reference' => $values['reference']], $values);

        $catalogueId = null;
        if (!empty($data['catalogue_slug'])) {
            $catalogue = Catalogue::query()->where('id_store', $store->id)->where('slug', $data['catalogue_slug'])->first();
            if ($catalogue) {
                $catalogueId = $catalogue->id;
                CatalogueProduct::updateOrCreate(
                    ['id_store' => $store->id, 'id_catalogue' => $catalogue->id, 'id_product' => $item->id],
                    ['status' => 'active']
                );
            }
        }

        $resourceStats = $this->importProductResourcesFromRow($data, $store->id, $item->id, $catalogueId);
        $message = ($created ? 'Product created.' : 'Product updated.');
        if ($resourceStats['created'] > 0 || $resourceStats['skipped'] > 0 || $resourceStats['failed'] > 0) {
            $message .= ' Resources: ' . $resourceStats['created'] . ' created, ' . $resourceStats['skipped'] . ' skipped, ' . $resourceStats['failed'] . ' failed.';
        }

        return ['created' => $created, 'id_store' => $store->id, 'id_catalogue' => $catalogueId, 'id_product' => $item->id, 'message' => $message];
    }

    protected function importResource(array $data, ImportBatch $batch): array
    {
        $store = $this->storeFrom($data, $batch);
        $catalogue = $this->catalogueFrom($data, $store);
        $ownerType = strtolower(trim((string) ($data['resource_owner_type'] ?? '')));
        $productRequired = $ownerType === 'product'
            || !empty($data['product_reference'])
            || !empty($data['reference'])
            || !empty($data['product_sku'])
            || !empty($data['product_external_id']);

        $product = $this->productFrom($data, $store, $productRequired);

        $values = $this->onlyFilled($data, ['resource_owner_type','resource_owner_key','resource_type','title','description','source_type','source_url','sort_order','status']);
        $values['id_store'] = $store->id;
        $values['id_catalogue'] = $catalogue?->id;
        $values['id_product'] = $product?->id;
        $values['resource_owner_type'] = $this->resolvedResourceOwnerType($values['resource_owner_type'] ?? null, $product, $catalogue);
        $values['resource_owner_id'] = $this->resolvedResourceOwnerId($values['resource_owner_type'], $store, $product, $catalogue);
        $values['is_main'] = $this->boolValue($data['is_main'] ?? 0);
        $values['metadata'] = $this->jsonValue($data['metadata'] ?? null);

        $sourceUrls = $this->splitUrls($values['source_url'] ?? null);
        if (empty($sourceUrls)) {
            throw new \RuntimeException('Resource source_url missing.');
        }

        $createdCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $failedCount = 0;
        $messages = [];
        $sortOrder = (int) ($values['sort_order'] ?? 0);

        foreach ($sourceUrls as $index => $sourceUrl) {
            $rowValues = $values;
            $rowValues['source_url'] = $sourceUrl;
            $rowSortOrder = $sortOrder > 0 ? $sortOrder + $index : $index + 1;

            if ($this->shouldDownloadResource($rowValues['resource_type'] ?? 'download', $rowValues['source_type'] ?? 'external_url')) {
                $downloaded = $this->downloadResourceFromUrl(
                    $sourceUrl,
                    $store->id,
                    $product?->id,
                    $catalogue?->id,
                    $rowValues['resource_type'] ?? 'download',
                    $rowValues['title'] ?? null,
                    (bool) ($rowValues['is_main'] ?? false),
                    $rowSortOrder,
                    $rowValues['description'] ?? null,
                    $rowValues['metadata'] ?? null
                );

                if (($downloaded['status'] ?? null) === 'created') {
                    $createdCount++;
                } elseif (($downloaded['status'] ?? null) === 'skipped') {
                    $skippedCount++;
                } else {
                    $failedCount++;
                }

                $messages[] = $downloaded['message'] ?? 'Resource processed.';
                continue;
            }

            $key = [
                'id_store' => $store->id,
                'id_product' => $product?->id,
                'source_url' => $sourceUrl,
            ];

            $existing = Resource::query()
                ->where('id_store', $store->id)
                ->where('source_url', $sourceUrl)
                ->when($product?->id, fn ($query) => $query->where('id_product', $product->id))
                ->first();

            $created = !$existing;
            Resource::updateOrCreate($key, array_merge($rowValues, ['sort_order' => $rowSortOrder]));
            $created ? $createdCount++ : $updatedCount++;
            $messages[] = $created ? 'Resource created.' : 'Resource updated.';
        }

        return [
            'created' => $createdCount > 0,
            'id_store' => $store->id,
            'id_catalogue' => $catalogue?->id,
            'id_product' => $product?->id,
            'message' => 'Resources processed: ' . $createdCount . ' created, ' . $updatedCount . ' updated, ' . $skippedCount . ' skipped, ' . $failedCount . ' failed. ' . implode(' ', array_slice($messages, 0, 3)),
        ];
    }

    protected function importPrice(array $data, ImportBatch $batch): array
    {
        $store = $this->storeFrom($data, $batch);
        $product = $this->productFrom($data, $store, true);
        $values = $this->onlyFilled($data, ['price_type','currency','regular_price','sale_price','tax_rate','valid_from','valid_until','status']);
        $values['id_store'] = $store->id;
        $values['id_product'] = $product->id;
        $values['tax_included'] = $this->boolValue($data['tax_included'] ?? 0);
        $values['metadata'] = $this->jsonValue($data['metadata'] ?? null);

        $key = ['id_store' => $store->id, 'id_product' => $product->id, 'price_type' => $values['price_type'] ?? 'standard', 'currency' => $values['currency'] ?? 'EUR'];
        $item = ProductPrice::query()->where($key)->first();
        $created = !$item;
        ProductPrice::updateOrCreate($key, array_merge($values, $key));

        return ['created' => $created, 'id_store' => $store->id, 'id_product' => $product->id, 'message' => $created ? 'Price created.' : 'Price updated.'];
    }

    protected function importPromotion(array $data, ImportBatch $batch): array
    {
        $store = $this->storeFrom($data, $batch);
        $catalogue = $this->catalogueFrom($data, $store);
        $values = $this->onlyFilled($data, ['name','slug','description','promotion_type','badge_label','discount_type','discount_value','starts_at','ends_at','status']);
        $values['id_store'] = $store->id;
        $values['id_catalogue'] = $catalogue?->id;
        $values['slug'] = $values['slug'] ?? Str::slug($values['name']);
        $values['metadata'] = $this->jsonValue($data['metadata'] ?? null);

        $item = Promotion::query()->where('id_store', $store->id)->where('slug', $values['slug'])->first();
        $created = !$item;
        $item = Promotion::updateOrCreate(['id_store' => $store->id, 'slug' => $values['slug']], $values);

        return ['created' => $created, 'id_store' => $store->id, 'id_catalogue' => $catalogue?->id, 'message' => $created ? 'Promotion created.' : 'Promotion updated.'];
    }

    protected function importPromotionProduct(array $data, ImportBatch $batch): array
    {
        $store = $this->storeFrom($data, $batch);
        $product = $this->productFrom($data, $store, true);
        $promotion = Promotion::query()->where('id_store', $store->id)->where('slug', $data['promotion_slug'] ?? null)->firstOrFail();
        $values = $this->onlyFilled($data, ['custom_badge_label','custom_sale_price','sort_order','status']);
        $values['id_store'] = $store->id;
        $values['id_promotion'] = $promotion->id;
        $values['id_product'] = $product->id;
        $values['metadata'] = $this->jsonValue($data['metadata'] ?? null);

        $key = ['id_store' => $store->id, 'id_promotion' => $promotion->id, 'id_product' => $product->id];
        $created = !PromotionProduct::query()->where($key)->exists();
        PromotionProduct::updateOrCreate($key, $values);

        return ['created' => $created, 'id_store' => $store->id, 'id_product' => $product->id, 'message' => $created ? 'Promotion product created.' : 'Promotion product updated.'];
    }

    protected function importTheme(array $data, ImportBatch $batch): array
    {
        $store = $this->storeFrom($data, $batch);
        $values = $this->onlyFilled($data, ['name','slug','font_family','heading_font_family','primary_color','secondary_color','accent_color','background_color','text_color','button_style','card_style','border_radius','custom_css','status']);
        $values['id_store'] = $store->id;
        $values['slug'] = $values['slug'] ?? Str::slug($values['name']);
        $values['is_default'] = $this->boolValue($data['is_default'] ?? 0);
        $values['metadata'] = $this->jsonValue($data['metadata'] ?? null);

        $created = !StoreTheme::query()->where('id_store', $store->id)->where('slug', $values['slug'])->exists();
        StoreTheme::updateOrCreate(['id_store' => $store->id, 'slug' => $values['slug']], $values);

        return ['created' => $created, 'id_store' => $store->id, 'message' => $created ? 'Theme created.' : 'Theme updated.'];
    }

    protected function importEnvironment(array $data, ImportBatch $batch): array
    {
        $store = $this->storeFrom($data, $batch);
        $values = $this->onlyFilled($data, ['name','slug','environment_type','background_type','background_color','lighting_preset','camera_preset','status']);
        $values['id_store'] = $store->id;
        $values['slug'] = $values['slug'] ?? Str::slug($values['name']);
        $values['is_default'] = $this->boolValue($data['is_default'] ?? 0);
        $values['vr_scene_config'] = $this->jsonValue($data['vr_scene_config'] ?? null);
        $values['ar_scene_config'] = $this->jsonValue($data['ar_scene_config'] ?? null);
        $values['metadata'] = $this->jsonValue($data['metadata'] ?? null);

        $created = !StoreEnvironment::query()->where('id_store', $store->id)->where('slug', $values['slug'])->exists();
        StoreEnvironment::updateOrCreate(['id_store' => $store->id, 'slug' => $values['slug']], $values);

        return ['created' => $created, 'id_store' => $store->id, 'message' => $created ? 'Environment created.' : 'Environment updated.'];
    }

    protected function importSetting(array $data, ImportBatch $batch): array
    {
        $store = $this->storeFrom($data, $batch, false);
        $values = $this->onlyFilled($data, ['group','key','value','type']);
        $values['id_store'] = $store?->id;
        $values['metadata'] = $this->jsonValue($data['metadata'] ?? null);

        $key = ['id_store' => $store?->id, 'key' => $values['key']];
        $created = !Setting::query()->where($key)->exists();
        Setting::updateOrCreate($key, $values);

        return ['created' => $created, 'id_store' => $store?->id, 'message' => $created ? 'Setting created.' : 'Setting updated.'];
    }

    protected function storeFrom(array $data, ImportBatch $batch, bool $required = true): ?Store
    {
        if (!empty($data['store_code'])) {
            $store = Store::query()->where('code', $data['store_code'])->first();
            if ($store) return $store;
        }

        if ($batch->id_store) {
            return Store::query()->findOrFail($batch->id_store);
        }

        if ($required) {
            throw new \RuntimeException('Store not found. Select a store or provide store_code in CSV.');
        }

        return null;
    }

    protected function catalogueFrom(array $data, Store $store): ?Catalogue
    {
        if (empty($data['catalogue_slug'])) return null;
        return Catalogue::query()->where('id_store', $store->id)->where('slug', $data['catalogue_slug'])->first();
    }

    protected function productFrom(array $data, Store $store, bool $required = false): ?Product
    {
        $reference = $this->firstFilled($data, [
            'product_reference',
            'reference',
            'resource_owner_key',
            'product_key',
        ]);

        $sku = $this->firstFilled($data, ['product_sku', 'sku']);
        $externalId = $this->firstFilled($data, ['product_external_id', 'external_id']);
        $idProduct = $this->firstFilled($data, ['id_product', 'product_id']);

        $query = Product::query()->where('id_store', $store->id);
        $product = null;

        if ($idProduct !== null && ctype_digit((string) $idProduct)) {
            $product = (clone $query)->where('id', (int) $idProduct)->first();
        }

        if (!$product && $reference !== null) {
            $product = (clone $query)->where('reference', trim((string) $reference))->first();
        }

        if (!$product && $sku !== null) {
            $product = (clone $query)->where('sku', trim((string) $sku))->first();
        }

        if (!$product && $externalId !== null) {
            $product = (clone $query)->where('external_id', trim((string) $externalId))->first();
        }

        if (!$product && $required) {
            $identifier = $reference ?? $sku ?? $externalId ?? $idProduct ?? '[missing]';
            throw new \RuntimeException('Product not found for store [' . $store->code . ']: ' . $identifier);
        }

        return $product;
    }

    protected function firstFilled(array $data, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $data) && trim((string) $data[$key]) !== '') {
                return $data[$key];
            }
        }

        return null;
    }

    protected function resolvedResourceOwnerType(?string $ownerType, ?Product $product, ?Catalogue $catalogue): string
    {
        $ownerType = strtolower(trim((string) $ownerType));

        if ($product) {
            return 'product';
        }

        if ($catalogue) {
            return 'catalogue';
        }

        return in_array($ownerType, ['store', 'catalogue', 'product', 'theme', 'environment'], true)
            ? $ownerType
            : 'store';
    }

    protected function resolvedResourceOwnerId(string $ownerType, Store $store, ?Product $product, ?Catalogue $catalogue): int
    {
        return match ($ownerType) {
            'product' => (int) $product?->id,
            'catalogue' => (int) $catalogue?->id,
            default => (int) $store->id,
        };
    }


    protected function importProductResourcesFromRow(array $data, int $storeId, int $productId, ?int $catalogueId = null): array
    {
        $map = [
            'image_urls' => 'image',
            'gallery_image_urls' => 'gallery_image',
            'thumbnail_url' => 'thumbnail',
            'cover_url' => 'cover',
            'manual_urls' => 'manual',
            'datasheet_urls' => 'datasheet',
            'assembly_instruction_urls' => 'assembly_instructions',
            'video_urls' => 'video',
            'audio_urls' => 'audio',
            'ambient_audio_urls' => 'ambient_audio',
            'voiceover_urls' => 'voiceover',
            'model_3d_url' => 'model_3d',
            'model_3d_urls' => 'model_3d',
            'ar_file_url' => 'ar_file',
            'ar_file_urls' => 'ar_file',
            'vr_file_url' => 'vr_file',
            'vr_file_urls' => 'vr_file',
        ];

        $created = 0;
        $skipped = 0;
        $failed = 0;
        $sequence = 0;

        foreach ($map as $column => $resourceType) {
            foreach ($this->splitUrls($data[$column] ?? null) as $url) {
                $sequence++;
                $isMain = in_array($column, ['image_urls', 'thumbnail_url', 'cover_url'], true) && $sequence === 1;
                $result = $this->downloadResourceFromUrl($url, $storeId, $productId, $catalogueId, $resourceType, null, $isMain, $sequence);
                if ($result['status'] === 'created') {
                    $created++;
                } elseif ($result['status'] === 'skipped') {
                    $skipped++;
                } else {
                    $failed++;
                }
            }
        }

        return compact('created', 'skipped', 'failed');
    }

    protected function splitUrls(mixed $value): array
    {
        if ($value === null || trim((string) $value) === '') {
            return [];
        }

        $parts = preg_split('/[|;,]+/', (string) $value) ?: [];
        return array_values(array_filter(array_map('trim', $parts), fn ($url) => $url !== ''));
    }

    protected function shouldDownloadResource(string $resourceType, string $sourceType): bool
    {
        if (in_array($sourceType, ['external_link', 'external_url_only', 'embed'], true)) {
            return false;
        }

        return in_array($resourceType, [
            'image', 'gallery_image', 'thumbnail', 'cover',
            'manual', 'datasheet', 'assembly_instructions', 'download',
            'audio', 'ambient_audio', 'voiceover', 'sound_effect', 'music_track',
            'model_3d', 'ar_file', 'vr_file', 'vr_scene',
            'skybox', 'floor_texture', 'environment_background',
        ], true);
    }

    protected function downloadResourceFromUrl(
        string $url,
        int $storeId,
        ?int $productId,
        ?int $catalogueId,
        string $resourceType,
        ?string $title = null,
        bool $isMain = false,
        int $sortOrder = 0,
        ?string $description = null,
        ?array $metadata = null
    ): array {
        $url = trim($url);
        if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
            return ['status' => 'failed', 'created' => false, 'message' => 'Invalid resource URL: ' . $url];
        }

        $existing = Resource::query()
            ->where('id_store', $storeId)
            ->where('source_url', $url)
            ->when($productId, fn ($query) => $query->where('id_product', $productId))
            ->first();

        if ($existing) {
            return ['status' => 'skipped', 'created' => false, 'message' => 'Resource already exists: ' . $url];
        }

        try {
            $response = Http::timeout(30)->retry(1, 300)->get($url);
            if (!$response->successful()) {
                return ['status' => 'failed', 'created' => false, 'message' => 'Download failed [' . $response->status() . ']: ' . $url];
            }

            $body = $response->body();
            if ($body === '') {
                return ['status' => 'failed', 'created' => false, 'message' => 'Downloaded file is empty: ' . $url];
            }

            $diskName = (string) config('webcatalogue.storage_disk', 'public');
            $disk = Storage::disk($diskName);
            $folder = $this->resourceFolderFor($storeId, $productId, $catalogueId, $resourceType);
            $disk->makeDirectory($folder);

            $extension = $this->extensionFromUrlOrMime($url, (string) $response->header('Content-Type'));
            $filename = $this->resourceFilename($storeId, $productId, $resourceType, $sortOrder, $extension);
            $path = trim($folder, '/') . '/' . $filename;
            $disk->put($path, $body);

            $resource = Resource::create([
                'id_store' => $storeId,
                'id_catalogue' => $catalogueId,
                'id_product' => $productId,
                'resource_owner_type' => $productId ? 'product' : ($catalogueId ? 'catalogue' : 'store'),
                'resource_owner_id' => $productId ?: ($catalogueId ?: $storeId),
                'resource_type' => $resourceType,
                'title' => $title ?: pathinfo(parse_url($url, PHP_URL_PATH) ?: $filename, PATHINFO_FILENAME),
                'description' => $description,
                'source_type' => 'downloaded_url',
                'source_url' => $url,
                'file_path' => $path,
                'public_url' => $disk->url($path),
                'filename' => $filename,
                'mime_type' => (string) $response->header('Content-Type'),
                'file_size' => strlen($body),
                'extension' => $extension,
                'is_main' => $isMain,
                'sort_order' => $sortOrder,
                'status' => 'active',
                'metadata' => array_merge($metadata ?? [], [
                    'imported_from_url' => true,
                    'downloaded_at' => now()->toDateTimeString(),
                ]),
            ]);

            return ['status' => 'created', 'created' => true, 'id_resource' => $resource->id, 'message' => 'Resource downloaded: ' . $filename];
        } catch (\Throwable $e) {
            return ['status' => 'failed', 'created' => false, 'message' => 'Download exception: ' . $e->getMessage()];
        }
    }

    protected function resourceFolderFor(int $storeId, ?int $productId, ?int $catalogueId, string $resourceType): string
    {
        $root = trim((string) config('webcatalogue.storage_root', 'webcatalogue'), '/');
        $typeFolder = match ($resourceType) {
            'image', 'gallery_image', 'cover', 'environment_background' => 'images',
            'thumbnail' => 'thumbnails',
            'video' => 'videos',
            'audio', 'ambient_audio', 'voiceover', 'sound_effect', 'music_track' => 'audio',
            'model_3d' => 'models',
            'ar_file' => 'ar',
            'vr_file', 'vr_scene' => 'vr',
            'skybox' => 'skyboxes',
            'floor_texture' => 'floors',
            'manual', 'datasheet', 'assembly_instructions', 'download' => 'documents',
            default => 'assets',
        };

        if ($productId) {
            return $root . '/stores/' . $storeId . '/products/' . $productId . '/' . $typeFolder;
        }

        if ($catalogueId) {
            return $root . '/stores/' . $storeId . '/catalogues/' . $catalogueId . '/' . $typeFolder;
        }

        return $root . '/stores/' . $storeId . '/resources/' . $typeFolder;
    }

    protected function resourceFilename(int $storeId, ?int $productId, string $resourceType, int $sortOrder, string $extension): string
    {
        $safeType = preg_replace('/[^a-z0-9_\-]/i', '_', $resourceType) ?: 'resource';
        $owner = $productId ?: 'resource';
        $sequence = str_pad((string) max(1, $sortOrder), 3, '0', STR_PAD_LEFT);
        return $storeId . '_' . $owner . '_' . $safeType . '_' . $sequence . '_' . substr(md5((string) microtime(true)), 0, 6) . '.' . $extension;
    }

    protected function extensionFromUrlOrMime(string $url, string $mime): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: '';
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($extension !== '') {
            return substr($extension, 0, 20);
        }

        return match (strtolower(strtok($mime, ';') ?: '')) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'application/pdf' => 'pdf',
            'audio/mpeg' => 'mp3',
            'audio/wav', 'audio/x-wav' => 'wav',
            'video/mp4' => 'mp4',
            'model/gltf-binary' => 'glb',
            'model/gltf+json' => 'gltf',
            'model/vnd.usdz+zip' => 'usdz',
            default => 'bin',
        };
    }


    protected function detectCsvDelimiter(string $absolutePath): string
    {
        $sample = '';
        $handle = fopen($absolutePath, 'r');

        if ($handle) {
            for ($i = 0; $i < 5 && !feof($handle); $i++) {
                $line = fgets($handle);
                if ($line !== false && trim($line) !== '') {
                    $sample .= $line;
                    break;
                }
            }
            fclose($handle);
        }

        $configured = (string) config('webcatalogue.csv_delimiter', 'auto');
        if (in_array($configured, [',', ';', "\t", '|'], true)) {
            return $configured;
        }

        $candidates = [
            ';' => substr_count($sample, ';'),
            ',' => substr_count($sample, ','),
            "\t" => substr_count($sample, "\t"),
            '|' => substr_count($sample, '|'),
        ];

        arsort($candidates);
        $delimiter = array_key_first($candidates);

        return ($candidates[$delimiter] ?? 0) > 0 ? $delimiter : ',';
    }

    protected function normalizeHeaders(array $headers): array
    {
        return array_map(function ($header) {
            $header = trim((string) $header);
            $header = preg_replace('/^\xEF\xBB\xBF/', '', $header) ?? $header;
            $header = strtolower($header);
            $header = preg_replace('/[^a-z0-9_]+/', '_', $header) ?? $header;
            return trim($header, '_');
        }, $headers);
    }

    protected function isEmptyCsvRow(array $values): bool
    {
        foreach ($values as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    protected function normalizeCsvFileToUtf8(string $absolutePath): void
    {
        if (!is_file($absolutePath) || !is_readable($absolutePath) || !is_writable($absolutePath)) {
            return;
        }

        $contents = file_get_contents($absolutePath);
        if ($contents === false || $contents === '') {
            return;
        }

        $contents = preg_replace('/^\xEF\xBB\xBF/', '', $contents) ?? $contents;
        $encoding = mb_detect_encoding($contents, ['UTF-8', 'Windows-1252', 'ISO-8859-1', 'ISO-8859-15'], true) ?: 'Windows-1252';

        if (strtoupper($encoding) !== 'UTF-8' || !mb_check_encoding($contents, 'UTF-8')) {
            $contents = mb_convert_encoding($contents, 'UTF-8', $encoding);
        }

        // Remove invalid/control bytes that can break JSON encoding while preserving tabs/newlines.
        $contents = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $contents) ?? $contents;

        file_put_contents($absolutePath, $contents);
    }

    protected function sanitizeCsvRow(array $row): array
    {
        return array_map(function ($value) {
            return is_string($value) ? $this->sanitizeStringForJson($value) : $value;
        }, $row);
    }

    protected function sanitizePayloadForJson(array $payload): array
    {
        foreach ($payload as $key => $value) {
            $cleanKey = is_string($key) ? trim($this->sanitizeStringForJson($key)) : $key;

            if (is_array($value)) {
                $value = $this->sanitizePayloadForJson($value);
            } elseif (is_string($value)) {
                $value = $this->sanitizeStringForJson($value);
            }

            if ($cleanKey !== $key) {
                unset($payload[$key]);
            }

            $payload[$cleanKey] = $value;
        }

        return $payload;
    }

    protected function sanitizeStringForJson(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;

        if (!mb_check_encoding($value, 'UTF-8')) {
            $encoding = mb_detect_encoding($value, ['UTF-8', 'Windows-1252', 'ISO-8859-1', 'ISO-8859-15'], true) ?: 'Windows-1252';
            $value = mb_convert_encoding($value, 'UTF-8', $encoding);
        }

        $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? $value;

        return trim($value);
    }

    protected function batchType(ImportBatch $batch): string
    {
        return Str::after($batch->source_type, 'csv:');
    }

    protected function templateOrFail(string $type): array
    {
        $templates = $this->templates();
        abort_unless(isset($templates[$type]), 404);
        return $templates[$type];
    }

    protected function templates(): array
    {
        return config('webcatalogue_import_templates', config('import_templates', []));
    }

    protected function boolValue(mixed $value): bool
    {
        return in_array(strtolower((string) $value), ['1','true','yes','sim','active'], true);
    }

    protected function jsonValue(?string $value): ?array
    {
        if ($value === null || trim($value) === '') return null;
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : ['value' => $value];
    }

    protected function onlyFilled(array $data, array $keys): array
    {
        $values = Arr::only($data, $keys);
        return array_filter($values, fn($value) => $value !== null && $value !== '');
    }

    protected function redirectRouteForType(string $type): string
    {
        return match ($type) {
            'stores' => 'webcatalogue.stores.index',
            'catalogues' => 'webcatalogue.catalogues.index',
            'products' => 'webcatalogue.products.index',
            'resources' => 'webcatalogue.resources.index',
            'prices' => 'webcatalogue.pricing.index',
            'promotions', 'promotion_products' => 'webcatalogue.promotions.index',
            'themes' => 'webcatalogue.themes.index',
            'environments' => 'webcatalogue.environments.index',
            default => 'webcatalogue.imports.index',
        };
    }
}
