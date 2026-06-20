@extends('product-core::layouts.module')

@php
    $isEditing = isset($product);
    $routeBase = \Illuminate\Support\Str::replaceLast('dashboard', '', $stage['route']);
    $editRoute = $routeBase . 'product.edit';
    $updateRoute = $routeBase . 'product.update';
    $currentStageData = $stageData ?? [];
    $currentAssetData = $assetData ?? [];
    $currentGalleryAssets = collect($currentAssetData)->filter(fn ($url, $role) => \Illuminate\Support\Str::startsWith((string) $role, 'gallery_'))->values();
    $currentCombinationAssets = collect($currentAssetData)->filter(fn ($url, $role) => \Illuminate\Support\Str::startsWith((string) $role, 'combination_'))->values();
    $workflowAreas = $workflowAreas ?? [];
    $currentArea = $currentArea ?? 'admin';
    $areaReviews = $areaReviews ?? [];
    $workQueue = $workQueue ?? ['new' => $products ?? collect(), 'corrections' => collect()];
    $currentAreaReview = $areaReviews[$currentArea] ?? [];
    $stageOptions = $stageOptions ?? [];
    $stageRoute = $stage['route'] ?? '';
    $isStoreBrandStage = $stageRoute === 'product_growth.store_brand_manager.dashboard';
    $isLogisticsStage = $stageRoute === 'product_growth.logistics_manager.dashboard';
    $isFinanceStage = $stageRoute === 'product_growth.brand_compliance_manager.dashboard';
    $isMarketingContentStage = $stageRoute === 'product_growth.marketing_content_manager.dashboard';
    $isCreativeAssetStage = $stageRoute === 'product_growth.creative_asset_manager.dashboard';
    $isGenericStage = !in_array($stageRoute, [
        'product_growth.store_brand_manager.dashboard',
        'product_growth.logistics_manager.dashboard',
        'product_growth.brand_compliance_manager.dashboard',
        'product_growth.marketing_content_manager.dashboard',
        'product_growth.creative_asset_manager.dashboard',
    ], true);
    $stageCategories = collect($stageOptions['categories'] ?? []);
    $stageCategoriesJson = $stageCategories->values()->toJson(JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $selectedStageCategory = old('category_id', $currentStageData['category_id'] ?? data_get($product->metadata ?? [], 'product_growth.category_id'));
    $combinationRows = old('combinations', $currentStageData['combinations'] ?? []);
    $combinationRows = is_array($combinationRows) ? array_values(array_filter($combinationRows, 'is_array')) : [];
    $combinationOptionSets = $stageOptions['combination_options'] ?? [];
    $combinationAttributes = collect($combinationOptionSets['attributes'] ?? [])->filter(fn($attribute) => filled($attribute['slug'] ?? null))->values();
    if ($combinationAttributes->isEmpty()) {
        $combinationAttributes = collect([
            ['slug' => 'condition', 'name' => 'Condition', 'values' => $combinationOptionSets['condition'] ?? []],
            ['slug' => 'language', 'name' => 'Language', 'values' => $combinationOptionSets['language'] ?? []],
            ['slug' => 'finish', 'name' => 'Finish', 'values' => $combinationOptionSets['finish'] ?? []],
        ]);
    }
    $combinationRows = $combinationRows ?: [[
        'attributes' => $combinationAttributes->mapWithKeys(fn($attribute) => [$attribute['slug'] => ''])->all(),
        'sku' => '',
        'stock' => '',
        'price' => '',
    ]];
    $combinationAttributeCount = max(1, $combinationAttributes->count());
    $packRows = old('pack_components', $currentStageData['pack_components'] ?? []);
    $packRows = is_array($packRows) ? array_values(array_filter($packRows, 'is_array')) : [];
    $packRows = $packRows ?: [['reference' => '', 'quantity' => 1]];
    $packProductSuggestions = collect($stageOptions['pack_product_suggestions'] ?? []);
    $packProductSuggestionsJson = $packProductSuggestions->values()->toJson(JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $salesProductSuggestions = collect($stageOptions['product_suggestions'] ?? []);
    $salesProductSuggestionsJson = $salesProductSuggestions->values()->toJson(JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $recommendedRows = old('recommended_products', $currentStageData['recommended_products'] ?? []);
    if (is_string($recommendedRows)) {
        $recommendedRows = preg_split('/\r\n|\r|\n|,/', $recommendedRows);
    }
    $recommendedRows = is_array($recommendedRows) ? array_values(array_filter($recommendedRows, fn($value) => filled($value))) : [];
    $recommendedRows = array_slice($recommendedRows, 0, 6);
    $upsellRows = old('upsell_bundles', $currentStageData['upsell_bundles'] ?? []);
    if (!is_array($upsellRows)) {
        $upsellRows = [];
    }
    $upsellRows = array_values(array_filter($upsellRows, 'is_array'));
@endphp

@section('module-content')
<?php if ((!$isEditing || $currentArea === 'admin') && $stageRoute !== 'product_growth.workflow_manager.dashboard') { ?>
<section class="product-core-card pc-panel">
    <div class="pc-panel-head">
        <div>
            <h2 class="pc-panel-title"><i class="{{ $stage['icon'] }} me-2"></i>{{ $stage['title'] }}</h2>
            <p class="pc-panel-subtitle">{{ $stage['summary'] }}</p>
        </div>
        <span class="pc-badge">{{ $stage['department'] }}</span>
    </div>

    <div class="pc-stage-grid">
        <div class="pc-stage-box"><small>Departamento</small><strong>{{ $stage['department'] }}</strong></div>
        <div class="pc-stage-box"><small>Output esperado</small><strong>{{ $stage['output'] }}</strong></div>
        <div class="pc-stage-box"><small>Produtos</small><strong>{{ $stats['products'] ?? 0 }}</strong></div>
        <div class="pc-stage-box"><small>Prontos sync</small><strong>{{ $stats['ready_to_sync'] ?? 0 }}</strong></div>
    </div>
</section>
<?php } ?>

<?php if ($isEditing) { ?>
    <?php if (($canSeeAdminTimeline ?? false) && isset($product)) { ?>
        @include('product-core::partials.product-timeline', [
            'productGrowthTimelineCurrentStage' => $stageKey,
            'productGrowthTimelineShowPreviewButton' => false,
        ])
    <?php } ?>

    <?php if ($currentArea !== 'admin') { ?>
        <div class="pc-department-edit-shell">
            <div class="pc-department-work-layout">
                <aside class="pc-department-work-preview">
                    <div class="product-core-card pc-panel">
                        @include('product-core::partials.product-preview-card', [
                            'productPreviewAssetData' => $currentAssetData,
                        ])
                    </div>
                    @include('product-core::partials.product-context-panel')
                </aside>
                <section class="product-core-card pc-panel pc-department-work-main">
    <?php } else { ?>
        <div class="pc-department-work-layout">
            <aside class="pc-department-work-preview">
                <div class="product-core-card pc-panel">
                    @include('product-core::partials.product-preview-card', [
                        'productPreviewAssetData' => $currentAssetData,
                    ])
                </div>
                @include('product-core::partials.product-context-panel')
            </aside>
            <section class="product-core-card pc-panel pc-department-work-main">
            <div class="pc-panel-head">
                <div>
                    <h2 class="pc-panel-title">{{ $product->name }}</h2>
                    <p class="pc-panel-subtitle">
                        {{ $product->internal_sku }} ·
                        {{ data_get($product->metadata ?? [], 'product_growth.manufacturer_name') ?? $product->brand?->name ?? 'Sem fabricante' }} ·
                        {{ data_get($product->metadata ?? [], 'product_growth.supplier_name') ?? $product->supplier?->name ?? 'Sem fornecedor' }}
                    </p>
                </div>
                <a href="{{ route('product_growth.product_core.products.show', $product) }}" class="lsg-action-btn lsg-action-btn--primary lsg-action-btn--compact">
                    <i class="fa-solid fa-eye"></i> Ver produto
                </a>
            </div>
    <?php } ?>

        <?php if ($errors->any()) { ?>
            <div class="pc-alert pc-alert--danger">
                <?php foreach ($errors->all() as $error) { ?>
                    <div>{{ $error }}</div>
                <?php } ?>
            </div>
        <?php } ?>

        <?php if ($currentArea !== 'admin' && ($currentAreaReview['status'] ?? null) === 'rejected') { ?>
            <div class="pc-alert pc-alert--danger">
                <strong>Este produto voltou para correcao.</strong>
                <div class="pc-review-feedback-list">
                    <?php foreach (($currentAreaReview['items'] ?? []) as $itemKey => $review) { ?>
                        <?php if (($review['status'] ?? null) === 'rejected') { ?>
                            <span>
                                <i class="fa-solid fa-xmark"></i>
                                {{ $workflowAreas[$currentArea]['items'][$itemKey]['label'] ?? $itemKey }}:
                                {{ $review['reason'] ?: 'Sem motivo detalhado.' }}
                            </span>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>

        <?php if ($currentArea === 'admin') { ?>
            @include('product-growth-stage::partials.admin-review-board')
        <?php } ?>

        <form method="POST" action="{{ route($updateRoute, $product) }}" class="pc-form" id="product-growth-stage-form" enctype="multipart/form-data">
            {!! csrf_field() !!}
            {!! method_field('PUT') !!}

            <?php if ($isStoreBrandStage) { ?>
                <div class="pc-stage-form-sections">
                    <section class="pc-field-panel pc-stage-form-section">
                        <div class="pc-stage-form-grid-3">
                            <div>
                                <label class="pc-label" for="reference">Referencia / SKU</label>
                                <input class="pc-input" id="reference" name="reference" value="{{ old('reference', $currentStageData['reference'] ?? $product->reference) }}">
                            </div>
                            <div>
                                <label class="pc-label" for="internal_sku">SKU interno</label>
                                <input class="pc-input" id="internal_sku" name="internal_sku" value="{{ old('internal_sku', $currentStageData['internal_sku'] ?? $product->internal_sku) }}">
                            </div>
                            <div>
                                <label class="pc-label" for="ean13">EAN13</label>
                                <input class="pc-input" id="ean13" name="ean13" value="{{ old('ean13', $currentStageData['ean13'] ?? $product->ean) }}">
                            </div>
                        </div>

                        <div class="pc-stage-form-grid-3">
                            <div>
                                <label class="pc-label" for="manufacturer_id">Manufacturer</label>
                                <select class="pc-select" id="manufacturer_id" name="manufacturer_id">
                                    <option value="">Sem manufacturer</option>
                                    <?php foreach (($stageOptions['manufacturers'] ?? []) as $id => $name) { ?>
                                        <option value="{{ $id }}" @selected((string) old('manufacturer_id', $currentStageData['manufacturer_id'] ?? data_get($product->metadata ?? [], 'product_growth.manufacturer_id')) === (string) $id)>{{ $name }}</option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div>
                                <label class="pc-label" for="supplier_id">Supplier</label>
                                <select class="pc-select" id="supplier_id" name="supplier_id">
                                    <option value="">Sem supplier</option>
                                    <?php foreach (($stageOptions['suppliers'] ?? []) as $id => $name) { ?>
                                        <option value="{{ $id }}" @selected((string) old('supplier_id', $currentStageData['supplier_id'] ?? data_get($product->metadata ?? [], 'product_growth.supplier_id')) === (string) $id)>{{ $name }}</option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div>
                                <label class="pc-label" for="product_type">Tipo produto</label>
                                <select class="pc-select" id="product_type" name="product_type" data-purchase-product-type>
                                    <?php foreach (['simple' => 'Produto simples', 'combination' => 'Produto com combinacoes', 'pack' => 'Produto pack'] as $value => $label) { ?>
                                        <option value="{{ $value }}" @selected((string) old('product_type', $currentStageData['product_type'] ?? $product->product_type ?? 'simple') === (string) $value)>{{ $label }}</option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div class="pc-stage-form-grid-2">
                            <div>
                                <label class="pc-label">Backorder</label>
                                <label class="pc-switch-field">
                                    <input type="hidden" name="allow_backorder" value="0">
                                    <input type="checkbox" name="allow_backorder" value="1" @checked(old('allow_backorder', $currentStageData['allow_backorder'] ?? false))>
                                    <span></span><strong>Permitir venda sem stock</strong>
                                </label>
                            </div>
                        </div>

                    </section>

                    <section class="pc-field-panel pc-stage-form-section pc-purchase-type-section" data-purchase-type-section="combination" hidden>
                        <div class="pc-stage-form-section__head">
                            <i class="fa-solid fa-layer-group"></i>
                            <strong>Combinacoes</strong>
                        </div>
                        <div class="pc-combination-builder" data-combination-builder>
                            <div class="pc-combination-grid pc-combination-grid--head" style="--pc-combination-attr-count: {{ $combinationAttributeCount }}">
                                <?php foreach ($combinationAttributes as $attribute) { ?>
                                    <span><?php echo e($attribute['name'] ?? $attribute['slug']); ?></span>
                                <?php } ?>
                                <span>SKU</span>
                                <span>Stock</span>
                                <span>Price</span>
                                <span></span>
                            </div>
                            <div class="pc-combination-list" data-combination-list>
                                <?php foreach ($combinationRows as $index => $combination) { ?>
                                    <div class="pc-combination-grid pc-combination-row" style="--pc-combination-attr-count: {{ $combinationAttributeCount }}" data-combination-row>
                                        <?php foreach ($combinationAttributes as $attribute) {
                                            $attributeSlug = (string) ($attribute['slug'] ?? '');
                                            $selectedAttributeValue = $combination['attributes'][$attributeSlug] ?? $combination[$attributeSlug] ?? '';
                                            $attributeOptions = array_merge([['value' => '', 'label' => $attribute['name'] ?? $attributeSlug]], $attribute['values'] ?? []);
                                        ?>
                                            <select class="pc-select" name="combinations[<?php echo e($index); ?>][attributes][<?php echo e($attributeSlug); ?>]">
                                                <?php foreach ($attributeOptions as $option) {
                                                    $optionValue = (string) ($option['value'] ?? '');
                                                    $optionLabel = (string) ($option['label'] ?? $optionValue);
                                                    $selected = (string) $selectedAttributeValue === $optionValue ? ' selected' : '';
                                                ?>
                                                    <option value="<?php echo e($optionValue); ?>"<?php echo $selected; ?>><?php echo e($optionLabel); ?></option>
                                                <?php } ?>
                                            </select>
                                        <?php } ?>
                                        <input class="pc-input" name="combinations[<?php echo e($index); ?>][sku]" value="<?php echo e($combination['sku'] ?? ''); ?>" placeholder="MTG-M11-149-EN-NM-NF">
                                        <input class="pc-input" name="combinations[<?php echo e($index); ?>][stock]" type="number" min="0" step="1" value="<?php echo e($combination['stock'] ?? ''); ?>" placeholder="0">
                                        <input class="pc-input" name="combinations[<?php echo e($index); ?>][price]" type="number" min="0" step="0.01" value="<?php echo e($combination['price'] ?? ''); ?>" placeholder="0.00">
                                        <button type="button" class="pc-icon-action pc-icon-action--danger" data-combination-remove title="Remover combinacao">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                <?php } ?>
                            </div>
                            <template data-combination-template>
                                <div class="pc-combination-grid pc-combination-row" style="--pc-combination-attr-count: {{ $combinationAttributeCount }}" data-combination-row>
                                    <?php foreach ($combinationAttributes as $attribute) {
                                        $attributeSlug = (string) ($attribute['slug'] ?? '');
                                        $attributeOptions = array_merge([['value' => '', 'label' => $attribute['name'] ?? $attributeSlug]], $attribute['values'] ?? []);
                                    ?>
                                        <select class="pc-select" name="combinations[__INDEX__][attributes][<?php echo e($attributeSlug); ?>]">
                                            <?php foreach ($attributeOptions as $option) {
                                                $optionValue = (string) ($option['value'] ?? '');
                                                $optionLabel = (string) ($option['label'] ?? $optionValue);
                                            ?>
                                                <option value="<?php echo e($optionValue); ?>"><?php echo e($optionLabel); ?></option>
                                            <?php } ?>
                                        </select>
                                    <?php } ?>
                                    <input class="pc-input" name="combinations[__INDEX__][sku]" placeholder="MTG-M11-149-EN-NM-NF">
                                    <input class="pc-input" name="combinations[__INDEX__][stock]" type="number" min="0" step="1" placeholder="0">
                                    <input class="pc-input" name="combinations[__INDEX__][price]" type="number" min="0" step="0.01" placeholder="0.00">
                                    <button type="button" class="pc-icon-action pc-icon-action--danger" data-combination-remove title="Remover combinacao">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            </template>
                            <button type="button" class="lsg-action-btn lsg-action-btn--secondary lsg-action-btn--compact" data-combination-add>
                                <i class="fa-solid fa-plus"></i> Adicionar combinacao
                            </button>
                        </div>
                    </section>

                    <section class="pc-field-panel pc-stage-form-section pc-purchase-type-section" data-purchase-type-section="pack" hidden>
                        <div class="pc-stage-form-section__head">
                            <i class="fa-solid fa-boxes-stacked"></i>
                            <strong>Composicao do pack</strong>
                        </div>
                        <div class="pc-pack-builder" data-pack-builder data-pack-suggestions="<?php echo e($packProductSuggestionsJson); ?>">
                            <datalist id="product-growth-pack-reference-suggestions">
                                <?php foreach ($packProductSuggestions as $suggestion) { ?>
                                    <option value="<?php echo e($suggestion['value'] ?? ''); ?>"><?php echo e($suggestion['label'] ?? $suggestion['value'] ?? ''); ?></option>
                                <?php } ?>
                            </datalist>
                            <div class="pc-pack-grid pc-pack-grid--head">
                                <span>Referencia do produto</span>
                                <span>Quantidade</span>
                                <span></span>
                            </div>
                            <div class="pc-pack-list" data-pack-list>
                                <?php foreach ($packRows as $index => $component) { ?>
                                    <div class="pc-pack-grid pc-pack-row" data-pack-row>
                                        <input class="pc-input" name="pack_components[<?php echo e($index); ?>][reference]" value="<?php echo e($component['reference'] ?? ''); ?>" list="product-growth-pack-reference-suggestions" data-pack-reference-input autocomplete="off" placeholder="SKU ou referencia do produto">
                                        <input class="pc-input" name="pack_components[<?php echo e($index); ?>][quantity]" type="number" min="0.0001" step="0.0001" value="<?php echo e($component['quantity'] ?? 1); ?>" placeholder="1">
                                        <button type="button" class="pc-icon-action pc-icon-action--danger" data-pack-remove title="Remover produto do pack">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                <?php } ?>
                            </div>
                            <template data-pack-template>
                                <div class="pc-pack-grid pc-pack-row" data-pack-row>
                                    <input class="pc-input" name="pack_components[__INDEX__][reference]" list="product-growth-pack-reference-suggestions" data-pack-reference-input autocomplete="off" placeholder="SKU ou referencia do produto">
                                    <input class="pc-input" name="pack_components[__INDEX__][quantity]" type="number" min="0.0001" step="0.0001" value="1" placeholder="1">
                                    <button type="button" class="pc-icon-action pc-icon-action--danger" data-pack-remove title="Remover produto do pack">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            </template>
                            <button type="button" class="lsg-action-btn lsg-action-btn--secondary lsg-action-btn--compact" data-pack-add>
                                <i class="fa-solid fa-plus"></i> Adicionar produto
                            </button>
                        </div>
                    </section>
                </div>
            <?php } ?>

            <?php if ($isLogisticsStage) { ?>
                <div class="pc-form-grid">
                    <div class="pc-field-panel"><label class="pc-label" for="ean13">EAN13</label><input class="pc-input" id="ean13" name="ean13" value="{{ old('ean13', $currentStageData['ean13'] ?? $product->ean) }}"></div>
                    <div class="pc-field-panel"><label class="pc-label" for="stock_quantity">Stock</label><input class="pc-input" id="stock_quantity" name="stock_quantity" type="number" min="0" step="1" value="{{ old('stock_quantity', $currentStageData['stock_quantity'] ?? $product->storeProducts->first()?->stock_quantity) }}"></div>
                    <div class="pc-field-panel pc-form-grid-1">
                        <div class="pc-compact-measurements">
                            <?php foreach (['weight' => 'Peso', 'width' => 'Larg.', 'height' => 'Alt.', 'depth' => 'Prof.', 'package_quantity' => 'Qtd emb.'] as $field => $label) { ?>
                                <div>
                                    <label class="pc-label" for="{{ $field }}">{{ $label }}</label>
                                    <input class="pc-input" id="{{ $field }}" name="{{ $field }}" type="number" step="{{ $field === 'weight' ? '0.001' : '0.1' }}" min="0" value="{{ old($field, $currentStageData[$field] ?? $product->{$field} ?? '') }}">
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="pc-field-panel"><div class="pc-volumetric-weight"><small>Peso volumetrico</small><strong data-volumetric-weight>0.000 kg</strong><span>L x A x P / 5000</span></div></div>
                    <div class="pc-field-panel"><label class="pc-label" for="package_type">Tipo embalagem</label><input class="pc-input" id="package_type" name="package_type" value="{{ old('package_type', $currentStageData['package_type'] ?? '') }}"></div>
                    <div class="pc-field-panel"><label class="pc-label" for="shipping_class">Classe envio</label><input class="pc-input" id="shipping_class" name="shipping_class" value="{{ old('shipping_class', $currentStageData['shipping_class'] ?? '') }}"></div>
                    <div class="pc-field-panel"><label class="pc-switch-field"><input type="hidden" name="measurements_verified" value="0"><input type="checkbox" name="measurements_verified" value="1" @checked(old('measurements_verified', $currentStageData['measurements_verified'] ?? false))><span></span><strong>Medidas verificadas</strong></label></div>
                    <div class="pc-field-panel"><label class="pc-switch-field"><input type="hidden" name="has_shipping_restrictions" value="0"><input type="checkbox" name="has_shipping_restrictions" value="1" @checked(old('has_shipping_restrictions', $currentStageData['has_shipping_restrictions'] ?? false))><span></span><strong>Produto com restricoes de envio</strong></label></div>
                    <div class="pc-field-panel pc-form-grid-1"><label class="pc-label" for="carrier_exclusions">Exclusoes transportadores</label><textarea class="pc-textarea" id="carrier_exclusions" name="carrier_exclusions" rows="4">{{ old('carrier_exclusions', $currentStageData['carrier_exclusions'] ?? '') }}</textarea></div>
                    <div class="pc-field-panel pc-form-grid-1"><label class="pc-label" for="handling_notes">Notas logisticas</label><textarea class="pc-textarea" id="handling_notes" name="handling_notes" rows="4">{{ old('handling_notes', $currentStageData['handling_notes'] ?? '') }}</textarea></div>
                </div>
            <?php } ?>

            <?php if ($isFinanceStage) { ?>
                <div class="pc-stage-form-sections">
                    @php
                        $legacyPurchaseData = data_get($product->metadata ?? [], 'department_content.store-brand-manager', []);
                        $financeVatRules = config('product-core.finance.vat_rules', ['pt_vat_23' => ['label' => 'Portugal VAT 23%', 'rate' => 0.23]]);
                        $defaultVatRule = config('product-core.finance.default_vat_rule', 'pt_vat_23');
                        $selectedVatRule = old('tax_rule', $currentStageData['tax_rule'] ?? $defaultVatRule);
                        $selectedVatRule = array_key_exists($selectedVatRule, $financeVatRules) ? $selectedVatRule : $defaultVatRule;
                        $supplierFinanceContext = $stageOptions['supplier_finance_context'] ?? [];
                        $selectedCurrency = strtoupper((string) ($supplierFinanceContext['currency'] ?? $currentStageData['supplier_currency'] ?? 'EUR'));
                        $currencyRate = (float) ($supplierFinanceContext['currency_rate_to_eur'] ?? $currentStageData['currency_rate_to_eur'] ?? 1);
                        $currencyRate = $currencyRate > 0 ? $currencyRate : 1;
                        $currencySymbols = ['EUR' => '€', 'USD' => '$', 'GBP' => '£', 'JPY' => '¥', 'CHF' => 'CHF', 'CAD' => 'C$', 'AUD' => 'A$'];
                            $selectedCurrencySymbol = $currencySymbols[$selectedCurrency] ?? $selectedCurrency;
                            $purchaseOriginal = old('purchase_price_original', $currentStageData['purchase_price_original'] ?? $legacyPurchaseData['purchase_price_original'] ?? $currentStageData['purchase_price'] ?? $legacyPurchaseData['purchase_price'] ?? $product->base_cost);
                            $supplierRecommendedSale = old('supplier_recommended_sale_price', $currentStageData['supplier_recommended_sale_price'] ?? '');
                            $desiredDiscount = old('desired_discount', $currentStageData['desired_discount'] ?? '');
                        @endphp

                    <section class="pc-field-panel pc-stage-form-section">
                        <div class="pc-stage-form-section__head"><i class="fa-solid fa-file-invoice-dollar"></i><strong>Fiscalidade</strong></div>
                        <div class="pc-form-grid">
                            <div>
                                <label class="pc-label" for="tax_rule">VAT / Regra fiscal</label>
                                <select class="pc-select" id="tax_rule" name="tax_rule">
                                    <?php foreach ($financeVatRules as $ruleKey => $rule) { ?>
                                        <option value="{{ $ruleKey }}" data-vat-rate="{{ $rule['rate'] ?? 0 }}" @selected((string) $selectedVatRule === (string) $ruleKey)>{{ $rule['label'] ?? $ruleKey }}</option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div>
                                <label class="pc-label" for="nc_code">NC code</label>
                                <input class="pc-input" id="nc_code" name="nc_code" value="{{ old('nc_code', $currentStageData['nc_code'] ?? '') }}" placeholder="Nomenclature code">
                            </div>
                        </div>
                    </section>

                    <section class="pc-field-panel pc-stage-form-section">
                        <div class="pc-stage-form-section__head">
                            <i class="fa-solid fa-euro-sign"></i>
                            <strong>Precos</strong>
                        </div>
                        <div class="pc-price-workbench pc-price-workbench--stacked">
                            <div class="pc-price-workbench__inputs">
                                <input type="hidden" id="supplier_currency" name="supplier_currency" value="{{ $selectedCurrency }}">
                                <input type="hidden" id="currency_rate_to_eur" name="currency_rate_to_eur" value="{{ $currencyRate }}">
                                <div class="pc-finance-origin-grid">
                                    <div class="pc-finance-origin-card pc-finance-origin-card--supplier">
                                        <div class="pc-finance-origin-card__head">
                                            <span><i class="fa-solid fa-truck-field"></i></span>
                                            <strong>Fornecedor</strong>
                                        </div>
                                        <div class="pc-stage-form-grid-2">
                                            <div>
                                                <label class="pc-label" for="purchase_price_original">Compra s/ VAT</label>
                                                <label class="pc-input-prefix">
                                                    <span>{{ $selectedCurrencySymbol }}</span>
                                                    <input class="pc-input pc-price-input" id="purchase_price_original" name="purchase_price_original" type="number" step="0.0001" min="0" value="{{ $purchaseOriginal }}">
                                                </label>
                                            </div>
                                            <div>
                                                <label class="pc-label" for="supplier_recommended_sale_price">Venda recomendada</label>
                                                <label class="pc-input-prefix">
                                                    <span>{{ $selectedCurrencySymbol }}</span>
                                                    <input class="pc-input pc-price-input" id="supplier_recommended_sale_price" name="supplier_recommended_sale_price" type="number" step="0.0001" min="0" value="{{ $supplierRecommendedSale }}">
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="pc-finance-origin-card pc-finance-origin-card--lsg">
                                        <div class="pc-finance-origin-card__head">
                                            <span><i class="fa-solid fa-building-columns"></i></span>
                                            <strong>LSG</strong>
                                        </div>
                                        <div class="pc-stage-form-grid-2">
                                            <div>
                                                <label class="pc-label" for="purchase_price">Compra EUR s/ VAT</label>
                                                <label class="pc-input-prefix">
                                                    <span>€</span>
                                                    <input class="pc-input pc-price-input" id="purchase_price" name="purchase_price" type="number" step="0.0001" min="0" readonly value="{{ old('purchase_price', $currentStageData['purchase_price'] ?? $legacyPurchaseData['purchase_price'] ?? $product->base_cost) }}">
                                                </label>
                                            </div>
                                            <div>
                                                <label class="pc-label" for="base_sale_price">Venda EUR s/ VAT</label>
                                                <label class="pc-input-prefix">
                                                    <span>€</span>
                                                    <input class="pc-input pc-price-input" id="base_sale_price" name="base_sale_price" type="number" step="0.0001" min="0" value="{{ old('base_sale_price', $currentStageData['base_sale_price'] ?? $legacyPurchaseData['base_sale_price'] ?? $product->base_price) }}">
                                                </label>
                                            </div>
                                            <div>
                                                <label class="pc-label" for="desired_discount">Desconto individual</label>
                                                <label class="pc-input-prefix">
                                                    <span>%</span>
                                                    <input class="pc-input pc-price-input" id="desired_discount" name="desired_discount" type="number" step="0.01" min="0" max="100" value="{{ $desiredDiscount }}">
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="pc-price-workbench__summary" aria-label="Resumo de preco">
                                <div class="pc-price-metric pc-price-metric--blue">
                                    <span><i class="fa-solid fa-scale-balanced"></i></span>
                                    <small>Compra EUR</small>
                                    <strong data-price-cost-eur-output>0.00</strong>
                                </div>
                                <div class="pc-price-metric pc-price-metric--green">
                                    <span><i class="fa-solid fa-chart-line"></i></span>
                                    <small>Margem s/ VAT</small>
                                    <strong data-price-margin-output>0.00%</strong>
                                </div>
                                <div class="pc-price-metric pc-price-metric--blue">
                                    <span><i class="fa-solid fa-coins"></i></span>
                                    <small>Lucro s/ VAT</small>
                                    <strong data-price-profit-output>0.00</strong>
                                </div>
                                <div class="pc-price-metric pc-price-metric--green">
                                    <span><i class="fa-solid fa-receipt"></i></span>
                                    <small>Venda c/ VAT</small>
                                    <strong data-price-sale-vat-output>0.00</strong>
                                </div>
                                <div class="pc-price-metric pc-price-metric--amber">
                                    <span><i class="fa-solid fa-percent"></i></span>
                                    <small>Lucro diluido VAT</small>
                                    <strong data-price-diluted-profit-output>0.00%</strong>
                                </div>
                                <div class="pc-price-metric pc-price-metric--amber">
                                    <span><i class="fa-solid fa-tag"></i></span>
                                    <small>Venda c/ desconto</small>
                                    <strong data-price-discount-output>0.00</strong>
                                </div>
                            </div>
                        </div>
                    </section>

                </div>
            <?php } ?>

            <?php if ($isMarketingContentStage) { ?>
                <div class="pc-stage-form-sections">
                    @include('product-growth-stage::partials.characteristics-editor')

                    <section class="pc-field-panel pc-stage-form-section" data-sales-relations data-product-suggestions="<?php echo e($salesProductSuggestionsJson); ?>">
                        <div class="pc-stage-form-section__head"><i class="fa-solid fa-link"></i><strong>Relacionamento comercial</strong></div>
                        <div class="pc-sales-helper-note">
                            Cross-sell e preco concorrencia serao calculados automaticamente pelo sistema. Sales apenas sugere recomendados e bundles de upsell.
                        </div>

                        <div class="pc-sales-recommendation-builder" data-sales-recommendations>
                            <div class="pc-sales-recommendation-head">
                                <div>
                                    <strong>Produtos recomendados</strong>
                                    <small>Opcional. Se preenchido, maximo de 6 produtos.</small>
                                </div>
                                <button type="button" class="lsg-action-btn lsg-action-btn--secondary lsg-action-btn--compact" data-sales-recommendation-add>
                                    <i class="fa-solid fa-plus"></i> Adicionar
                                </button>
                            </div>
                            <div class="pc-sales-recommendation-list" data-sales-recommendation-list>
                                <?php foreach ($recommendedRows as $index => $reference) { ?>
                                    <div class="pc-sales-reference-row" data-sales-reference-row>
                                        <input class="pc-input" name="recommended_products[<?php echo e($index); ?>]" value="<?php echo e($reference); ?>" data-sales-reference-input autocomplete="off" placeholder="Referencia do produto">
                                        <button type="button" class="pc-icon-action pc-icon-action--danger" data-sales-reference-remove title="Remover recomendado">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                <?php } ?>
                            </div>
                            <template data-sales-recommendation-template>
                                <div class="pc-sales-reference-row" data-sales-reference-row>
                                    <input class="pc-input" name="recommended_products[__INDEX__]" data-sales-reference-input autocomplete="off" placeholder="Referencia do produto">
                                    <button type="button" class="pc-icon-action pc-icon-action--danger" data-sales-reference-remove title="Remover recomendado">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            </template>
                        </div>

                        <div class="pc-sales-upsell-builder" data-sales-upsell-builder>
                            <div class="pc-sales-recommendation-head">
                                <div>
                                    <strong>Upsell / bundles sugeridos</strong>
                                    <small>Cria propostas de bundle com desconto extra no grupo.</small>
                                </div>
                                <button type="button" class="lsg-action-btn lsg-action-btn--secondary lsg-action-btn--compact" data-sales-upsell-add>
                                    <i class="fa-solid fa-plus"></i> Adicionar bundle
                                </button>
                            </div>
                            <div class="pc-sales-upsell-list" data-sales-upsell-list>
                                <?php foreach ($upsellRows as $bundleIndex => $bundle) { ?>
                                    <?php $bundleProducts = array_slice(array_values(array_filter($bundle['products'] ?? [], fn($value) => filled($value))), 0, 6); ?>
                                    <div class="pc-sales-upsell-card" data-sales-upsell-row>
                                        <div class="pc-stage-form-grid-2">
                                            <input class="pc-input" name="upsell_bundles[<?php echo e($bundleIndex); ?>][title]" value="<?php echo e($bundle['title'] ?? ''); ?>" placeholder="Nome do bundle">
                                            <input class="pc-input" name="upsell_bundles[<?php echo e($bundleIndex); ?>][discount]" type="number" min="0" max="100" step="0.01" value="<?php echo e($bundle['discount'] ?? ''); ?>" placeholder="Desconto %">
                                        </div>
                                        <div class="pc-sales-upsell-products" data-sales-upsell-products>
                                            <?php foreach ($bundleProducts as $productIndex => $reference) { ?>
                                                <div class="pc-sales-reference-row" data-sales-reference-row>
                                                    <input class="pc-input" name="upsell_bundles[<?php echo e($bundleIndex); ?>][products][<?php echo e($productIndex); ?>]" value="<?php echo e($reference); ?>" data-sales-reference-input autocomplete="off" placeholder="Referencia do produto">
                                                    <button type="button" class="pc-icon-action pc-icon-action--danger" data-sales-reference-remove title="Remover produto">
                                                        <i class="fa-solid fa-xmark"></i>
                                                    </button>
                                                </div>
                                            <?php } ?>
                                        </div>
                                        <div class="pc-sales-upsell-actions">
                                            <button type="button" class="lsg-action-btn lsg-action-btn--secondary lsg-action-btn--compact" data-sales-upsell-product-add>
                                                <i class="fa-solid fa-plus"></i> Produto
                                            </button>
                                            <button type="button" class="lsg-action-btn lsg-action-btn--danger lsg-action-btn--compact" data-sales-upsell-remove>
                                                <i class="fa-solid fa-xmark"></i> Remover bundle
                                            </button>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                            <template data-sales-upsell-template>
                                    <div class="pc-sales-upsell-card" data-sales-upsell-row>
                                    <div class="pc-stage-form-grid-2">
                                        <input class="pc-input" name="upsell_bundles[__BUNDLE__][title]" placeholder="Nome do bundle">
                                        <input class="pc-input" name="upsell_bundles[__BUNDLE__][discount]" type="number" min="0" max="100" step="0.01" placeholder="Desconto %">
                                    </div>
                                    <div class="pc-sales-upsell-products" data-sales-upsell-products>
                                    </div>
                                    <div class="pc-sales-upsell-actions">
                                        <button type="button" class="lsg-action-btn lsg-action-btn--secondary lsg-action-btn--compact" data-sales-upsell-product-add>
                                            <i class="fa-solid fa-plus"></i> Produto
                                        </button>
                                        <button type="button" class="lsg-action-btn lsg-action-btn--danger lsg-action-btn--compact" data-sales-upsell-remove>
                                            <i class="fa-solid fa-xmark"></i> Remover bundle
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </section>
                </div>
            <?php } ?>

            <?php if ($isCreativeAssetStage) { ?>
                <div class="pc-stage-form-sections">
                    <section class="pc-field-panel pc-stage-form-section">
                        <div class="pc-stage-form-section__head"><i class="fa-solid fa-images"></i><strong>Imagens do produto</strong></div>
                        <div class="pc-form-grid">
                            <div>
                                <label class="pc-upload-zone" for="cover_image_upload" data-upload-zone data-upload-kind="image">
                                    <input id="cover_image_upload" name="cover_image_upload" type="file" accept="image/*" data-upload-input>
                                    <span class="pc-upload-zone__preview" data-upload-preview>
                                        @if(!empty($currentAssetData['cover_image']))
                                            <img src="{{ $currentAssetData['cover_image'] }}" alt="Foto de capa">
                                        @else
                                            <i class="fa-regular fa-image"></i>
                                        @endif
                                    </span>
                                    <span class="pc-upload-zone__body">
                                        <strong>Foto de capa</strong>
                                        <small data-upload-filename>{{ !empty($currentAssetData['cover_image']) ? basename(parse_url($currentAssetData['cover_image'], PHP_URL_PATH) ?: $currentAssetData['cover_image']) : 'Arraste ou clique para escolher imagem' }}</small>
                                    </span>
                                </label>
                            </div>
                            <div>
                                <label class="pc-upload-zone" for="main_image_upload" data-upload-zone data-upload-kind="image">
                                    <input id="main_image_upload" name="main_image_upload" type="file" accept="image/*" data-upload-input>
                                    <span class="pc-upload-zone__preview" data-upload-preview>
                                        @if(!empty($currentAssetData['main_image']))
                                            <img src="{{ $currentAssetData['main_image'] }}" alt="Foto principal">
                                        @else
                                            <i class="fa-regular fa-image"></i>
                                        @endif
                                    </span>
                                    <span class="pc-upload-zone__body">
                                        <strong>Foto principal</strong>
                                        <small data-upload-filename>{{ !empty($currentAssetData['main_image']) ? basename(parse_url($currentAssetData['main_image'], PHP_URL_PATH) ?: $currentAssetData['main_image']) : 'Arraste ou clique para escolher imagem' }}</small>
                                    </span>
                                </label>
                            </div>
                            <div class="pc-form-grid-1">
                                <label class="pc-upload-zone pc-upload-zone--wide" for="gallery_images" data-upload-zone data-upload-kind="image" data-upload-multiple>
                                    <input id="gallery_images" name="gallery_images[]" type="file" accept="image/*" multiple data-upload-input>
                                    <span class="pc-upload-zone__preview pc-upload-zone__preview--gallery" data-upload-preview>
                                        @forelse($currentGalleryAssets as $galleryAssetUrl)
                                            <img src="{{ $galleryAssetUrl }}" alt="Foto do produto">
                                        @empty
                                            <i class="fa-regular fa-images"></i>
                                        @endforelse
                                    </span>
                                    <span class="pc-upload-zone__body">
                                        <strong>Fotos do produto</strong>
                                        <small data-upload-filename>{{ $currentGalleryAssets->count() ? $currentGalleryAssets->count() . ' ficheiro(s) guardado(s)' : 'Arraste ou clique para escolher uma ou varias imagens' }}</small>
                                    </span>
                                </label>
                            </div>
                            <div class="pc-form-grid-1">
                                <label class="pc-upload-zone pc-upload-zone--wide" for="combination_images" data-upload-zone data-upload-kind="image" data-upload-multiple>
                                    <input id="combination_images" name="combination_images[]" type="file" accept="image/*" multiple data-upload-input>
                                    <span class="pc-upload-zone__preview pc-upload-zone__preview--gallery" data-upload-preview>
                                        @forelse($currentCombinationAssets as $combinationAssetUrl)
                                            <img src="{{ $combinationAssetUrl }}" alt="Foto da combinacao">
                                        @empty
                                            <i class="fa-regular fa-images"></i>
                                        @endforelse
                                    </span>
                                    <span class="pc-upload-zone__body">
                                        <strong>Fotos das combinacoes</strong>
                                        <small data-upload-filename>{{ $currentCombinationAssets->count() ? $currentCombinationAssets->count() . ' ficheiro(s) guardado(s)' : 'Arraste ou clique para escolher imagens' }}</small>
                                    </span>
                                </label>
                                <textarea class="pc-textarea" name="combination_image_labels" rows="3" placeholder="Opcional: uma identificacao por linha, pela mesma ordem dos ficheiros">{{ old('combination_image_labels', $currentStageData['combination_image_labels'] ?? '') }}</textarea>
                            </div>
                        </div>
                    </section>

                    <section class="pc-field-panel pc-stage-form-section">
                        <div class="pc-stage-form-section__head"><i class="fa-solid fa-video"></i><strong>Videos e redes sociais</strong></div>
                        <div class="pc-form-grid">
                            <div class="pc-form-grid-1">
                                <label class="pc-label" for="youtube_video_code">Codigo YouTube do video principal</label>
                                <input class="pc-input" id="youtube_video_code" name="youtube_video_code" value="{{ old('youtube_video_code', $currentStageData['youtube_video_code'] ?? '') }}" placeholder="Ex: dQw4w9WgXcQ">
                            </div>
                            <div>
                                <label class="pc-upload-zone" for="social_square_upload" data-upload-zone data-upload-kind="image">
                                    <input id="social_square_upload" name="social_square_upload" type="file" accept="image/*" data-upload-input>
                                    <span class="pc-upload-zone__preview" data-upload-preview>
                                        @if(!empty($currentAssetData['social_square']))
                                            <img src="{{ $currentAssetData['social_square'] }}" alt="Criativo social quadrado">
                                        @else
                                            <i class="fa-regular fa-image"></i>
                                        @endif
                                    </span>
                                    <span class="pc-upload-zone__body">
                                        <strong>Criativo social quadrado</strong>
                                        <small data-upload-filename>{{ !empty($currentAssetData['social_square']) ? basename(parse_url($currentAssetData['social_square'], PHP_URL_PATH) ?: $currentAssetData['social_square']) : 'Imagem 1:1' }}</small>
                                    </span>
                                </label>
                            </div>
                            <div>
                                <label class="pc-upload-zone" for="social_story_upload" data-upload-zone data-upload-kind="image">
                                    <input id="social_story_upload" name="social_story_upload" type="file" accept="image/*" data-upload-input>
                                    <span class="pc-upload-zone__preview" data-upload-preview>
                                        @if(!empty($currentAssetData['social_story']))
                                            <img src="{{ $currentAssetData['social_story'] }}" alt="Criativo story">
                                        @else
                                            <i class="fa-regular fa-image"></i>
                                        @endif
                                    </span>
                                    <span class="pc-upload-zone__body">
                                        <strong>Criativo story</strong>
                                        <small data-upload-filename>{{ !empty($currentAssetData['social_story']) ? basename(parse_url($currentAssetData['social_story'], PHP_URL_PATH) ?: $currentAssetData['social_story']) : 'Imagem vertical' }}</small>
                                    </span>
                                </label>
                            </div>
                            <div class="pc-form-grid-1">
                                <label class="pc-label" for="social_reel_youtube_code">Codigo YouTube do video/reel social</label>
                                <input class="pc-input" id="social_reel_youtube_code" name="social_reel_youtube_code" value="{{ old('social_reel_youtube_code', $currentStageData['social_reel_youtube_code'] ?? '') }}" placeholder="Ex: dQw4w9WgXcQ">
                            </div>
                            <div class="pc-form-grid-1"><label class="pc-label" for="asset_notes">Notas de assets</label><textarea class="pc-textarea" id="asset_notes" name="asset_notes" rows="3">{{ old('asset_notes', $currentStageData['asset_notes'] ?? '') }}</textarea></div>
                        </div>
                    </section>
                </div>
            <?php } ?>

        </form>

        <?php if ($currentArea !== 'admin') { ?>
                </section>
            </div>
        </div>
        <?php } else { ?>
            </section>
        </div>
        <?php } ?>
<?php } else { ?>
    <section class="product-core-card pc-panel">
        <div class="pc-panel-head">
            <div>
                <h2 class="pc-panel-title">Painel de trabalho</h2>
                <p class="pc-panel-subtitle">Entrada segmentada para os produtos desta area.</p>
            </div>
            <a href="{{ route('product_growth.product_core.products.index') }}" class="lsg-action-btn lsg-action-btn--primary lsg-action-btn--compact">
                <i class="fa-solid fa-box"></i> Produtos
            </a>
        </div>

        <div class="pc-queue-grid">
            <?php foreach (['new' => ['label' => 'Novos anúncios', 'icon' => 'fa-solid fa-inbox', 'class' => 'pc-stat--blue'], 'corrections' => ['label' => 'Correções necessárias', 'icon' => 'fa-solid fa-triangle-exclamation', 'class' => 'pc-stat--amber']] as $queueKey => $queueMeta) { ?>
                <div class="pc-queue-panel">
                    <div class="pc-stat product-core-card {{ $queueMeta['class'] }}">
                        <div><small>{{ $queueMeta['label'] }}</small><strong>{{ ($workQueue[$queueKey] ?? collect())->count() }}</strong></div>
                        <span class="pc-stat-icon"><i class="{{ $queueMeta['icon'] }}"></i></span>
                    </div>

                    <div class="product-core-table-wrap">
                        <table class="product-core-table">
                            <thead><tr><th>Produto</th><th>Fabricante</th><th>Estado</th><th></th></tr></thead>
                            <tbody>
                            <?php if (($workQueue[$queueKey] ?? collect())->isEmpty()) { ?>
                                <tr><td colspan="4" class="text-center text-muted py-4">Sem produtos nesta fila.</td></tr>
                            <?php } else { ?>
                                <?php foreach (($workQueue[$queueKey] ?? collect()) as $queuedProduct) { ?>
                                    <tr>
                                        <td><div class="pc-table-title"><strong>{{ $queuedProduct->name }}</strong><span>{{ $queuedProduct->internal_sku }} · {{ $queuedProduct->reference ?: 'sem ref.' }}</span></div></td>
                                        <td>{{ data_get($queuedProduct->metadata ?? [], 'product_growth.manufacturer_name') ?? $queuedProduct->brand?->name ?? '-' }}</td>
                                        <td><span class="pc-badge">{{ config('product-core.states.' . $queuedProduct->status, $queuedProduct->status) }}</span></td>
                                        <td><a href="{{ route($editRoute, $queuedProduct) }}" class="lsg-action-btn lsg-action-btn--primary lsg-action-btn--compact"><i class="fa-solid fa-pen-to-square"></i> Abrir</a></td>
                                    </tr>
                                <?php } ?>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php } ?>
        </div>
    </section>
<?php } ?>

<?php if ($currentArea === 'admin' && !$isEditing) { ?>
    <?php
        $premiumStageKeys = [
            'webcatalogue-premium-layer',
            'product-buzz-manager',
            'ai-ads-manager',
            'product-evolution-manager',
            'publisher-export-manager',
            'prestashop-bridge',
            'performance-manager',
        ];
    ?>
    <section class="product-core-card pc-panel">
        <div class="pc-panel-head">
            <div>
                <h2 class="pc-panel-title">Zonas premium</h2>
                <p class="pc-panel-subtitle">Atalhos para extensoes e camadas avancadas do Product Growth.</p>
            </div>
        </div>

        <div class="pc-stage-nav">
            <?php foreach ($stages as $stageKey => $item) { ?>
                <?php if (in_array($stageKey, $premiumStageKeys, true) && Route::has($item['route'])) { ?>
                    <a href="{{ route($item['route']) }}" class="{{ $item['route'] === $stage['route'] ? 'is-active' : '' }}">
                        <i class="{{ $item['icon'] }}"></i>
                        <span>{{ $item['title'] }}</span>
                    </a>
                <?php } ?>
            <?php } ?>
        </div>
    </section>
<?php } ?>
@endsection
