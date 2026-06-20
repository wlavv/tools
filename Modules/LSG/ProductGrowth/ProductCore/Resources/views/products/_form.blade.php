@php
    $product = $product ?? null;
    $selectedStores = old(
        'store_ids',
        $product && $product->relationLoaded('storeProducts')
            ? $product->storeProducts->pluck('store_id')->all()
            : []
    );
    $categoriesByStore = collect($categoriesByStore ?? []);
    $selectedCategoryByStore = collect(old('category_ids', $selectedCategoryByStore ?? []))
        ->mapWithKeys(fn($categoryId, $storeId) => [(int) $storeId => (int) $categoryId])
        ->all();
    $assignedWorkflow = old('assigned_workflow', data_get($product?->metadata ?? [], 'product_growth.assigned_workflow', 'standard_product_announcement'));
@endphp

<div class="pc-form-grid pc-product-admin-form-grid">
    <div class="pc-form-span-7">
        <label class="pc-label">Nome</label>
        <input class="pc-input" name="name" value="{{ old('name', $product?->name) }}" required>
    </div>

    <div class="pc-form-span-2">
        <label class="pc-label">SKU</label>
        <input class="pc-input" id="product_sku" name="reference" value="{{ old('reference', $product?->reference) }}" placeholder="SKU comercial/base">
    </div>

    <div class="pc-form-span-2">
        <label class="pc-label">SKU interno</label>
        <input class="pc-input" id="product_internal_sku" name="internal_sku" value="{{ old('internal_sku', $product?->internal_sku) }}" placeholder="auto com o SKU">
    </div>

    <div class="pc-form-span-1">
        <label class="pc-label">Estado</label>
        <label class="pc-switch-field pc-switch-field--compact">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product?->is_active ?? false)) data-active-state-toggle>
            <span></span>
        </label>
    </div>

    <div class="pc-form-span-3">
        <label class="pc-label" for="product_type">Tipo produto</label>
        <select class="pc-select" id="product_type" name="product_type">
            <?php foreach (['simple' => 'Produto simples', 'combination' => 'Produto com combinacoes', 'pack' => 'Produto pack'] as $value => $label) { ?>
                <option value="{{ $value }}" @selected((string) old('product_type', $product?->product_type ?? 'simple') === (string) $value)>{{ $label }}</option>
            <?php } ?>
        </select>
    </div>

    <div class="pc-form-span-3">
        <label class="pc-label" for="product_status">Status</label>
        <select class="pc-select" id="product_status" name="status">
            <?php foreach (['draft' => 'Rascunho', 'in_review' => 'Em validacao', 'approved' => 'Aprovado', 'ready_to_sync' => 'Pronto a publicar', 'blocked' => 'Bloqueado'] as $value => $label) { ?>
                <option value="{{ $value }}" @selected((string) old('status', $product?->status ?? 'draft') === (string) $value)>{{ $label }}</option>
            <?php } ?>
        </select>
    </div>

    <div class="pc-form-span-6">
        <label class="pc-label" for="assigned_workflow">Workflow atribuido</label>
        <select class="pc-select" id="assigned_workflow" name="assigned_workflow">
            <?php foreach (['standard_product_announcement' => 'Anuncio standard: Admin > Purchase > Sales > Marketing > Admin', 'tcg_single_workflow' => 'TCG single: Admin > Purchase > Sales > Marketing > Admin'] as $value => $label) { ?>
                <option value="{{ $value }}" @selected((string) $assignedWorkflow === (string) $value)>{{ $label }}</option>
            <?php } ?>
        </select>
    </div>

    <div class="pc-form-grid-1">
        <label class="pc-label">Lojas onde o produto existe</label>
        <div class="pc-field-panel">
            <div class="pc-store-logo-grid">
                @if($stores->isNotEmpty())
                    @foreach($stores as $store)
                        @php
                            $settings = is_array($store->settings ?? null) ? $store->settings : (json_decode($store->settings ?? '[]', true) ?: []);
                            $storeLogo = data_get($settings, 'logo_url') ?? data_get($settings, 'logo') ?? data_get($settings, 'image') ?? data_get($settings, 'icon');
                        @endphp
                        <label class="pc-store-logo-option">
                            <input type="checkbox" name="store_ids[]" value="{{ $store->id }}" data-product-store-toggle="{{ $store->id }}" @checked(in_array($store->id, $selectedStores))>
                            <span>
                                @if($storeLogo)
                                    <img src="{{ $storeLogo }}" alt="{{ $store->name }}">
                                @else
                                    <i class="fa-solid fa-store"></i>
                                @endif
                            </span>
                            <strong>{{ $store->name }}</strong>
                        </label>
                    @endforeach
                @else
                    <span class="text-muted">Sem lojas ativas.</span>
                @endif
            </div>
        </div>
    </div>

    <div class="pc-form-grid-1">
        <label class="pc-label">Categorias por loja</label>
        <div class="pc-field-panel pc-product-category-panel">
            @foreach($stores as $store)
                @php
                    $storeCategories = collect($categoriesByStore->get($store->id, collect()));
                    $storeCategoriesJson = $storeCategories->values()->toJson(JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
                @endphp
                <div
                    class="pc-product-category-row {{ in_array($store->id, $selectedStores) ? 'is-active' : 'is-hidden' }}"
                    data-product-category-store="{{ $store->id }}"
                    data-product-category-cascade
                    data-categories="{{ $storeCategoriesJson }}"
                    data-selected-category="{{ $selectedCategoryByStore[$store->id] ?? '' }}"
                >
                    <div>
                        <strong>{{ $store->name }}</strong>
                        <small>{{ in_array($store->id, $selectedStores) ? 'Loja selecionada' : 'Seleciona a loja para ativar esta categoria' }}</small>
                    </div>
                    <div class="pc-category-cascade">
                        <input
                            type="hidden"
                            name="category_ids[{{ $store->id }}]"
                            value="{{ $selectedCategoryByStore[$store->id] ?? '' }}"
                            data-product-category-final
                        >
                        <div class="pc-category-cascade__levels" data-product-category-levels></div>
                    </div>
                    <select class="pc-select pc-category-cascade__legacy" data-product-category-legacy @disabled(!in_array($store->id, $selectedStores))>
                        <option value="">Sem categoria definida</option>
                        @foreach($storeCategories as $category)
                            <option value="{{ $category->id }}" @selected(($selectedCategoryByStore[$store->id] ?? null) === (int) $category->id)>
                                {{ $category->parent_id ? '- ' : '' }}{{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endforeach
        </div>
    </div>
</div>
