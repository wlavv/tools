<?php

namespace Modules\LSG\ProductGrowth\ProductCore\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\LSG\ProductGrowth\ProductCore\Http\Requests\StoreProductRequest;
use Modules\LSG\ProductGrowth\ProductCore\Models\Product;
use Modules\LSG\ProductGrowth\ProductCore\Models\ProductCharacteristic;
use Modules\LSG\ProductGrowth\ProductCore\Models\ProductCharacteristicValue;
use Modules\LSG\ProductGrowth\ProductCore\Models\ProductStore;
use Modules\LSG\ProductGrowth\ProductCore\Services\ProductCoreLogService;
use Modules\LSG\ProductGrowth\ProductCore\Services\ProductDescriptionAiService;
use Modules\LSG\ProductGrowth\ProductCore\Services\ProductQualityScoreService;
use Modules\LSG\ProductGrowth\ProductCore\Services\ProductSkuGenerator;

class ProductCoreProductController extends BaseProductCoreController
{
    public function index(Request $request)
    {
        $this->prepareProductCorePage('Anuncios em criacao', [
            ['label' => 'Anuncios', 'url' => null],
        ]);
        $this->addNewProductAction();

        $products = Product::with(['brand','supplier','storeProducts.store','assets'])
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('q'), fn($q) => $q->where(function($query) use ($request) {
                $term = '%' . $request->q . '%';
                $query->where('name', 'like', $term)->orWhere('internal_sku', 'like', $term)->orWhere('reference', 'like', $term)->orWhere('ean', 'like', $term);
            }))
            ->latest()
            ->paginate(config('product-core.pagination', 25))
            ->withQueryString();

