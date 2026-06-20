<?php

namespace Modules\LSG\ProductGrowth\Shared\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\LSG\ProductGrowth\ProductCore\Models\Product;
use Modules\LSG\ProductGrowth\ProductCore\Models\ProductAsset;
use Modules\LSG\ProductGrowth\ProductCore\Models\ProductCharacteristic;
use Modules\LSG\ProductGrowth\ProductCore\Models\ProductCharacteristicValue;
use Modules\LSG\ProductGrowth\ProductCore\Models\StoreProduct;

class ProductGrowthStageController extends Controller
{
    public function __invoke(string $stage)
    {
        $stageMeta = $this->stages()[$stage] ?? null;

        abort_if(!$stageMeta, 404);

        $this->preparePage($stageMeta);

        $products = Product::with(['brand', 'supplier', 'storeProducts.store', 'assets'])
            ->latest()
            ->limit(12)
            ->get();
        $workflowAreas = $this->workflowAreas();
        $currentArea = $this->stageArea($stage);
        $workQueue = $this->workQueue($currentArea);

        $stats = [
            'products' => Product::count(),
            'in_review' => Product::where('status', 'in_review')->count(),
            'approved' => Product::where('status', 'approved')->count(),
            'ready_to_sync' => Product::where('status', 'ready_to_sync')->count(),
            'assets' => ProductAsset::count(),
            'store_products' => StoreProduct::count(),
            'sync_failed' => StoreProduct::where('sync_status', 'sync_failed')->count(),
        ];

        return $this->view('product-growth-stage::stage', [
            'stage' => $stageMeta,
            'products' => $products,
            'stats' => $stats,
            'stages' => $this->stages(),
            'workflowAreas' => $workflowAreas,
            'currentArea' => $currentArea,
            'workQueue' => $workQueue,
        ]);
    }

    public function edit(Product $product, string $stage)
    {
        $stageMeta = $this->stages()[$stage] ?? null;

        abort_if(!$stageMeta, 404);

        $this->preparePage($stageMeta, $product);

        $product->load(['brand', 'supplier', 'storeProducts.store', 'assets']);

        return $this->view('product-growth-stage::stage', [
            'stage' => $stageMeta,
            'stageKey' => $stage,
            'product' => $product,
            'products' => collect([$product]),
            'stats' => $this->stats(),
            'stages' => $this->stages(),
            'stageData' => data_get($product->metadata ?? [], 'department_content.' . $stage, []),
            'assetData' => $this->assetData($product),
            'workflowAreas' => $this->workflowAreas(),
            'currentArea' => $this->stageArea($stage),
            'areaReviews' => data_get($product->metadata ?? [], 'department_reviews', []),
            'stageOptions' => $this->stageOptions($stage, $product),
            'characteristics' => $this->expectedCharacteristicsForProduct($stage, $product),
            'selectedCharacteristicValues' => $this->selectedCharacteristicValues($stage, $product),
            'hideProductGrowthNavigation' => true,
            'canSeeAdminTimeline' => $this->authenticatedUserIsAdmin(),
        ]);
    }

    public function update(Request $request, Product $product, string $stage): RedirectResponse
    {
        $stageMeta = $this->stages()[$stage] ?? null;

        abort_if(!$stageMeta, 404);

        $data = $this->validatedStageData($request, $stage);
        if ($stage === 'store-brand-manager') {
            $data = $this->normalizePurchaseStructures($data);
            $this->validatePurchaseStructureRules($product, $data);
            $data = $this->enrichPurchaseMasterData($data);
        }
        if ($stage === 'brand-compliance-manager') {
            $data = $this->normalizeFinanceData($product, $data);
        }
        if ($stage === 'marketing-content-manager') {
            $data = $this->normalizeSalesStructures($data);
            $this->validateSalesStructureRules($product, $data);
        }
        if ($stage === 'creative-asset-manager') {
            $data = $this->syncCreativeAssets($product, $data, $stage);
        }

        $metadata = $product->metadata ?? [];
        $metadata['department_content'][$stage] = array_merge($metadata['department_content'][$stage] ?? [], $data);
        if ($stage === 'store-brand-manager') {
            $metadata['product_growth']['master_data_source'] = 'catalog_manager';
            foreach (['manufacturer_id', 'manufacturer_name', 'supplier_id', 'supplier_name'] as $field) {
                if (array_key_exists($field, $data)) {
                    $metadata['product_growth'][$field] = $data[$field];
                }
            }
        }

        $metadata['workflow_steps'][$this->stageKey($stage)] = [
            'title' => $stageMeta['title'],
            'owner' => $stageMeta['department'],
            'status' => 'completed',
            'content' => $this->stageContentSummary($stage, $data),
            'output' => $stageMeta['output'],
            'updated_at' => now()->toDateTimeString(),
        ];
        $this->markAreaSubmitted($metadata, $stage);

        $product->metadata = $metadata;
        $product->status = $product->status === 'draft' ? 'in_review' : $product->status;

        if ($stage === 'store-brand-manager') {
            $product->fill([
                'reference' => $data['reference'] ?? $product->reference,
                'internal_sku' => $data['internal_sku'] ?? ($data['reference'] ?? $product->internal_sku),
                'product_type' => $data['product_type'] ?? $product->product_type,
                'ean' => $data['ean13'] ?? $product->ean,
                'brand_id' => $data['manufacturer_id'] ?? $product->brand_id,
                'supplier_id' => $data['supplier_id'] ?? $product->supplier_id,
            ]);
            $this->updatePurchaseStoreData($product, $data);
        }

        if ($stage === 'brand-compliance-manager') {
            $product->fill([
                'base_cost' => $data['purchase_price'] ?? $product->base_cost,
                'base_price' => $data['base_sale_price'] ?? $product->base_price,
            ]);
            $this->updateFinanceStoreData($product, $data);
        }

        if ($stage === 'logistics-manager') {
            $product->fill([
                'ean' => $data['ean13'] ?? $product->ean,
                'weight' => $data['weight'] ?? $product->weight,
                'width' => $data['width'] ?? $product->width,
                'height' => $data['height'] ?? $product->height,
                'depth' => $data['depth'] ?? $product->depth,
            ]);
            $this->updateLogisticsStoreData($product, $data);
        }

        if ($stage === 'marketing-content-manager') {
            $this->syncProductCharacteristics($product, $data['characteristics'] ?? []);
            $this->updateStoreSales($product, $data);
            $metadata = $product->metadata ?? [];
            $metadata['description_generation_request'] = $this->buildDescriptionGenerationRequest($product, $data);
            $product->metadata = $metadata;
        }

        $product->data_quality_score = max((float) $product->data_quality_score, 88);
        $product->save();

        $this->logStageUpdate($product, $stage, $stageMeta, $data);

        return redirect()
            ->route($stageMeta['route'])
            ->with('success', $stageMeta['title'] . ' atualizado.');
    }

    public function reviewItem(Request $request, Product $product, string $area, string $item, string $decision): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        abort_unless($this->stageArea('workflow-manager') === 'admin', 403);
        abort_unless(in_array($decision, ['approved', 'rejected'], true), 404);

        $workflowAreas = $this->workflowAreas();
        abort_unless(isset($workflowAreas[$area]['items'][$item]), 404);

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $metadata = $product->metadata ?? [];
        $metadata['department_reviews'][$area]['items'][$item] = [
            'status' => $decision,
            'reason' => $decision === 'rejected' ? ($data['reason'] ?? null) : null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now()->toDateTimeString(),
        ];

        $metadata['department_reviews'][$area]['status'] = $this->areaReviewStatus($workflowAreas[$area], $metadata['department_reviews'][$area]['items'] ?? []);
        $metadata['department_reviews'][$area]['updated_at'] = now()->toDateTimeString();

