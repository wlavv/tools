@php
    $purchaseData = data_get($product->metadata ?? [], 'department_content.store-brand-manager', []);
    $financeData = data_get($product->metadata ?? [], 'department_content.brand-compliance-manager', []);
    $financeCost = isset($financeData['purchase_price']) && $financeData['purchase_price'] !== '' ? (float) $financeData['purchase_price'] : (float) ($product->base_cost ?? 0);
    $financeSale = isset($financeData['base_sale_price']) && $financeData['base_sale_price'] !== '' ? (float) $financeData['base_sale_price'] : (float) ($product->base_price ?? 0);
    $financeDiscount = isset($financeData['desired_discount']) && $financeData['desired_discount'] !== '' ? (float) $financeData['desired_discount'] : null;
    $financeVatRule = $financeData['tax_rule'] ?? config('product-core.finance.default_vat_rule', 'pt_vat_23');
    $financeVatRate = (float) data_get(config('product-core.finance.vat_rules', []), $financeVatRule . '.rate', 0.23);
    $financeSaleWithVat = $financeSale > 0 ? $financeSale * (1 + $financeVatRate) : 0;
    $financeProfit = $financeCost > 0 && $financeSale > 0 ? $financeSale - $financeCost : 0;
    $financeMargin = $financeSale > 0 && $financeProfit > 0 ? ($financeProfit / $financeSale) * 100 : 0;
    $financeDiscountedWithVat = $financeSaleWithVat > 0 && $financeDiscount !== null ? $financeSaleWithVat * (1 - min(max($financeDiscount, 0), 100) / 100) : 0;
@endphp

<details class="product-core-card pc-panel pc-field-panel pc-stage-form-section pc-collapsible-panel pc-context-preview-panel">
    <summary class="pc-stage-form-section__head pc-collapsible-panel__summary">
        <i class="fa-solid fa-circle-info"></i>
        <strong>Informacao do produto</strong>
        <span><i class="fa-solid fa-chevron-down"></i></span>
    </summary>

    <div class="pc-stage-context-grid">
        <div class="pc-stage-context-card">
            <div class="pc-stage-context-card__head">
                <i class="fa-solid fa-store"></i>
                <strong>Loja</strong>
            </div>
            <div class="pc-stage-info-list">
                <?php if ($product->storeProducts->isNotEmpty()) { ?>
                    <?php foreach ($product->storeProducts as $storeProduct) { ?>
                        <span>{{ $storeProduct->store?->name ?? 'Loja #' . $storeProduct->store_id }}</span>
                    <?php } ?>
                <?php } else { ?>
                    <span>Sem lojas associadas.</span>
                <?php } ?>
            </div>
        </div>
        <div class="pc-stage-context-card">
            <div class="pc-stage-context-card__head">
                <i class="fa-solid fa-sitemap"></i>
                <strong>Categoria</strong>
            </div>
            <div class="pc-stage-info-list">
                <?php if (!empty($stageOptions['selected_categories'] ?? [])) { ?>
                    <?php foreach ($stageOptions['selected_categories'] as $categoryInfo) { ?>
                        <span>{{ $categoryInfo['category'] }}</span>
                    <?php } ?>
                <?php } else { ?>
                    <span>Sem categoria definida.</span>
                <?php } ?>
            </div>
        </div>
    </div>

    <div class="pc-readonly-context-grid">
        <div><small>Marca</small><strong>{{ $purchaseData['manufacturer_name'] ?? data_get($product->metadata ?? [], 'product_growth.manufacturer_name') ?? $product->brand?->name ?? '-' }}</strong></div>
        <div><small>Fornecedor</small><strong>{{ $purchaseData['supplier_name'] ?? data_get($product->metadata ?? [], 'product_growth.supplier_name') ?? $product->supplier?->name ?? '-' }}</strong></div>
        <div><small>EAN13</small><strong>{{ $purchaseData['ean13'] ?? $product->ean ?: '-' }}</strong></div>
        <div><small>Tipo produto</small><strong>{{ $purchaseData['product_type'] ?? $product->product_type ?: '-' }}</strong></div>
    </div>

    <div class="pc-stage-form-section__head"><i class="fa-solid fa-chart-line"></i><strong>Logica de preco e estrategia comercial</strong></div>
    <div class="pc-sales-workbench">
        <div class="pc-readonly-context-grid pc-readonly-context-grid--compact">
            <div><small>Preco compra s/ VAT</small><strong>{{ $financeCost > 0 ? number_format($financeCost, 2) . ' EUR' : '-' }}</strong></div>
            <div><small>Preco venda s/ VAT</small><strong>{{ $financeSale > 0 ? number_format($financeSale, 2) . ' EUR' : '-' }}</strong></div>
            <div><small>Preco venda c/ VAT</small><strong>{{ $financeSaleWithVat > 0 ? number_format($financeSaleWithVat, 2) . ' EUR' : '-' }}</strong></div>
            <div><small>Desconto pretendido</small><strong>{{ $financeDiscount !== null ? number_format($financeDiscount, 2) . '%' : '-' }}</strong></div>
            <div><small>Backorder</small><strong>{{ !empty($purchaseData['allow_backorder']) ? 'Permitido' : 'Nao permitido' }}</strong></div>
            <div><small>VAT</small><strong>{{ strtoupper(str_replace('_', ' ', $financeVatRule)) }}</strong></div>
        </div>

        <div class="pc-price-workbench__summary pc-sales-summary" aria-label="Resumo comercial">
            <div class="pc-price-metric pc-price-metric--green">
                <span><i class="fa-solid fa-percent"></i></span>
                <small>Margem estimada</small>
                <strong>{{ $financeMargin > 0 ? number_format($financeMargin, 2) . '%' : '0.00%' }}</strong>
            </div>
            <div class="pc-price-metric pc-price-metric--blue">
                <span><i class="fa-solid fa-coins"></i></span>
                <small>Lucro unitario</small>
                <strong>{{ $financeProfit > 0 ? number_format($financeProfit, 2) : '0.00' }}</strong>
            </div>
            <div class="pc-price-metric pc-price-metric--amber">
                <span><i class="fa-solid fa-tag"></i></span>
                <small>Preco c/ desconto</small>
                <strong>{{ $financeDiscountedWithVat > 0 ? number_format($financeDiscountedWithVat, 2) : '0.00' }}</strong>
            </div>
        </div>
    </div>
</details>