        return $this->view('product-core::products.index', compact('products'));
    }

    public function create()
    {
        $this->prepareProductCorePage('Novo anuncio', [
            ['label' => 'Anuncios', 'url' => route('product_growth.product_core.products.index')],
            ['label' => 'Novo anuncio', 'url' => null],
        ]);
        $this->addBackToProductsAction();
        $this->addSaveAction();

        return $this->view('product-core::products.create', $this->formData());
    }

    public function store(StoreProductRequest $request, ProductSkuGenerator $skuGenerator, ProductQualityScoreService $quality, ProductCoreLogService $log): RedirectResponse
    {
        $data = $request->validated();
        $storeIds = $data['store_ids'] ?? [];
        $categoryIds = $data['category_ids'] ?? [];
        $assignedWorkflow = $data['assigned_workflow'] ?? 'standard_product_announcement';
        unset($data['store_ids']);
        unset($data['category_ids']);
        unset($data['assigned_workflow']);
        $data['internal_sku'] = $data['internal_sku'] ?: ($data['reference'] ?: $skuGenerator->generate());
        $data['status'] = $data['status'] ?? 'draft';
        $data['product_type'] = $data['product_type'] ?? 'simple';
        $data['created_by'] = auth()->id();

        $product = Product::create($data);
        $metadata = $product->metadata ?? [];
        $metadata['product_growth']['assigned_workflow'] = $assignedWorkflow;
        $metadata['product_growth']['workflow_created_at'] = now()->toDateTimeString();
        $product->forceFill(['metadata' => $metadata])->save();

        foreach ($storeIds as $storeId) {
            $product->storeProducts()->create([
                'store_id' => $storeId,
                'name' => $product->name,
                'description' => $product->description,
                'sale_price' => $product->base_price,
                'cost_price' => $product->base_cost,
                'active_for_sale' => false,
                'sync_to_prestashop' => false,
            ]);
        }
        $this->syncDefaultStoreCategories($product, $categoryIds, $storeIds);
        $quality->update($product);
        $log->log($product, 'created', 'Anuncio criado', $product->name);

        return redirect()->route('product_growth.product_core.products.show', $product)->with('success', 'Anuncio criado com sucesso.');
    }

    public function show(Product $product)
    {
        $this->prepareProductCorePage($product->name, [
            ['label' => 'Anuncios', 'url' => route('product_growth.product_core.products.index')],
            ['label' => $product->name, 'url' => null],
        ]);
        $this->addEditProductAction($product);
        $this->addApproveProductAction($product);
        $this->addReadyToSyncAction($product);

        $product->load(['brand','supplier','storeProducts.store','assets','productAttributes.attribute','productCharacteristics.characteristic']);
        $aiDescription = $this->aiDescriptionData($product);

        return $this->view('product-core::products.show', [
            'product' => $product,
            'aiDescription' => $aiDescription,
            'hideProductGrowthNavigation' => true,
        ]);
    }

    public function edit(Product $product)
    {
        $this->prepareProductCorePage('Editar anuncio', [
            ['label' => 'Anuncios', 'url' => route('product_growth.product_core.products.index')],
            ['label' => $product->name, 'url' => route('product_growth.product_core.products.show', $product)],
            ['label' => 'Editar', 'url' => null],
        ]);
        $this->addProductShowAction($product);
        $this->addSaveAction();

        $product->load(['brand', 'supplier', 'storeProducts.store', 'assets', 'productCharacteristics.characteristic']);
        return $this->view('product-core::products.edit', array_merge($this->formData($product), [
            'product' => $product,
            'hideProductGrowthNavigation' => true,
        ]));
    }

    public function update(StoreProductRequest $request, Product $product, ProductQualityScoreService $quality, ProductCoreLogService $log): RedirectResponse
    {
        $data = $request->validated();
        $storeIds = $data['store_ids'] ?? [];
        $categoryIds = $data['category_ids'] ?? [];
        $assignedWorkflow = $data['assigned_workflow'] ?? null;
        unset($data['store_ids']);
        unset($data['category_ids']);
        unset($data['assigned_workflow']);
        if (empty($data['internal_sku']) && !empty($data['reference'])) {
            $data['internal_sku'] = $data['reference'];
        }
        $data['updated_by'] = auth()->id();

        $product->update($data);
        if ($assignedWorkflow !== null) {
            $metadata = $product->metadata ?? [];
            $metadata['product_growth']['assigned_workflow'] = $assignedWorkflow;
            $metadata['product_growth']['workflow_updated_at'] = now()->toDateTimeString();
            $product->forceFill(['metadata' => $metadata])->save();
        }

        foreach ($storeIds as $storeId) {
            $product->storeProducts()->firstOrCreate(['store_id' => $storeId], ['name' => $product->name, 'sale_price' => $product->base_price]);
        }
        $this->syncDefaultStoreCategories($product, $categoryIds, $storeIds);
        $quality->update($product);
        $log->log($product, 'updated', 'Anuncio atualizado', $product->name);

        return redirect()->route('product_growth.product_core.products.show', $product)->with('success', 'Anuncio atualizado com sucesso.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->update(['status' => 'archived', 'is_active' => false]);
        $product->delete();
        return redirect()->route('product_growth.product_core.products.index')->with('success', 'Anuncio arquivado.');
    }

    public function approve(Product $product, ProductCoreLogService $log): RedirectResponse
    {
        $product->update(['status' => 'approved']);
        $log->log($product, 'approved', 'Anuncio aprovado', 'Anuncio aprovado para venda/sincronizacao futura.');
        return back()->with('success', 'Anuncio aprovado.');
    }

    public function markReadyToSync(Product $product, ProductCoreLogService $log): RedirectResponse
    {
        $product->update(['status' => 'ready_to_sync']);
        $product->storeProducts()->where('sync_to_prestashop', true)->update(['sync_status' => 'ready_to_sync']);
        $log->log($product, 'ready_to_sync', 'Anuncio marcado para sincronizacao');
        return back()->with('success', 'Anuncio marcado para sincronizacao.');
    }

    public function archive(Product $product): RedirectResponse
    {
        $product->update(['status' => 'archived', 'is_active' => false]);
        $product->storeProducts()->update(['active_for_sale' => false, 'sync_status' => 'needs_resync']);
        return back()->with('success', 'Anuncio arquivado e marcado para desativacao nos canais.');
    }

    public function generateDescription(Request $request, Product $product, ProductDescriptionAiService $descriptionAi): RedirectResponse
    {
        $data = $request->validate([
            'provider' => ['required', 'in:anthropic,gemini,openai'],
            'prompt' => ['required', 'string', 'min:10', 'max:3000'],
            'ai_category_ref' => ['nullable', 'string', 'max:80'],
        ]);

        $category = $this->resolveAiCategoryFromRef($data['ai_category_ref'] ?? null);

        try {
            $descriptionAi->generate($product, $data['provider'], $data['prompt'], auth()->id(), $category);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Nao foi possivel gerar a descricao: ' . $e->getMessage());
        }

        return redirect()
            ->route('product_growth.product_core.products.show', $product)
            ->with('success', 'Descricao gerada por IA e aplicada ao anuncio.');
    }

    private function formData(?Product $product = null): array
    {
        $stores = ProductStore::where('site_type', 'store')->where('status', 'active')->orderBy('name')->get();
        $storeIds = $stores->pluck('id')->all();

        return [
            'stores' => $stores,
            'categoriesByStore' => $this->catalogManagerCategoriesForStores($stores),
            'selectedCategoryByStore' => $this->selectedCategoryByStore($product),
        ];
    }

    private function selectedCharacteristicValues(?Product $product): array
    {
        if (!$product) {
            return [];
        }

        $product->loadMissing('productCharacteristics');

        return $product->productCharacteristics
            ->mapWithKeys(fn (ProductCharacteristicValue $value) => [(int) $value->characteristic_id => $value->value])
            ->all();
    }

    private function syncProductCharacteristics(Product $product, array $characteristicValues): void
    {
        $validCharacteristicIds = ProductCharacteristic::query()
            ->where('is_active', true)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        foreach ($characteristicValues as $characteristicId => $value) {
            $characteristicId = (int) $characteristicId;
            $values = is_array($value)
                ? collect($value)->map(fn ($item) => trim((string) $item))->filter()->values()->all()
                : collect([trim((string) $value)])->filter()->values()->all();

            $value = implode(', ', $values);

            if (!in_array($characteristicId, $validCharacteristicIds, true)) {
                continue;
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
    }

    private function expectedCharacteristicsForProduct(?Product $product)
    {
        if (
            !$product
            || !Schema::hasTable('lsg_catalog_category_characteristics')
            || !Schema::hasTable('lsg_catalog_core_characteristics')
        ) {
            return collect();
        }

        $categoryIds = collect($this->selectedCategoryByStore($product))
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

    private function selectedCategoryByStore(?Product $product): array
    {
        if (!$product) {
            return [];
        }

        return collect(data_get($product->metadata ?? [], 'product_growth.category_ids_by_store', []))
            ->mapWithKeys(fn ($categoryId, $storeId) => [(int) $storeId => (int) $categoryId])
            ->filter()
            ->all();
    }

    private function syncDefaultStoreCategories(Product $product, array $categoryIdsByStore, array $selectedStoreIds): void
    {
        $selectedStoreIds = collect($selectedStoreIds)->map(fn ($id) => (int) $id)->filter()->unique()->all();
        $validCategoryIdsByStore = $this->validCatalogCategoryIdsByLsgStore($selectedStoreIds, $categoryIdsByStore);

        $metadata = $product->metadata ?? [];
        $metadata['product_growth']['category_ids_by_store'] = $validCategoryIdsByStore;
        $metadata['product_growth']['category_source'] = 'catalog_manager';
        $metadata['product_growth']['master_data_source'] = 'catalog_manager';
        $metadata['product_growth']['category_updated_at'] = now()->toDateTimeString();

        $product->forceFill(['metadata' => $metadata])->save();
    }

    private function aiDescriptionData(Product $product): array
    {
        $store = $product->storeProducts->first()?->store;
        $categories = collect();

        if ($store) {
            $categories = $this->catalogManagerCategoriesForStore($store);
        }

        $selectedCategoryId = $store
            ? (int) data_get($product->metadata ?? [], 'product_growth.category_ids_by_store.' . $store->id, 0)
            : 0;
        $selectedCategory = $selectedCategoryId
            ? $categories->first(fn (array $category) => (int) str_replace('catalog:', '', (string) $category['id']) === $selectedCategoryId)
            : null;

        return [
            'store' => $store,
            'categories' => $categories->values(),
            'default_prompt' => $selectedCategory['prompt'] ?? $this->resolveDescriptionPrompt($store?->slug, null),
            'selected_category_ref' => $selectedCategory['id'] ?? null,
        ];
    }

    private function resolveDescriptionPrompt(?string $storeSlug, ?array $category): string
    {
        $default = (string) config('product-core.description_prompts.default', '');

        if (!$storeSlug) {
            return ($category['ai_prompt'] ?? null) ?: ($category['description'] ?? null) ?: $default;
        }

        $storePrompt = (string) config("product-core.description_prompts.stores.{$storeSlug}.default", $default);

        if (!$category) {
            return $storePrompt;
        }

        return (string) (
            ($category['ai_prompt'] ?? null)
            ?: config("product-core.description_prompts.stores.{$storeSlug}.categories." . ($category['slug'] ?? ''))
            ?: ($category['description'] ?? null)
            ?: $storePrompt
        );
    }

    private function catalogManagerCategoriesForStore(object $store)
    {
        if (!Schema::hasTable('catalog_store_categories') || !Schema::hasTable('catalog_store_category_lang') || !Schema::hasTable('catalog_stores')) {
            return collect();
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
            return collect();
        }

        $hasAiPrompt = Schema::hasColumn('catalog_store_category_lang', 'ai_prompt');

        return DB::table('catalog_store_categories as c')
            ->leftJoin('catalog_store_category_lang as cl', function ($join): void {
                $join->on('cl.store_category_id', '=', 'c.id')
                    ->where('cl.locale', 'pt');
            })
            ->where('c.store_id', $catalogStore->id)
            ->where('c.active', true)
            ->select([
                'c.id',
                'c.parent_id',
                'c.code',
                'c.position',
                'cl.name',
                'cl.description',
                'cl.link_rewrite',
                $hasAiPrompt ? 'cl.ai_prompt' : DB::raw('NULL as ai_prompt'),
            ])
            ->orderBy('c.parent_id')
            ->orderBy('c.position')
            ->orderBy('cl.name')
            ->get()
            ->map(fn ($category) => [
                'id' => 'catalog:' . $category->id,
                'parent_id' => $category->parent_id ? 'catalog:' . $category->parent_id : null,
                'name' => $category->name ?: ($category->code ?: 'Categoria #' . $category->id),
                'slug' => $category->link_rewrite ?: Str::slug((string) ($category->name ?: $category->code ?: $category->id)),
                'description' => $category->description,
                'ai_prompt' => $category->ai_prompt,
                'prompt' => $this->resolveDescriptionPrompt($store->slug ?? null, [
                    'slug' => $category->link_rewrite ?: Str::slug((string) ($category->name ?: $category->code ?: $category->id)),
                    'description' => $category->description,
                    'ai_prompt' => $category->ai_prompt,
                ]),
            ]);
    }

    private function catalogManagerCategoriesForStores($stores)
    {
        return collect($stores)
            ->mapWithKeys(fn ($store) => [(int) $store->id => $this->catalogManagerCategoriesForStore($store)->map(fn (array $category) => (object) [
                'id' => (int) str_replace('catalog:', '', (string) $category['id']),
                'parent_id' => $category['parent_id'] ? (int) str_replace('catalog:', '', (string) $category['parent_id']) : null,
                'name' => $category['name'],
            ])]);
    }

    private function validCatalogCategoryIdsByLsgStore(array $selectedStoreIds, array $categoryIdsByStore): array
    {
        return collect($selectedStoreIds)
            ->mapWithKeys(function (int $storeId) use ($categoryIdsByStore) {
                $categoryId = (int) ($categoryIdsByStore[$storeId] ?? 0);

                if (!$categoryId) {
                    return [];
                }

                $store = ProductStore::query()->find($storeId);
                if (!$store) {
                    return [];
                }

                $validIds = $this->catalogManagerCategoriesForStore($store)
                    ->map(fn (array $category) => (int) str_replace('catalog:', '', (string) $category['id']))
                    ->all();

                return in_array($categoryId, $validIds, true) ? [$storeId => $categoryId] : [];
            })
            ->all();
    }

    private function resolveAiCategoryFromRef(?string $ref): ?array
    {
        if (!$ref || !str_contains($ref, ':')) {
            return null;
        }

        [$source, $id] = explode(':', $ref, 2);
        $id = (int) $id;

        if ($source !== 'catalog' || !Schema::hasTable('catalog_store_categories') || !Schema::hasTable('catalog_store_category_lang')) {
            return null;
        }

        $hasAiPrompt = Schema::hasColumn('catalog_store_category_lang', 'ai_prompt');

        $category = DB::table('catalog_store_categories as c')
            ->leftJoin('catalog_store_category_lang as cl', function ($join): void {
                $join->on('cl.store_category_id', '=', 'c.id')
                    ->where('cl.locale', 'pt');
            })
            ->where('c.id', $id)
            ->select([
                'c.id',
                'c.code',
                'cl.name',
                'cl.description',
                'cl.link_rewrite',
                $hasAiPrompt ? 'cl.ai_prompt' : DB::raw('NULL as ai_prompt'),
            ])
            ->first();

        return $category ? [
            'source' => 'catalog_manager',
            'id' => $category->id,
            'name' => $category->name ?: ($category->code ?: 'Categoria #' . $category->id),
            'slug' => $category->link_rewrite ?: Str::slug((string) ($category->name ?: $category->code ?: $category->id)),
            'description' => $category->description,
            'ai_prompt' => $category->ai_prompt,
        ] : null;
    }
}