        $product->metadata = $metadata;
        $product->status = $this->productReviewStatus($metadata['department_reviews'] ?? [], $workflowAreas);
        $product->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'item_status' => $decision,
                'area_status' => $metadata['department_reviews'][$area]['status'],
                'product_status' => $product->status,
                'reason' => $metadata['department_reviews'][$area]['items'][$item]['reason'],
                'message' => $workflowAreas[$area]['items'][$item]['label'] . ' marcado como ' . ($decision === 'approved' ? 'aprovado' : 'para correcao') . '.',
            ]);
        }

        return back()->with('success', $workflowAreas[$area]['items'][$item]['label'] . ' marcado como ' . ($decision === 'approved' ? 'aprovado' : 'para correcao') . '.');
    }

    public function reviewArea(Request $request, Product $product, string $area): RedirectResponse
    {
        abort_unless($this->stageArea('workflow-manager') === 'admin', 403);

        $workflowAreas = $this->workflowAreas();
        abort_unless(isset($workflowAreas[$area]), 404);

        $metadata = $product->metadata ?? [];

        foreach (array_keys($workflowAreas[$area]['items'] ?? []) as $itemKey) {
            $metadata['department_reviews'][$area]['items'][$itemKey] = [
                'status' => 'approved',
                'reason' => null,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now()->toDateTimeString(),
            ];
        }

        $metadata['department_reviews'][$area]['status'] = $this->areaReviewStatus($workflowAreas[$area], $metadata['department_reviews'][$area]['items'] ?? []);
        $metadata['department_reviews'][$area]['updated_at'] = now()->toDateTimeString();

        $product->metadata = $metadata;
        $product->status = $this->productReviewStatus($metadata['department_reviews'] ?? [], $workflowAreas);
        $product->save();

        return back()->with('success', ($workflowAreas[$area]['label'] ?? $area) . ' aprovado.');
    }

    private function preparePage(array $stage, ?Product $product = null): void
    {
        $this->setPageTitle($product ? $stage['title'] . ' - ' . $product->name : $stage['title']);
        $this->setBreadcrumbs([
            ['label' => 'Dashboard', 'url' => route('dashboard.index'), 'translate' => false],
            ['label' => 'LSG', 'url' => route('lsg.index'), 'translate' => false],
            ['label' => 'Product Growth', 'url' => route('product_growth.product_core.dashboard'), 'translate' => false],
            ['label' => $stage['title'], 'url' => $product ? route($stage['route']) : null, 'translate' => false],
            ...($product ? [['label' => $product->name, 'url' => null, 'translate' => false]] : []),
        ]);

        if ($product) {
            $this->setActions([
                [
                    'key' => 'save',
                    'label' => 'Guardar',
                    'name' => 'Guardar',
                    'icon' => 'fa-solid fa-floppy-disk',
                    'type' => 'submit',
                    'form' => 'product-growth-stage-form',
                    'class' => 'lsg-action-btn--primary',
                ],
            ]);

            return;
        }

        $this->setActions([]);
    }

    private function stats(): array
    {
        return [
            'products' => Product::count(),
            'in_review' => Product::where('status', 'in_review')->count(),
            'approved' => Product::where('status', 'approved')->count(),
            'ready_to_sync' => Product::where('status', 'ready_to_sync')->count(),
            'assets' => ProductAsset::count(),
            'store_products' => StoreProduct::count(),
            'sync_failed' => StoreProduct::where('sync_status', 'sync_failed')->count(),
        ];
    }

    private function stageOptions(string $stage, ?Product $product = null): array
    {
        if ($stage !== 'store-brand-manager') {
            return [
                'selected_categories' => $this->selectedCatalogCategoriesForProduct($product),
                'supplier_finance_context' => $this->supplierFinanceContext($product),
                'product_suggestions' => $stage === 'marketing-content-manager'
                    ? $this->salesProductSuggestions($product)
                    : [],
            ];
        }

        return [
            'manufacturers' => $this->catalogOptionList('catalog_core_manufacturers', 'catalog_manufacturer_stores', 'manufacturer_id', $product),
            'suppliers' => $this->catalogOptionList('catalog_core_suppliers', 'catalog_supplier_stores', 'supplier_id', $product),
            'categories' => $this->catalogCategoriesForProduct($product),
            'selected_categories' => $this->selectedCatalogCategoriesForProduct($product),
            'supplier_finance_context' => $this->supplierFinanceContext($product),
            'combination_options' => $this->combinationOptionsForProduct($product),
            'pack_product_suggestions' => $this->packProductSuggestions($product),
        ];
    }

    private function packProductSuggestions(?Product $product): array
    {
        $storeIds = $this->productStoreIds($product);

        return Product::query()
            ->when($product, fn ($query) => $query->where('id', '!=', $product->id))
            ->whereIn('status', $this->approvedProductStatuses())
            ->when($storeIds->isNotEmpty(), function ($query) use ($storeIds): void {
                $query->whereHas('storeProducts', fn ($storeQuery) => $storeQuery->whereIn('store_id', $storeIds->all()));
            })
            ->where(function ($query): void {
                $query->whereNotNull('reference')
                    ->orWhereNotNull('internal_sku')
                    ->orWhereNotNull('metadata');
            })
            ->orderBy('name')
            ->limit(300)
            ->get(['id', 'name', 'reference', 'internal_sku', 'metadata'])
            ->flatMap(function (Product $item): array {
                $suggestions = [];

                foreach (array_filter([$item->reference, $item->internal_sku]) as $reference) {
                    $suggestions[] = [
                        'value' => $reference,
                        'label' => trim($reference . ' - ' . $item->name),
                    ];
                }

                $combinations = collect(data_get($item->metadata ?? [], 'department_content.store-brand-manager.combinations', []))
                    ->filter(fn ($combination) => is_array($combination) && filled($combination['sku'] ?? null));

                foreach ($combinations as $combination) {
                    $parts = collect($combination['attributes'] ?? [])
                        ->filter(fn ($value) => filled($value))
                        ->map(fn ($value) => Str::headline((string) $value))
                        ->implode(' / ');

                    $sku = (string) $combination['sku'];
                    $suggestions[] = [
                        'value' => $sku,
                        'label' => trim($sku . ' - ' . $item->name . ($parts ? ' (' . $parts . ')' : '')),
                    ];
                }

                return $suggestions;
            })
            ->unique('value')
            ->values()
            ->all();
    }

    private function salesProductSuggestions(?Product $product): array
    {
        $storeIds = $this->productStoreIds($product);

        return Product::query()
            ->when($product, fn ($query) => $query->where('id', '!=', $product->id))
            ->whereIn('status', $this->approvedProductStatuses())
            ->when($storeIds->isNotEmpty(), function ($query) use ($storeIds): void {
                $query->whereHas('storeProducts', fn ($storeQuery) => $storeQuery->whereIn('store_id', $storeIds->all()));
            })
            ->where(function ($query): void {
                $query->whereNotNull('reference')
                    ->orWhereNotNull('internal_sku');
            })
            ->orderBy('name')
            ->limit(300)
            ->get(['id', 'name', 'reference', 'internal_sku'])
            ->flatMap(function (Product $item): array {
                $suggestions = [];

                foreach (array_filter([$item->reference, $item->internal_sku]) as $reference) {
                    $suggestions[] = [
                        'value' => $reference,
                        'label' => trim($reference . ' - ' . $item->name),
                    ];
                }

                return $suggestions;
            })
            ->unique('value')
            ->values()
            ->all();
    }

    private function approvedProductStatuses(): array
    {
        return ['approved', 'ready_to_sync', 'synced', 'needs_resync'];
    }

    private function productStoreIds(?Product $product)
    {
        if (!$product) {
            return collect();
        }

        if (!$product->relationLoaded('storeProducts')) {
            $product->load('storeProducts');
        }

        return $product->storeProducts
            ->pluck('store_id')
            ->filter()
            ->map(fn ($storeId) => (int) $storeId)
            ->unique()
            ->values();
    }

    private function selectedCatalogCategoriesForProduct(?Product $product): array
    {
        if (!$product || !Schema::hasTable('catalog_store_categories') || !Schema::hasTable('catalog_store_category_lang')) {
            return [];
        }

        $selected = collect(data_get($product->metadata ?? [], 'product_growth.category_ids_by_store', []))
            ->map(fn ($categoryId) => (int) $categoryId)
            ->filter()
            ->unique()
            ->values();

        if ($selected->isEmpty()) {
            return [];
        }

        $categoryRows = DB::table('catalog_store_categories as c')
            ->leftJoin('catalog_store_category_lang as cl', function ($join): void {
                $join->on('cl.store_category_id', '=', 'c.id')
                    ->where('cl.locale', 'pt');
            })
            ->select('c.id', 'c.parent_id', 'c.code', 'cl.name')
            ->get()
            ->mapWithKeys(fn ($category) => [(int) $category->id => [
                'parent_id' => $category->parent_id ? (int) $category->parent_id : null,
                'name' => $category->name ?: ($category->code ?: 'Categoria #' . $category->id),
            ]])
            ->all();

        return $product->storeProducts
            ->map(function ($storeProduct) use ($product, $categoryRows) {
                $categoryId = (int) data_get($product->metadata ?? [], 'product_growth.category_ids_by_store.' . $storeProduct->store_id, 0);

                if (!$categoryId) {
                    return null;
                }

                return [
                    'store' => $storeProduct->store?->name ?? 'Loja #' . $storeProduct->store_id,
                    'category' => $this->catalogCategoryPath($categoryId, $categoryRows),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function catalogCategoryPath(int $categoryId, array $categoryRows): string
    {
        $path = [];
        $visited = [];
        $currentId = $categoryId;

        while ($currentId && isset($categoryRows[$currentId]) && !in_array($currentId, $visited, true)) {
            $visited[] = $currentId;
            array_unshift($path, $categoryRows[$currentId]['name']);
            $currentId = $categoryRows[$currentId]['parent_id'] ?? null;
        }

        return $path ? implode(' > ', $path) : 'Categoria #' . $categoryId;
    }

    private function expectedCharacteristicsForProduct(string $stage, ?Product $product)
    {
        if ($stage !== 'marketing-content-manager') {
            return collect();
        }

        return $this->categoryCharacteristicsForProduct($product);
    }

    private function categoryCharacteristicsForProduct(?Product $product)
    {
        if (
            !$product
            || !Schema::hasTable('lsg_catalog_category_characteristics')
            || !Schema::hasTable('lsg_catalog_core_characteristics')
        ) {
            return collect();
        }

        $categoryIds = collect(data_get($product->metadata ?? [], 'product_growth.category_ids_by_store', []))
            ->map(fn ($categoryId) => (int) $categoryId)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (!$categoryIds) {
            return collect();
        }

        $hasUsageScope = Schema::hasColumn('lsg_catalog_core_characteristics', 'usage_scope');
        $productType = (string) (data_get($product->metadata ?? [], 'department_content.store-brand-manager.product_type') ?? $product->product_type ?? 'simple');

        $characteristics = DB::table('lsg_catalog_category_characteristics as cc')
            ->join('lsg_catalog_core_characteristics as ch', 'ch.id', '=', 'cc.characteristic_id')
            ->whereIn('cc.store_category_id', $categoryIds)
            ->where('ch.is_active', true)
            ->select([
                'ch.id',
                'ch.name',
                'ch.slug',
                'ch.unit',
                'ch.data_type',
                $hasUsageScope
                    ? 'ch.usage_scope'
                    : DB::raw("'product' as usage_scope"),
                'ch.is_filterable',
                'ch.is_searchable',
                'ch.is_seo_keyword',
                'ch.is_syncable',
                DB::raw('MAX(cc.is_required) as is_required'),
                DB::raw('GROUP_CONCAT(CAST(cc.allowed_values AS CHAR) SEPARATOR "||") as allowed_values'),
                DB::raw('MIN(cc.position) as category_position'),
                DB::raw('MIN(cc.section) as category_section'),
            ])
            ->groupBy('ch.id', 'ch.name', 'ch.slug', 'ch.unit', 'ch.data_type', 'ch.is_filterable', 'ch.is_searchable', 'ch.is_seo_keyword', 'ch.is_syncable')
            ->when($hasUsageScope, fn ($query) => $query->groupBy('ch.usage_scope'))
            ->orderBy('category_position')
            ->orderBy('ch.name')
            ->get();

        $valuesByCharacteristic = $this->characteristicValuesByCharacteristic($characteristics->pluck('id')->all());

        $freeCharacteristicPriority = [
            'mana_cost' => 1,
            'mana_value' => 2,
        ];

        return $characteristics
            ->filter(fn ($characteristic) => $productType !== 'combination' || (string) ($characteristic->usage_scope ?? 'product') !== 'combination')
            ->map(function ($characteristic) use ($valuesByCharacteristic) {
            $allowedValues = $this->decodeAllowedValues($characteristic->allowed_values ?? null);
            $values = $valuesByCharacteristic[(int) $characteristic->id] ?? [];
            $characteristic->values = $allowedValues
                ? collect($values)->filter(fn ($value) => in_array((string) ($value['value'] ?? ''), $allowedValues, true))->values()->all()
                : $values;

            return $characteristic;
        })
            ->sortBy(function ($characteristic) use ($freeCharacteristicPriority) {
                $hasValues = !empty($characteristic->values ?? []);
                $priority = $freeCharacteristicPriority[(string) ($characteristic->slug ?? '')] ?? 99;

                return sprintf(
                    '%02d-%03d-%04d-%s',
                    $hasValues ? 1 : 0,
                    $hasValues ? 99 : $priority,
                    (int) ($characteristic->category_position ?? 0),
                    (string) ($characteristic->name ?? '')
                );
            })
            ->values();
    }

    private function combinationOptionsForProduct(?Product $product): array
    {
        $fallbackAttributes = [
            [
                'slug' => 'condition',
                'name' => 'Condition',
                'is_required' => true,
                'values' => [
                ['value' => 'near_mint', 'label' => 'Near Mint'],
                ['value' => 'excellent', 'label' => 'Excellent'],
                ['value' => 'good', 'label' => 'Good'],
                ['value' => 'light_played', 'label' => 'Light Played'],
                ['value' => 'played', 'label' => 'Played'],
                ['value' => 'poor', 'label' => 'Poor'],
                ],
            ],
            [
                'slug' => 'language',
                'name' => 'Language',
                'is_required' => true,
                'values' => [
                ['value' => 'english', 'label' => 'English'],
                ['value' => 'portuguese', 'label' => 'Portuguese'],
                ['value' => 'spanish', 'label' => 'Spanish'],
                ['value' => 'french', 'label' => 'French'],
                ['value' => 'german', 'label' => 'German'],
                ['value' => 'italian', 'label' => 'Italian'],
                ['value' => 'japanese', 'label' => 'Japanese'],
                ],
            ],
            [
                'slug' => 'finish',
                'name' => 'Finish',
                'is_required' => true,
                'values' => [
                ['value' => 'non_foil', 'label' => 'Non-Foil'],
                ['value' => 'foil', 'label' => 'Foil'],
                ['value' => 'etched_foil', 'label' => 'Etched Foil'],
                ],
            ],
        ];

        if (
            !$product
            || !Schema::hasTable('catalog_category_combination_attributes')
            || !Schema::hasTable('catalog_combination_attributes')
            || !Schema::hasTable('catalog_combination_attribute_values')
        ) {
            return $this->combinationOptionPayload($fallbackAttributes);
        }

        $categoryIds = collect(data_get($product->metadata ?? [], 'product_growth.category_ids_by_store', []))
            ->map(fn ($categoryId) => (int) $categoryId)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (!$categoryIds) {
            return $this->combinationOptionPayload($fallbackAttributes);
        }

        $attributes = DB::table('catalog_category_combination_attributes as cca')
            ->join('catalog_combination_attributes as ca', 'ca.id', '=', 'cca.attribute_id')
            ->whereIn('cca.store_category_id', $categoryIds)
            ->where('ca.is_active', true)
            ->select([
                'ca.id',
                'ca.name',
                'ca.slug',
                'ca.display_type',
                DB::raw('MAX(cca.is_required) as is_required'),
                DB::raw('GROUP_CONCAT(CAST(cca.allowed_values AS CHAR) SEPARATOR "||") as allowed_values'),
                DB::raw('MIN(cca.position) as category_position'),
            ])
            ->groupBy('ca.id', 'ca.name', 'ca.slug', 'ca.display_type')
            ->orderBy('category_position')
            ->orderBy('ca.position')
            ->orderBy('ca.name')
            ->get();

        if ($attributes->isEmpty()) {
            return $this->combinationOptionPayload($fallbackAttributes);
        }

        $values = DB::table('catalog_combination_attribute_values')
            ->whereIn('attribute_id', $attributes->pluck('id')->all())
            ->where('active', true)
            ->orderBy('position')
            ->orderBy('label')
            ->get(['attribute_id', 'value', 'label'])
            ->groupBy('attribute_id');

        $payload = $attributes
            ->map(function ($attribute) use ($values) {
                $allowedValues = $this->decodeAllowedValues($attribute->allowed_values ?? null);
                $attributeValues = collect($values[(int) $attribute->id] ?? [])
                    ->map(fn ($value) => [
                        'value' => $value->value,
                        'label' => $value->label ?: $value->value,
                    ]);

                return [
                    'slug' => (string) $attribute->slug,
                    'name' => (string) $attribute->name,
                    'display_type' => (string) $attribute->display_type,
                    'is_required' => (bool) $attribute->is_required,
                    'values' => ($allowedValues
                            ? $attributeValues->filter(fn ($value) => in_array((string) ($value['value'] ?? ''), $allowedValues, true))
                            : $attributeValues)
                        ->values()
                        ->all(),
                ];
            })
            ->filter(fn ($attribute) => filled($attribute['slug']) && filled($attribute['name']))
            ->values()
            ->all();

        return $this->combinationOptionPayload($payload ?: $fallbackAttributes);
    }

    private function combinationOptionPayload(array $attributes): array
    {
        $attributes = collect($attributes)->values()->all();
        $legacy = collect($attributes)
            ->mapWithKeys(fn ($attribute) => [(string) ($attribute['slug'] ?? '') => $attribute['values'] ?? []])
            ->filter(fn ($values, $slug) => filled($slug))
            ->all();

        return array_merge(['attributes' => $attributes], $legacy);
    }

    private function characteristicValuesByCharacteristic(array $characteristicIds): array
    {
        $characteristicIds = collect($characteristicIds)->map(fn ($id) => (int) $id)->filter()->unique()->all();

        if (!$characteristicIds || !Schema::hasTable('lsg_catalog_core_characteristic_values')) {
            return [];
        }

        $columns = ['characteristic_id', 'value', 'label'];
        if (Schema::hasColumn('lsg_catalog_core_characteristic_values', 'image_url')) {
            $columns[] = 'image_url';
        }
        if (Schema::hasColumn('lsg_catalog_core_characteristic_values', 'image_alt')) {
            $columns[] = 'image_alt';
        }

        return DB::table('lsg_catalog_core_characteristic_values')
            ->whereIn('characteristic_id', $characteristicIds)
            ->where('active', true)
            ->orderBy('position')
            ->orderBy('label')
            ->get($columns)
            ->groupBy('characteristic_id')
            ->map(fn ($rows) => $rows->map(fn ($row) => [
                'value' => $row->value,
                'label' => $row->label,
                'image_url' => $row->image_url ?? null,
                'image_alt' => $row->image_alt ?? null,
            ])->values()->all())
            ->all();
    }

    private function decodeAllowedValues($value): array
    {
        if (!$value) {
            return [];
        }

        if (is_string($value) && str_contains($value, '||')) {
            return collect(explode('||', $value))
                ->flatMap(fn ($part) => $this->decodeAllowedValues($part))
                ->unique()
                ->values()
                ->all();
        }

        $decoded = is_array($value) ? $value : json_decode((string) $value, true);

        return is_array($decoded) ? array_values(array_filter($decoded, fn ($item) => filled($item))) : [];
    }

    private function selectedCharacteristicValues(string $stage, ?Product $product): array
    {
        if ($stage !== 'marketing-content-manager' || !$product) {
            return [];
        }

        $product->loadMissing('productCharacteristics');

        return $product->productCharacteristics
            ->mapWithKeys(fn (ProductCharacteristicValue $value) => [(int) $value->characteristic_id => $value->value])
            ->all();
    }

    private function syncProductCharacteristics(Product $product, array $characteristicValues): void
    {
        if (!$characteristicValues) {
            return;
        }

        $validCharacteristics = ProductCharacteristic::query()
            ->where('is_active', true)
            ->get(['id', 'name', 'slug'])
            ->keyBy(fn (ProductCharacteristic $characteristic) => (int) $characteristic->id);
        $validCharacteristicIds = $validCharacteristics
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->all();
        $knownValues = Schema::hasTable('lsg_catalog_core_characteristic_values')
            ? DB::table('lsg_catalog_core_characteristic_values')
                ->whereIn('characteristic_id', array_keys($characteristicValues))
                ->where('active', true)
                ->get(['characteristic_id', 'value', 'label'])
                ->groupBy('characteristic_id')
                ->map(fn ($rows) => $rows
                    ->flatMap(fn ($row) => [(string) $row->value, (string) $row->label])
                    ->filter()
                    ->map(fn ($item) => mb_strtolower(trim((string) $item)))
                    ->unique()
                    ->values()
                    ->all())
                ->all()
            : [];
        $salesAddedValues = [];

        foreach ($characteristicValues as $characteristicId => $value) {
            $characteristicId = (int) $characteristicId;
            $values = is_array($value)
                ? collect($value)->map(fn ($item) => trim((string) $item))->filter()->values()->all()
                : collect([trim((string) $value)])->filter()->values()->all();

            $value = implode(', ', $values);

            if (!in_array($characteristicId, $validCharacteristicIds, true)) {
                continue;
            }

            $newValues = collect($values)
                ->reject(fn ($item) => in_array(mb_strtolower(trim((string) $item)), $knownValues[$characteristicId] ?? [], true))
                ->values()
                ->all();

            if ($newValues) {
                $characteristic = $validCharacteristics->get($characteristicId);
                $createdValues = $this->ensureCharacteristicValuesAvailable($product, $characteristicId, $newValues);
                if ($createdValues) {
                    $knownValues[$characteristicId] = array_values(array_unique(array_merge(
                        $knownValues[$characteristicId] ?? [],
                        collect($newValues)->map(fn ($item) => mb_strtolower(trim((string) $item)))->all(),
                        collect($createdValues)->pluck('value')->map(fn ($item) => mb_strtolower(trim((string) $item)))->all()
                    )));
                }

                $salesAddedValues[$characteristicId] = [
                    'characteristic_id' => $characteristicId,
                    'characteristic_name' => $characteristic?->name,
                    'characteristic_slug' => $characteristic?->slug,
                    'values' => $createdValues,
                    'status' => 'created_from_sales',
                ];
            }

            if ($value === '') {
                ProductCharacteristicValue::query()
                    ->where('product_id', $product->id)
                    ->where('characteristic_id', $characteristicId)
                    ->delete();
                continue;
            }

            ProductCharacteristicValue::query()->updateOrCreate(
                ['product_id' => $product->id, 'characteristic_id' => $characteristicId],
                ['value' => $value, 'value_json' => count($values) > 1 ? $values : null]
            );
        }

        $metadata = $product->metadata ?? [];
        if ($salesAddedValues) {
            $metadata['product_growth']['sales_added_characteristic_values'] = $salesAddedValues;
            unset($metadata['product_growth']['pending_characteristic_values']);
        } else {
            unset($metadata['product_growth']['pending_characteristic_values']);
        }
        $product->metadata = $metadata;
    }

    private function ensureCharacteristicValuesAvailable(Product $product, int $characteristicId, array $values): array
    {
        if (!Schema::hasTable('lsg_catalog_core_characteristic_values')) {
            return [];
        }

        $hasImageUrl = Schema::hasColumn('lsg_catalog_core_characteristic_values', 'image_url');
        $hasImageAlt = Schema::hasColumn('lsg_catalog_core_characteristic_values', 'image_alt');
        $created = [];

        foreach ($values as $label) {
            $label = trim((string) $label);
            if ($label === '') {
                continue;
            }

            $value = Str::slug($label, '_');
            $value = $value !== '' ? $value : 'value_' . substr(md5($label), 0, 8);
            $existing = DB::table('lsg_catalog_core_characteristic_values')
                ->where('characteristic_id', $characteristicId)
                ->where(function ($query) use ($value, $label): void {
                    $query->where('value', $value)->orWhere('label', $label);
                })
                ->first();

            if ($existing) {
                $value = (string) $existing->value;
                $created[] = [
                    'value' => $value,
                    'label' => (string) $existing->label,
                    'created' => false,
                ];
                $this->appendCharacteristicValueToProductCategories($product, $characteristicId, $value);

                continue;
            }

            $payload = [
                'characteristic_id' => $characteristicId,
                'value' => $value,
                'label' => $label,
                'position' => ((int) DB::table('lsg_catalog_core_characteristic_values')->where('characteristic_id', $characteristicId)->max('position')) + 1,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if ($hasImageUrl) {
                $payload['image_url'] = null;
            }

            if ($hasImageAlt) {
                $payload['image_alt'] = null;
            }

            DB::table('lsg_catalog_core_characteristic_values')->insert($payload);
            $created[] = [
                'value' => $value,
                'label' => $label,
                'created' => true,
            ];
            $this->appendCharacteristicValueToProductCategories($product, $characteristicId, $value);
        }

        return $created;
    }

    private function appendCharacteristicValueToProductCategories(Product $product, int $characteristicId, string $value): void
    {
        if (!Schema::hasTable('lsg_catalog_category_characteristics') || !Schema::hasColumn('lsg_catalog_category_characteristics', 'allowed_values')) {
            return;
        }

        $categoryIds = collect(data_get($product->metadata ?? [], 'product_growth.category_ids_by_store', []))
            ->map(fn ($categoryId) => (int) $categoryId)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (!$categoryIds) {
            return;
        }

        DB::table('lsg_catalog_category_characteristics')
            ->whereIn('store_category_id', $categoryIds)
            ->where('characteristic_id', $characteristicId)
            ->get(['id', 'allowed_values'])
            ->each(function ($row) use ($value): void {
                $allowedValues = $this->decodeAllowedValues($row->allowed_values ?? null);

                if (!$allowedValues || in_array($value, array_map('strval', $allowedValues), true)) {
                    return;
                }

                $allowedValues[] = $value;
                DB::table('lsg_catalog_category_characteristics')
                    ->where('id', $row->id)
                    ->update([
                        'allowed_values' => json_encode(array_values(array_unique($allowedValues))),
                        'updated_at' => now(),
                    ]);
            });
    }

    private function catalogOptionList(string $table, string $pivotTable, string $pivotKey, ?Product $product): array
    {
        try {
            if (!Schema::hasTable($table)) {
                return [];
            }

            $query = DB::table($table)
                ->where('active', true)
                ->orderBy('name');

            $catalogStoreIds = $this->catalogStoreIdsForProduct($product);
            if ($catalogStoreIds && Schema::hasTable($pivotTable)) {
                $scopedIds = DB::table($pivotTable)
                    ->whereIn('store_id', $catalogStoreIds)
                    ->pluck($pivotKey)
                    ->unique()
                    ->all();

                if ($scopedIds) {
                    $query->whereIn('id', $scopedIds);
                }
            }

            return $query->limit(500)->pluck('name', 'id')->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function catalogStoreIdsForProduct(?Product $product): array
    {
        if (!$product || !Schema::hasTable('catalog_stores')) {
            return [];
        }

        return $product->storeProducts
            ->map(fn ($storeProduct) => $storeProduct->store)
            ->filter()
            ->flatMap(function ($store) {
                return DB::table('catalog_stores')
                    ->where(function ($query) use ($store): void {
                        $query->where('name', $store->name);

                        if (!empty($store->domain)) {
                            $query->orWhere('domain', $store->domain);
                        }

                        if (!empty($store->slug)) {
                            $query->orWhere('code', $store->slug);
                        }
                    })
                    ->pluck('id');
            })
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    private function catalogCategoriesForProduct(?Product $product): array
    {
        if (!$product || !Schema::hasTable('catalog_store_categories') || !Schema::hasTable('catalog_store_category_lang') || !Schema::hasTable('catalog_stores')) {
            return [];
        }

        $store = $product->storeProducts->first()?->store;
        if (!$store) {
            return [];
        }

        $catalogStore = DB::table('catalog_stores')
            ->where(function ($query) use ($store): void {
                $query->where('name', $store->name);

                if (!empty($store->domain)) {
                    $query->orWhere('domain', $store->domain);
                }

                if (!empty($store->slug)) {
                    $query->orWhere('code', $store->slug);
                }
            })
            ->first();

        if (!$catalogStore) {
            return [];
        }

        return DB::table('catalog_store_categories as c')
            ->leftJoin('catalog_store_category_lang as cl', function ($join): void {
                $join->on('cl.store_category_id', '=', 'c.id')
                    ->where('cl.locale', 'pt');
            })
            ->where('c.store_id', $catalogStore->id)
            ->where('c.active', true)
            ->select('c.id', 'c.parent_id', 'c.position', 'c.code', 'cl.name')
            ->orderBy('c.parent_id')
            ->orderBy('c.position')
            ->orderBy('cl.name')
            ->get()
            ->map(fn ($category) => [
                'id' => (int) $category->id,
                'parent_id' => $category->parent_id ? (int) $category->parent_id : null,
                'name' => $category->name ?: ($category->code ?: 'Categoria #' . $category->id),
                'position' => (int) $category->position,
            ])
            ->values()
            ->all();
    }

    private function enrichPurchaseMasterData(array $data): array
    {
        $data['manufacturer_name'] = $this->catalogName('catalog_core_manufacturers', $data['manufacturer_id'] ?? null);
        $data['supplier_name'] = $this->catalogName('catalog_core_suppliers', $data['supplier_id'] ?? null);
        $data['category_name'] = $this->catalogCategoryName($data['category_id'] ?? null);

        return $data;
    }

    private function catalogName(string $table, mixed $id): ?string
    {
        $id = (int) ($id ?? 0);
        if (!$id || !Schema::hasTable($table)) {
            return null;
        }

        return DB::table($table)->where('id', $id)->value('name');
    }

    private function catalogCategoryName(mixed $id): ?string
    {
        $id = (int) ($id ?? 0);
        if (!$id || !Schema::hasTable('catalog_store_categories') || !Schema::hasTable('catalog_store_category_lang')) {
            return null;
        }

        $category = DB::table('catalog_store_categories as c')
            ->leftJoin('catalog_store_category_lang as cl', function ($join): void {
                $join->on('cl.store_category_id', '=', 'c.id')
                    ->where('cl.locale', 'pt');
            })
            ->where('c.id', $id)
            ->select('c.code', 'cl.name')
            ->first();

        return $category ? ($category->name ?: $category->code) : null;
    }

    private function workflowAreas(): array
    {
        return config('product-core.workflow_areas.areas', []);
    }

    private function stageArea(string $stage): string
    {
        return config('product-core.workflow_areas.stage_area_map.' . $stage) ?? 'supporting';
    }

    private function authenticatedUserIsAdmin(): bool
    {
        $userId = auth()->id();

        if (!$userId) {
            return false;
        }

        try {
            if (!Schema::hasTable('permission_roles') || !Schema::hasTable('permission_user_role')) {
                return false;
            }

            return DB::table('permission_roles')
                ->join('permission_user_role', 'permission_roles.id', '=', 'permission_user_role.permission_role_id')
                ->where('permission_user_role.user_id', $userId)
                ->whereIn('permission_roles.slug', ['super-admin', 'admin'])
                ->where('permission_roles.is_active', true)
                ->whereNull('permission_roles.deleted_at')
                ->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    private function workQueue(string $area): array
    {
        if ($area === 'admin') {
            return [
                'new' => Product::with(['brand', 'supplier', 'storeProducts.store', 'assets'])
                    ->whereIn('status', ['in_review', 'approved'])
                    ->latest()
                    ->limit(12)
                    ->get(),
                'corrections' => Product::with(['brand', 'supplier', 'storeProducts.store', 'assets'])
                    ->where('status', 'in_review')
                    ->latest()
                    ->limit(12)
                    ->get()
                    ->filter(fn (Product $product) => $this->hasAnyRejectedArea($product)),
            ];
        }

        $products = Product::with(['brand', 'supplier', 'storeProducts.store', 'assets'])
            ->whereNotIn('status', ['synced', 'archived'])
            ->latest()
            ->limit(80)
            ->get();

        return [
            'new' => $products
                ->filter(fn (Product $product) => in_array($this->reviewStatus($product, $area), ['pending', 'submitted', 'resubmitted'], true))
                ->take(12)
                ->values(),
            'corrections' => $products
                ->filter(fn (Product $product) => $this->reviewStatus($product, $area) === 'rejected')
                ->take(12)
                ->values(),
        ];
    }

    private function reviewStatus(Product $product, string $area): string
    {
        return data_get($product->metadata ?? [], 'department_reviews.' . $area . '.status', 'pending');
    }

    private function hasAnyRejectedArea(Product $product): bool
    {
        $workflowAreaKeys = array_keys($this->workflowAreas());

        foreach (data_get($product->metadata ?? [], 'department_reviews', []) as $areaKey => $review) {
            if (!in_array($areaKey, $workflowAreaKeys, true)) {
                continue;
            }

            if (($review['status'] ?? null) === 'rejected') {
                return true;
            }
        }

        return false;
    }

    private function markAreaSubmitted(array &$metadata, string $stage): void
    {
        $area = $this->stageArea($stage);

        if ($area === 'admin') {
            return;
        }

        $currentStatus = data_get($metadata, 'department_reviews.' . $area . '.status', 'pending');
        $metadata['department_reviews'][$area]['status'] = $currentStatus === 'rejected' ? 'resubmitted' : 'submitted';
        $metadata['department_reviews'][$area]['submitted_by'] = auth()->id();
        $metadata['department_reviews'][$area]['submitted_at'] = now()->toDateTimeString();
    }

    private function areaReviewStatus(array $area, array $items): string
    {
        $expectedItems = array_keys($area['items'] ?? []);
        $reviewed = collect($expectedItems)->mapWithKeys(fn ($key) => [$key => $items[$key]['status'] ?? null]);

        if ($reviewed->contains('rejected')) {
            return 'rejected';
        }

        if ($reviewed->filter(fn ($status) => $status === 'approved')->count() === count($expectedItems)) {
            return 'approved';
        }

        return 'in_review';
    }

    private function productReviewStatus(array $reviews, array $workflowAreas): string
    {
        foreach ($workflowAreas as $areaKey => $area) {
            if (($reviews[$areaKey]['status'] ?? null) === 'rejected') {
                return 'in_review';
            }
        }

        foreach (array_keys($workflowAreas) as $areaKey) {
            if (($reviews[$areaKey]['status'] ?? null) !== 'approved') {
                return 'in_review';
            }
        }

        return 'approved';
    }

    private function validatedStageData(Request $request, string $stage): array
    {
        if ($stage === 'store-brand-manager') {
            return $request->validate([
                'reference' => ['nullable', 'string', 'max:120'],
                'internal_sku' => ['nullable', 'string', 'max:120'],
                'product_type' => ['nullable', 'in:simple,combination,pack'],
                'manufacturer_id' => ['nullable', 'integer', 'exists:catalog_core_manufacturers,id'],
                'supplier_id' => ['nullable', 'integer', 'exists:catalog_core_suppliers,id'],
                'ean13' => ['nullable', 'string', 'max:80'],
                'allow_backorder' => ['nullable', 'boolean'],
                'combinations' => ['nullable', 'array', 'max:100'],
                'combinations.*.attributes' => ['nullable', 'array'],
                'combinations.*.attributes.*' => ['nullable', 'string', 'max:120'],
                'combinations.*.condition' => ['nullable', 'string', 'max:80'],
                'combinations.*.language' => ['nullable', 'string', 'max:80'],
                'combinations.*.finish' => ['nullable', 'string', 'max:80'],
                'combinations.*.sku' => ['nullable', 'string', 'max:120'],
                'combinations.*.stock' => ['nullable', 'integer', 'min:0'],
                'combinations.*.price' => ['nullable', 'numeric', 'min:0'],
                'pack_components' => ['nullable', 'array', 'max:100'],
                'pack_components.*.reference' => ['nullable', 'string', 'max:120'],
                'pack_components.*.quantity' => ['nullable', 'numeric', 'min:0.0001'],
            ]);
        }

        if ($stage === 'logistics-manager') {
            return $request->validate([
                'ean13' => ['nullable', 'string', 'max:80'],
                'stock_quantity' => ['nullable', 'integer', 'min:0'],
                'weight' => ['nullable', 'numeric', 'min:0'],
                'width' => ['nullable', 'numeric', 'min:0'],
                'height' => ['nullable', 'numeric', 'min:0'],
                'depth' => ['nullable', 'numeric', 'min:0'],
                'package_quantity' => ['nullable', 'numeric', 'min:0'],
                'package_type' => ['nullable', 'string', 'max:120'],
                'shipping_class' => ['nullable', 'string', 'max:120'],
                'measurements_verified' => ['nullable', 'boolean'],
                'has_shipping_restrictions' => ['nullable', 'boolean'],
                'carrier_exclusions' => ['nullable', 'string', 'max:1000'],
                'handling_notes' => ['nullable', 'string', 'max:1000'],
            ]);
        }

        if ($stage === 'brand-compliance-manager') {
            return $request->validate([
                'purchase_price_original' => ['nullable', 'numeric', 'min:0'],
                'supplier_currency' => ['nullable', 'string', 'max:3'],
                'currency_rate_to_eur' => ['nullable', 'numeric', 'min:0'],
                'purchase_price' => ['nullable', 'numeric', 'min:0'],
                'supplier_recommended_sale_price' => ['nullable', 'numeric', 'min:0'],
                'base_sale_price' => ['nullable', 'numeric', 'min:0'],
                'desired_discount' => ['nullable', 'numeric', 'min:0', 'max:100'],
                'tax_rule' => ['nullable', 'string', 'in:' . implode(',', array_keys(config('product-core.finance.vat_rules', ['pt_vat_23' => []])))],
                'nc_code' => ['nullable', 'string', 'max:80'],
            ]);
        }

        if ($stage === 'marketing-content-manager') {
            return $request->validate([
                'recommended_products' => ['nullable', 'array', 'max:6'],
                'recommended_products.*' => ['nullable', 'string', 'max:120'],
                'upsell_bundles' => ['nullable', 'array', 'max:6'],
                'upsell_bundles.*.title' => ['nullable', 'string', 'max:160'],
                'upsell_bundles.*.discount' => ['nullable', 'numeric', 'min:0', 'max:100'],
                'upsell_bundles.*.products' => ['nullable', 'array', 'max:6'],
                'upsell_bundles.*.products.*' => ['nullable', 'string', 'max:120'],
                'sales_channels' => ['nullable', 'array'],
                'sales_channels.*' => ['string', 'max:80'],
                'characteristics' => ['nullable', 'array'],
                'characteristics.*' => ['nullable'],
            ]);
        }

        if ($stage === 'creative-asset-manager') {
            return $request->validate([
                'cover_image_upload' => ['nullable', 'image', 'max:10240'],
                'main_image_upload' => ['nullable', 'image', 'max:10240'],
                'gallery_images' => ['nullable', 'array', 'max:30'],
                'gallery_images.*' => ['nullable', 'image', 'max:10240'],
                'combination_images' => ['nullable', 'array', 'max:100'],
                'combination_images.*' => ['nullable', 'image', 'max:10240'],
                'combination_image_labels' => ['nullable', 'string', 'max:12000'],
                'youtube_video_code' => ['nullable', 'string', 'max:120'],
                'social_square_upload' => ['nullable', 'image', 'max:10240'],
                'social_story_upload' => ['nullable', 'image', 'max:10240'],
                'social_reel_youtube_code' => ['nullable', 'string', 'max:120'],
                'asset_notes' => ['nullable', 'string', 'max:1500'],
            ]);
        }

        return $request->validate([
            'status' => ['nullable', 'string', 'max:80'],
            'notes' => ['nullable', 'string'],
            'output' => ['nullable', 'string', 'max:500'],
        ]);
    }

    private function stageRouteName(array $stage, string $suffix): string
    {
        return Str::replaceLast('dashboard', $suffix, $stage['route']);
    }

    private function updatePurchaseStoreData(Product $product, array $data): void
    {
        $storeProducts = $product->storeProducts()->get();

        foreach ($storeProducts as $storeProduct) {
            $overrides = $storeProduct->store_overrides ?? [];
            if (is_string($overrides)) {
                $overrides = json_decode($overrides, true) ?: [];
            }

            $overrides['purchase_content'] = $data;
            $storeProduct->fill([
                'store_overrides' => $overrides,
                'sync_status' => $storeProduct->sync_status === 'synced' ? 'needs_resync' : $storeProduct->sync_status,
            ])->save();
        }
    }

    private function updateFinanceStoreData(Product $product, array $data): void
    {
        $storeProducts = $product->storeProducts()->get();

        foreach ($storeProducts as $storeProduct) {
            $overrides = $storeProduct->store_overrides ?? [];
            if (is_string($overrides)) {
                $overrides = json_decode($overrides, true) ?: [];
            }

            $overrides['finance_content'] = $data;
            $storeProduct->fill([
                'cost_price' => $data['purchase_price'] ?? $storeProduct->cost_price,
                'store_overrides' => $overrides,
                'sync_status' => $storeProduct->sync_status === 'synced' ? 'needs_resync' : $storeProduct->sync_status,
            ])->save();
        }
    }

    private function updateLogisticsStoreData(Product $product, array $data): void
    {
        $storeProducts = $product->storeProducts()->get();

        foreach ($storeProducts as $storeProduct) {
            $overrides = $storeProduct->store_overrides ?? [];
            if (is_string($overrides)) {
                $overrides = json_decode($overrides, true) ?: [];
            }

            $overrides['logistics_content'] = $data;
            $storeProduct->fill([
                'stock_quantity' => $data['stock_quantity'] ?? $storeProduct->stock_quantity,
                'store_overrides' => $overrides,
                'sync_status' => $storeProduct->sync_status === 'synced' ? 'needs_resync' : $storeProduct->sync_status,
            ])->save();
        }
    }

    private function updateStoreSales(Product $product, array $data): void
    {
        $storeProducts = $product->storeProducts()->get();

        foreach ($storeProducts as $storeProduct) {
            $overrides = $storeProduct->store_overrides ?? [];
            if (is_string($overrides)) {
                $overrides = json_decode($overrides, true) ?: [];
            }

            $overrides['sales_content'] = $data;
            $storeProduct->fill([
                'store_overrides' => $overrides,
                'sync_status' => $storeProduct->sync_status === 'synced' ? 'needs_resync' : $storeProduct->sync_status,
            ])->save();
        }
    }

    private function updateMarketingContent(Product $product, array $data): void
    {
        $storeProducts = $product->storeProducts()->get();

        foreach ($storeProducts as $storeProduct) {
            $overrides = $storeProduct->store_overrides ?? [];
            if (is_string($overrides)) {
                $overrides = json_decode($overrides, true) ?: [];
            }

            $overrides['marketing_content'] = $data;
            $storeProduct->fill([
                'short_description' => $data['short_description'] ?: $storeProduct->short_description,
                'description' => $data['long_description'] ?: $storeProduct->description,
                'seo_title' => $data['seo_title'] ?: $storeProduct->seo_title,
                'seo_description' => $data['seo_description'] ?: $storeProduct->seo_description,
                'store_overrides' => $overrides,
                'sync_status' => $storeProduct->sync_status === 'synced' ? 'needs_resync' : $storeProduct->sync_status,
            ])->save();
        }
    }

    private function syncCreativeAssets(Product $product, array $data, string $stage): array
    {
        $storeProduct = $product->storeProducts()->first();
        $storeId = $storeProduct?->store_id;
        $cleanData = [
            'asset_notes' => $data['asset_notes'] ?? null,
            'combination_image_labels' => $data['combination_image_labels'] ?? null,
            'youtube_video_code' => $data['youtube_video_code'] ?? null,
            'social_reel_youtube_code' => $data['social_reel_youtube_code'] ?? null,
            'uploaded_assets' => [],
        ];

        $assets = [
            'cover_image' => [$data['cover_image_upload'] ?? null, 'Imagem de capa', 1, 'image'],
            'main_image' => [$data['main_image_upload'] ?? null, 'Imagem principal', 2, 'image'],
            'social_square' => [$data['social_square_upload'] ?? null, 'Imagem redes sociais quadrada', 3, 'image'],
            'social_story' => [$data['social_story_upload'] ?? null, 'Imagem redes sociais story', 4, 'image'],
        ];

        foreach ($assets as $role => [$file, $title, $sort, $type]) {
            if (!$file) {
                continue;
            }

            $url = $this->storeCreativeAssetUpload($product, $file, $role);
            $this->upsertAsset($product, $storeProduct?->id, $storeId, $type, $role, $title, $url, $sort, [
                'stage' => $stage,
                'notes' => $data['asset_notes'] ?? null,
            ]);

            $cleanData[$role . '_url'] = $url;
            $cleanData['uploaded_assets'][$role] = $url;
        }

        $galleryFiles = collect($data['gallery_images'] ?? [])
            ->filter()
            ->values();

        $galleryUrls = [];
        foreach ($galleryFiles as $index => $file) {
            $url = $this->storeCreativeAssetUpload($product, $file, 'gallery_' . ($index + 1));
            $this->upsertAsset($product, $storeProduct?->id, $storeId, 'image', 'gallery_' . ($index + 1), 'Imagem galeria ' . ($index + 1), $url, 10 + $index, [
                'stage' => $stage,
                'gallery_index' => $index + 1,
            ]);
            $galleryUrls[] = $url;
        }
        $cleanData['gallery_urls'] = $galleryUrls;

        $combinationLabels = collect(preg_split('/\r\n|\r|\n/', (string) ($data['combination_image_labels'] ?? '')))
            ->map(fn ($row) => trim($row))
            ->filter()
            ->values();
        $combinationFiles = collect($data['combination_images'] ?? [])->filter()->values();
        $combinationUrls = [];

        foreach ($combinationFiles as $index => $file) {
            $label = $combinationLabels[$index] ?? 'Combinacao ' . ($index + 1);
            $url = $this->storeCreativeAssetUpload($product, $file, 'combination_' . ($index + 1));
            $this->upsertAsset($product, $storeProduct?->id, $storeId, 'image', 'combination_' . ($index + 1), 'Imagem ' . ($label ?: 'combinacao ' . ($index + 1)), $url, 30 + $index, [
                'stage' => $stage,
                'combination_label' => $label,
                'notes' => $data['asset_notes'] ?? null,
            ]);
            $combinationUrls[] = ['label' => $label, 'url' => $url];
        }

        $cleanData['combination_images'] = $combinationUrls;

        return $cleanData;
    }

    private function storeCreativeAssetUpload(Product $product, $file, string $role): string
    {
        $extension = $file->getClientOriginalExtension() ?: $file->extension() ?: 'bin';
        $filename = Str::slug($role) . '-' . Str::random(10) . '.' . Str::lower($extension);
        $path = $file->storeAs('product-growth/products/' . $product->id . '/marketing', $filename, 'public');

        return '/storage/' . ltrim(str_replace('\\', '/', $path), '/');
    }

    private function upsertAsset(Product $product, ?int $storeProductId, ?int $storeId, string $type, string $role, string $title, string $url, int $sortOrder, array $metadata): void
    {
        DB::table('lsg_catalog_product_assets')->updateOrInsert(
            ['product_id' => $product->id, 'asset_role' => $role, 'source_module' => 'product-growth'],
            [
                'store_product_id' => $storeProductId,
                'store_id' => $storeId,
                'asset_type' => $type,
                'source_id' => $product->id,
                'title' => $title,
                'file_path' => ltrim($url, '/'),
                'public_url' => $url,
                'mime_type' => $type === 'video' ? 'video/mp4' : 'image/jpeg',
                'extension' => $type === 'video' ? 'mp4' : (pathinfo(parse_url($url, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION) ?: 'jpg'),
                'language' => 'pt',
                'is_public' => true,
                'is_primary' => in_array($role, ['cover_image', 'main_image'], true),
                'is_syncable_to_prestashop' => true,
                'is_syncable_to_webcatalogue' => true,
                'approval_status' => 'pending_review',
                'brand_compliance_status' => 'not_checked',
                'quality_score' => 80,
                'sort_order' => $sortOrder,
                'metadata' => json_encode($metadata),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function assetData(Product $product): array
    {
        return $product->assets
            ->filter(fn ($asset) => $asset->source_module === 'product-growth')
            ->mapWithKeys(fn ($asset) => [$asset->asset_role => $this->normalisePublicAssetUrl($asset->public_url)])
            ->all();
    }

    private function normalisePublicAssetUrl(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH);

        if ($path && Str::contains($path, '/storage/')) {
            return Str::after($path, '/storage/') ? '/storage/' . Str::after($path, '/storage/') : $path;
        }

        if (Str::startsWith($url, 'storage/')) {
            return '/' . $url;
        }

        return $url;
    }

    private function logStageUpdate(Product $product, string $stage, array $stageMeta, array $data): void
    {
        if (!Schema::hasTable('lsg_catalog_logs')) {
            return;
        }

        DB::table('lsg_catalog_logs')->insert([
            'loggable_type' => Product::class,
            'loggable_id' => $product->id,
            'event' => $this->stageKey($stage),
            'severity' => 'info',
            'title' => $stageMeta['title'] . ' atualizado',
            'message' => $this->stageContentSummary($stage, $data),
            'payload' => json_encode($data),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function normalizeFinanceData(Product $product, array $data): array
    {
        $vatRules = config('product-core.finance.vat_rules', ['pt_vat_23' => ['rate' => 0.23]]);
        $defaultVatRule = config('product-core.finance.default_vat_rule', 'pt_vat_23');
        $supplier = $this->supplierFinanceContext($product);

        $currency = $supplier['currency'] ?? 'EUR';
        $data['supplier_currency'] = strtoupper((string) $currency);
        $data['tax_rule'] = array_key_exists($data['tax_rule'] ?? '', $vatRules) ? $data['tax_rule'] : $defaultVatRule;

        $configuredRate = $supplier['currency_rate_to_eur'] ?? 1;
        $rate = (float) $configuredRate;
        if ($rate <= 0 && $configuredRate !== null) {
            $rate = (float) $configuredRate;
        }
        $data['currency_rate_to_eur'] = $rate > 0 ? $rate : 1;

        $original = $data['purchase_price_original'] ?? null;
        if (($data['purchase_price'] ?? null) === null && $original !== null) {
            $data['purchase_price'] = round((float) $original * (float) $data['currency_rate_to_eur'], 4);
        }
        if (($data['base_sale_price'] ?? null) === null && ($data['supplier_recommended_sale_price'] ?? null) !== null) {
            $data['base_sale_price'] = round((float) $data['supplier_recommended_sale_price'] * (float) $data['currency_rate_to_eur'], 4);
        }

        return $data;
    }

    private function supplierFinanceContext(Product $product): array
    {
        $supplierId = $product->supplier_id ?: data_get($product->metadata ?? [], 'product_growth.supplier_id');
        if (!$supplierId || !Schema::hasTable('catalog_core_suppliers')) {
            return ['currency' => 'EUR', 'currency_rate_to_eur' => 1];
        }

        $supplier = DB::table('catalog_core_suppliers')->where('id', $supplierId)->first();
        if (!$supplier) {
            return ['currency' => 'EUR', 'currency_rate_to_eur' => 1];
        }

        return [
            'currency' => strtoupper((string) ($supplier->currency ?? 'EUR')),
            'currency_rate_to_eur' => $this->currencyRateToEur((string) ($supplier->currency ?? 'EUR')),
        ];
    }

    private function currencyRateToEur(string $currency): float
    {
        $currency = strtoupper($currency ?: 'EUR');
        if (!Schema::hasTable('catalog_currencies')) {
            return $currency === 'EUR' ? 1 : (float) data_get(config('product-core.finance.currencies', []), $currency . '.rate_to_eur', 1);
        }

        $rate = DB::table('catalog_currencies')
            ->where('iso_code', $currency)
            ->where('active', true)
            ->value('conversion_rate_to_eur');

        return (float) ($rate ?: 1);
    }

    private function normalizePurchaseStructures(array $data): array
    {
        $productType = $data['product_type'] ?? 'simple';

        $data['combinations'] = collect($data['combinations'] ?? [])
            ->filter(function ($row): bool {
                if (!is_array($row)) {
                    return false;
                }

                $attributes = collect($row['attributes'] ?? [])->filter(fn ($value) => filled($value));

                return collect(['condition', 'language', 'finish', 'sku', 'stock', 'price'])
                    ->contains(fn ($field) => filled($row[$field] ?? null))
                    || $attributes->isNotEmpty();
            })
            ->map(function (array $row): array {
                $attributes = collect($row['attributes'] ?? [])
                    ->map(fn ($value) => is_string($value) ? trim($value) : $value)
                    ->filter(fn ($value) => filled($value))
                    ->all();

                foreach (['condition', 'language', 'finish'] as $legacyField) {
                    if (filled($row[$legacyField] ?? null) && !array_key_exists($legacyField, $attributes)) {
                        $attributes[$legacyField] = $row[$legacyField];
                    }
                }

                $sku = $row['sku'] ?? null;
                if (!filled($sku)) {
                    $sku = $this->combinationSkuFromAttributes($data['reference'] ?? null, $attributes);
                }

                return [
                    'attributes' => $attributes,
                    'condition' => $attributes['condition'] ?? null,
                    'language' => $attributes['language'] ?? null,
                    'finish' => $attributes['finish'] ?? null,
                    'sku' => $sku,
                    'stock' => isset($row['stock']) && $row['stock'] !== '' ? (int) $row['stock'] : null,
                    'price' => isset($row['price']) && $row['price'] !== '' ? (float) $row['price'] : null,
                ];
            })
            ->values()
            ->all();

        $data['pack_components'] = collect($data['pack_components'] ?? [])
            ->filter(function ($row): bool {
                return is_array($row)
                    && (filled($row['reference'] ?? null) || filled($row['quantity'] ?? null));
            })
            ->map(fn (array $row) => [
                'reference' => Str::upper(trim((string) ($row['reference'] ?? ''))),
                'quantity' => isset($row['quantity']) && $row['quantity'] !== '' ? (float) $row['quantity'] : null,
            ])
            ->filter(fn (array $row) => filled($row['reference']) && ($row['quantity'] ?? 0) > 0)
            ->values()
            ->all();

        if ($productType !== 'combination') {
            $data['combinations'] = [];
        }

        if ($productType !== 'pack') {
            $data['pack_components'] = [];
        }

        return $data;
    }

    private function validatePurchaseStructureRules(Product $product, array $data): void
    {
        $currentType = $product->product_type ?: 'simple';
        $requestedType = $data['product_type'] ?? $currentType;

        if (in_array($product->status, $this->approvedProductStatuses(), true) && $requestedType !== $currentType) {
            throw ValidationException::withMessages([
                'product_type' => 'Nao e possivel alterar o tipo de produto depois de aprovado.',
            ]);
        }

        if ($requestedType !== 'pack' || empty($data['pack_components'])) {
            return;
        }

        $references = collect($data['pack_components'])
            ->pluck('reference')
            ->filter()
            ->map(fn ($reference) => Str::upper(trim((string) $reference)))
            ->unique()
            ->values();

        if ($references->isEmpty()) {
            return;
        }

        $storeIds = $this->productStoreIds($product);

        $approvedProducts = Product::query()
            ->whereIn('status', $this->approvedProductStatuses())
            ->where('id', '!=', $product->id)
            ->when($storeIds->isNotEmpty(), function ($query) use ($storeIds): void {
                $query->whereHas('storeProducts', fn ($storeQuery) => $storeQuery->whereIn('store_id', $storeIds->all()));
            })
            ->where(function ($query) use ($references): void {
                $query->whereIn(DB::raw('UPPER(reference)'), $references->all())
                    ->orWhereIn(DB::raw('UPPER(internal_sku)'), $references->all())
                    ->orWhereNotNull('metadata');
            })
            ->get(['id', 'name', 'reference', 'internal_sku', 'metadata']);

        $allowedReferences = $approvedProducts
            ->flatMap(function (Product $item): array {
                $references = array_filter([$item->reference, $item->internal_sku]);
                $combinations = collect(data_get($item->metadata ?? [], 'department_content.store-brand-manager.combinations', []))
                    ->filter(fn ($combination) => is_array($combination) && filled($combination['sku'] ?? null))
                    ->pluck('sku')
                    ->all();

                return array_merge($references, $combinations);
            })
            ->map(fn ($reference) => Str::upper(trim((string) $reference)))
            ->filter()
            ->unique()
            ->values();

        $selfReferences = collect(array_filter([$product->reference, $product->internal_sku]))
            ->merge(collect(data_get($product->metadata ?? [], 'department_content.store-brand-manager.combinations', []))
                ->filter(fn ($combination) => is_array($combination) && filled($combination['sku'] ?? null))
                ->pluck('sku'))
            ->map(fn ($reference) => Str::upper(trim((string) $reference)))
            ->filter()
            ->unique()
            ->values();

        $selfMatches = $references->intersect($selfReferences);
        if ($selfMatches->isNotEmpty()) {
            throw ValidationException::withMessages([
                'pack_components' => 'O produto nao pode ser componente do seu proprio pack: ' . $selfMatches->implode(', ') . '.',
            ]);
        }

        $missing = $references->diff($allowedReferences);
        if ($missing->isNotEmpty()) {
            throw ValidationException::withMessages([
                'pack_components' => 'Os componentes do pack devem existir, estar aprovados e pertencer a pelo menos uma das mesmas lojas do pack: ' . $missing->implode(', ') . '.',
            ]);
        }
    }

    private function normalizeSalesStructures(array $data): array
    {
        $data['recommended_products'] = collect($data['recommended_products'] ?? [])
            ->map(fn ($reference) => Str::upper(trim((string) $reference)))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $data['upsell_bundles'] = collect($data['upsell_bundles'] ?? [])
            ->filter(fn ($row) => is_array($row))
            ->map(function (array $row): array {
                return [
                    'title' => trim((string) ($row['title'] ?? '')),
                    'discount' => isset($row['discount']) && $row['discount'] !== '' ? (float) $row['discount'] : null,
                    'products' => collect($row['products'] ?? [])
                        ->map(fn ($reference) => Str::upper(trim((string) $reference)))
                        ->filter()
                        ->unique()
                        ->values()
                        ->all(),
                ];
            })
            ->filter(fn (array $row) => $row['title'] !== '' || $row['discount'] !== null || !empty($row['products']))
            ->values()
            ->all();

        unset($data['cross_sell_products'], $data['competitor_price_reference'], $data['upsell_products']);
        $data['sales_channels'] = ['online'];

        return $data;
    }

    private function validateSalesStructureRules(Product $product, array $data): void
    {
        $recommended = collect($data['recommended_products'] ?? []);

        if ($recommended->count() > 6) {
            throw ValidationException::withMessages([
                'recommended_products' => 'Sales pode indicar no maximo 6 produtos recomendados.',
            ]);
        }

        $bundleReferences = collect($data['upsell_bundles'] ?? [])
            ->flatMap(fn ($bundle) => $bundle['products'] ?? []);

        $references = $recommended
            ->merge($bundleReferences)
            ->filter()
            ->map(fn ($reference) => Str::upper(trim((string) $reference)))
            ->unique()
            ->values();

        if ($references->isEmpty()) {
            return;
        }

        $allowedReferences = $this->approvedSameStoreReferences($product, $references);
        $missing = $references->diff($allowedReferences);

        if ($missing->isNotEmpty()) {
            throw ValidationException::withMessages([
                'recommended_products' => 'Os produtos sugeridos por Sales devem existir, estar aprovados e pertencer a pelo menos uma das mesmas lojas: ' . $missing->implode(', ') . '.',
            ]);
        }
    }

    private function approvedSameStoreReferences(Product $product, $references)
    {
        $references = collect($references)
            ->map(fn ($reference) => Str::upper(trim((string) $reference)))
            ->filter()
            ->unique()
            ->values();

        if ($references->isEmpty()) {
            return collect();
        }

        $storeIds = $this->productStoreIds($product);

        return Product::query()
            ->whereIn('status', $this->approvedProductStatuses())
            ->where('id', '!=', $product->id)
            ->when($storeIds->isNotEmpty(), function ($query) use ($storeIds): void {
                $query->whereHas('storeProducts', fn ($storeQuery) => $storeQuery->whereIn('store_id', $storeIds->all()));
            })
            ->where(function ($query) use ($references): void {
                $query->whereIn(DB::raw('UPPER(reference)'), $references->all())
                    ->orWhereIn(DB::raw('UPPER(internal_sku)'), $references->all());
            })
            ->get(['reference', 'internal_sku'])
            ->flatMap(fn (Product $item) => array_filter([$item->reference, $item->internal_sku]))
            ->map(fn ($reference) => Str::upper(trim((string) $reference)))
            ->filter()
            ->unique()
            ->values();
    }

    private function buildDescriptionGenerationRequest(Product $product, array $salesData): array
    {
        $metadata = $product->metadata ?? [];
        $purchaseData = data_get($metadata, 'department_content.store-brand-manager', []);
        $financeData = data_get($metadata, 'department_content.brand-compliance-manager', []);
        $categoryData = $this->selectedCatalogCategoriesForProduct($product);
        $characteristics = $this->selectedCharacteristicValues('marketing-content-manager', $product);
        $productType = $purchaseData['product_type'] ?? $product->product_type ?? 'simple';

        return [
            'status' => 'pending',
            'source_stage' => 'marketing-content-manager',
            'requested_at' => now()->toDateTimeString(),
            'product_id' => $product->id,
            'product_type' => $productType,
            'prompt' => $this->descriptionPromptForProduct($product, $purchaseData, $salesData, $characteristics, $categoryData),
            'payload' => [
                'product' => [
                    'name' => $product->name,
                    'reference' => $purchaseData['reference'] ?? $product->reference,
                    'internal_sku' => $purchaseData['internal_sku'] ?? $product->internal_sku,
                    'ean13' => $purchaseData['ean13'] ?? $product->ean,
                    'type' => $productType,
                ],
                'stores_categories' => $categoryData,
                'purchase' => [
                    'manufacturer' => $purchaseData['manufacturer_name'] ?? data_get($metadata, 'product_growth.manufacturer_name'),
                    'supplier' => $purchaseData['supplier_name'] ?? data_get($metadata, 'product_growth.supplier_name'),
                    'allow_backorder' => !empty($purchaseData['allow_backorder']),
                    'combinations' => $purchaseData['combinations'] ?? [],
                    'pack_components' => $purchaseData['pack_components'] ?? [],
                ],
                'finance' => [
                    'purchase_price_original' => $financeData['purchase_price_original'] ?? null,
                    'supplier_currency' => $financeData['supplier_currency'] ?? 'EUR',
                    'currency_rate_to_eur' => $financeData['currency_rate_to_eur'] ?? 1,
                    'purchase_price' => $financeData['purchase_price'] ?? $purchaseData['purchase_price'] ?? $product->base_cost,
                    'supplier_recommended_sale_price' => $financeData['supplier_recommended_sale_price'] ?? null,
                    'base_sale_price' => $financeData['base_sale_price'] ?? $purchaseData['base_sale_price'] ?? $product->base_price,
                    'desired_discount' => $financeData['desired_discount'] ?? null,
                    'tax_rule' => $financeData['tax_rule'] ?? null,
                    'nc_code' => $financeData['nc_code'] ?? null,
                ],
                'sales' => $salesData,
                'characteristics' => $characteristics,
            ],
        ];
    }

    private function descriptionPromptForProduct(Product $product, array $purchaseData, array $salesData, $characteristics, array $categoryData): string
    {
        $categoryPrompt = $this->descriptionPromptFromSelectedCategory($product);
        $basePrompt = $categoryPrompt ?: (string) config('product-core.description_prompts.default', 'Cria uma descricao de produto para ecommerce com foco comercial, SEO e clareza.');

        $dataBlock = [
            'product_name' => $product->name,
            'reference' => $purchaseData['reference'] ?? $product->reference,
            'type' => $purchaseData['product_type'] ?? $product->product_type,
            'stores_categories' => $categoryData,
            'manufacturer' => $purchaseData['manufacturer_name'] ?? null,
            'base_sale_price' => data_get($product->metadata ?? [], 'department_content.brand-compliance-manager.base_sale_price', $product->base_price),
            'sales_channels' => $salesData['sales_channels'] ?? [],
            'recommended_products' => $salesData['recommended_products'] ?? [],
            'upsell_bundles' => $salesData['upsell_bundles'] ?? [],
            'characteristics' => $characteristics,
            'combinations' => $purchaseData['combinations'] ?? [],
            'pack_components' => $purchaseData['pack_components'] ?? [],
        ];

        return trim($basePrompt) . "\n\nDados estruturados para substituir variaveis do prompt:\n" . json_encode($dataBlock, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    private function descriptionPromptFromSelectedCategory(Product $product): ?string
    {
        $categoryId = collect(data_get($product->metadata ?? [], 'product_growth.category_ids_by_store', []))
            ->filter()
            ->first();

        if (!$categoryId || !Schema::hasTable('catalog_store_category_lang')) {
            return null;
        }

        $hasAiPrompt = Schema::hasColumn('catalog_store_category_lang', 'ai_prompt');
        if (!$hasAiPrompt) {
            return null;
        }

        return DB::table('catalog_store_category_lang')
            ->where('store_category_id', (int) $categoryId)
            ->where('locale', 'pt')
            ->value('ai_prompt');
    }

    private function combinationSkuFromAttributes(?string $baseReference, array $attributes): ?string
    {
        $base = Str::upper(trim((string) $baseReference));
        $base = preg_replace('/\s+/', '-', $base);
        $codes = [];

        foreach ($attributes as $attribute => $value) {
            $code = $this->combinationAttributeCode((string) $attribute, (string) $value);
            if ($code) {
                $codes[] = $code;
            }
        }

        $parts = array_values(array_filter(array_merge([$base], $codes)));

        return $parts ? implode('-', $parts) : null;
    }

    private function combinationAttributeCode(string $attribute, string $value): ?string
    {
        $value = Str::of($value)
            ->lower()
            ->replace(["'", '’'], '')
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->toString();

        if ($value === '') {
            return null;
        }

        $maps = [
            'condition' => [
                'mint' => 'M',
                'near_mint' => 'NM',
                'excellent' => 'EX',
                'good' => 'GD',
                'light_played' => 'LP',
                'played' => 'PL',
                'poor' => 'PO',
            ],
            'language' => [
                'english' => 'EN',
                'portuguese' => 'PT',
                'spanish' => 'ES',
                'french' => 'FR',
                'german' => 'DE',
                'italian' => 'IT',
                'japanese' => 'JP',
                'korean' => 'KR',
                'russian' => 'RU',
                'simplified_chinese' => 'SC',
                'traditional_chinese' => 'TC',
            ],
            'finish' => [
                'non_foil' => 'NF',
                'traditional_foil' => 'TF',
                'etched_foil' => 'EF',
                'glossy' => 'GL',
            ],
            'version_treatment' => [
                'regular' => 'REG',
                'extended_art' => 'EA',
                'borderless' => 'BL',
                'showcase' => 'SH',
                'retro_frame' => 'RF',
                'full_art' => 'FA',
                'textured_foil' => 'TXF',
                'surge_foil' => 'SF',
                'galaxy_foil' => 'GF',
                'confetti_foil' => 'CF',
                'serialized' => 'SER',
                'promo' => 'PR',
                'prerelease_promo' => 'PP',
                'buy_a_box_promo' => 'BAB',
                'bundle_promo' => 'BP',
                'store_championship_promo' => 'SCP',
            ],
        ];

        if (isset($maps[$attribute][$value])) {
            return $maps[$attribute][$value];
        }

        return Str::of($value)
            ->explode('_')
            ->filter()
            ->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))
            ->implode('');
    }

    private function stageContentSummary(string $stage, array $data): string
    {
        if ($stage === 'store-brand-manager') {
            return 'Purchase atualizado: referencia, tipo de produto, fabricante, fornecedor, EAN e estrutura comercial quando aplicavel.';
        }

        if ($stage === 'marketing-content-manager') {
            return 'Sales atualizado: caracteristicas e relacoes de venda.';
        }

        if ($stage === 'logistics-manager') {
            return 'Logistics atualizado: stock, EAN fallback, medidas e restricoes de envio.';
        }

        if ($stage === 'brand-compliance-manager') {
            return 'Finance atualizado: precos com conversao EUR pelo fornecedor, VAT e NC code.';
        }

        if ($stage === 'creative-asset-manager') {
            return 'Marketing atualizado: foto de capa, galeria, imagens de combinacoes, videos e criativos sociais.';
        }

        return $data['notes'] ?? $data['output'] ?? 'Etapa atualizada.';
    }

    private function stageKey(string $stage): string
    {
        return str_replace('-', '_', $stage);
    }

    private function stages(): array
    {
        return [
            'workflow-manager' => [
                'title' => 'Workflow Manager',
                'route' => 'product_growth.workflow_manager.dashboard',
                'icon' => 'fa-solid fa-diagram-project',
                'department' => 'Admin / Operacoes',
                'summary' => 'Coordena a timeline geral, prioridades, bloqueios e passagens entre departamentos.',
                'output' => 'Workflow validado e proxima equipa definida.',
            ],
            'store-brand-manager' => [
                'title' => 'Purchase Workspace',
                'route' => 'product_growth.store_brand_manager.dashboard',
                'icon' => 'fa-solid fa-cart-shopping',
                'department' => 'Purchase',
                'summary' => 'Valida ou corrige referencia, identifica o tipo de produto e preenche fabricante, fornecedor, EAN e estrutura de combinacoes ou packs quando aplicavel.',
                'output' => 'Dados de compra prontos para validacao admin.',
            ],
            'brand-compliance-manager' => [
                'title' => 'Finance Workspace',
                'route' => 'product_growth.brand_compliance_manager.dashboard',
                'icon' => 'fa-solid fa-file-invoice-dollar',
                'department' => 'Finance',
                'summary' => 'Valida precos, conversao EUR pela currency do fornecedor, VAT, NC code e dados financeiros necessarios para o produto.',
                'output' => 'Dados financeiros prontos para validacao admin.',
            ],
            'marketing-content-manager' => [
                'title' => 'Sales Workspace',
                'route' => 'product_growth.marketing_content_manager.dashboard',
                'icon' => 'fa-solid fa-tags',
                'department' => 'Sales',
                'summary' => 'Consulta a estrategia de preco e define caracteristicas e produtos relacionados.',
                'output' => 'Dados comerciais prontos para validacao admin.',
            ],
            'creative-asset-manager' => [
                'title' => 'Marketing Workspace',
                'route' => 'product_growth.creative_asset_manager.dashboard',
                'icon' => 'fa-solid fa-bullhorn',
                'department' => 'Marketing',
                'summary' => 'Prepara foto de capa, galeria, imagens de combinacoes, videos e criativos para redes sociais.',
                'output' => 'Assets visuais prontos para validacao admin.',
            ],
            'logistics-manager' => [
                'title' => 'Logistics Workspace',
                'route' => 'product_growth.logistics_manager.dashboard',
                'icon' => 'fa-solid fa-truck-fast',
                'department' => 'Logistics',
                'summary' => 'Define stock, EAN fallback, peso, medidas, embalagem, restricoes e exclusoes de transportadores.',
                'output' => 'Dados logisticos operacionais, fora do ciclo bloqueante de criacao.',
            ],
            'webcatalogue-premium-layer' => [
                'title' => 'WebCatalogue Premium Layer',
                'route' => 'product_growth.webcatalogue_premium_layer.dashboard',
                'icon' => 'fa-solid fa-cube',
                'department' => 'WebCatalogue',
                'summary' => 'Prepara camada premium, conteudos enriquecidos, 3D/AR e recursos de apresentacao.',
                'output' => 'Experiencia premium pronta para WebCatalogue.',
            ],
            'product-buzz-manager' => [
                'title' => 'Product Buzz Manager',
                'route' => 'product_growth.product_buzz_manager.dashboard',
                'icon' => 'fa-solid fa-bullhorn',
                'department' => 'Marketing / Buzz',
                'summary' => 'Planeia buzz inicial, sinalizacao comercial, campanhas teaser e ativacao.',
                'output' => 'Plano de lancamento e comunicacao.',
            ],
            'ai-ads-manager' => [
                'title' => 'AI Ads Manager',
                'route' => 'product_growth.ai_ads_manager.dashboard',
                'icon' => 'fa-solid fa-rectangle-ad',
                'department' => 'Ads / AI',
                'summary' => 'Gera variantes para ads, audiencias, textos e assets de campanha.',
                'output' => 'Campanhas preparadas para validacao.',
            ],
            'product-evolution-manager' => [
                'title' => 'Product Evolution Manager',
                'route' => 'product_growth.product_evolution_manager.dashboard',
                'icon' => 'fa-solid fa-chart-line',
                'department' => 'Produto / Performance',
                'summary' => 'Acompanha iteracoes, melhorias, feedback e evolucao do produto.',
                'output' => 'Plano de evolucao e proximas alteracoes.',
            ],
            'publisher-export-manager' => [
                'title' => 'Publisher & Export Manager',
                'route' => 'product_growth.publisher_export_manager.dashboard',
                'icon' => 'fa-solid fa-upload',
                'department' => 'Publicacao',
                'summary' => 'Controla publicacao, exportacoes, elegibilidade e handoff para integracoes.',
                'output' => 'Produto pronto para exportacao/publicacao.',
            ],
            'prestashop-bridge' => [
                'title' => 'PrestaShop 9 Bridge',
                'route' => 'product_growth.prestashop_bridge.dashboard',
                'icon' => 'fa-solid fa-plug-circle-bolt',
                'department' => 'Integracoes',
                'summary' => 'Cria, atualiza, sincroniza e faz soft delete de produtos e recursos no PS9.',
                'output' => 'Sincronizacao PS9 controlada.',
            ],
            'performance-manager' => [
                'title' => 'Performance Manager',
                'route' => 'product_growth.performance_manager.dashboard',
                'icon' => 'fa-solid fa-gauge-high',
                'department' => 'Reporting / Performance',
                'summary' => 'Mede performance, qualidade, problemas de sync e impacto comercial.',
                'output' => 'Indicadores e alertas para melhoria continua.',
            ],
        ];
    }
}
