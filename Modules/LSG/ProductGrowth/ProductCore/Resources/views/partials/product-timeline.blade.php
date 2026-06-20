@php
    $timelineStatus = $product->status ?? null;
    $timelineCurrentStage = $productGrowthTimelineCurrentStage ?? null;
    $timelineShowPreviewButton = $productGrowthTimelineShowPreviewButton ?? false;
    $timelineSteps = [
        ['status' => 'draft', 'stage' => 'product-core', 'label' => 'Admin', 'hint' => 'base do anuncio', 'icon' => 'fa-solid fa-box', 'route' => 'product_growth.product_core.products.edit'],
        ['status' => 'purchase', 'stage' => 'store-brand-manager', 'label' => 'Purchase', 'hint' => 'compra e fornecedor', 'icon' => 'fa-solid fa-cart-shopping', 'route' => 'product_growth.store_brand_manager.product.edit'],
        ['status' => 'finance', 'stage' => 'brand-compliance-manager', 'label' => 'Finance', 'hint' => 'fiscal e financeiro', 'icon' => 'fa-solid fa-file-invoice-dollar', 'route' => 'product_growth.brand_compliance_manager.product.edit'],
        ['status' => 'sales', 'stage' => 'marketing-content-manager', 'label' => 'Sales', 'hint' => 'preco e canais', 'icon' => 'fa-solid fa-tags', 'route' => 'product_growth.marketing_content_manager.product.edit'],
        ['status' => 'marketing', 'stage' => 'creative-asset-manager', 'label' => 'Marketing', 'hint' => 'assets visuais', 'icon' => 'fa-solid fa-bullhorn', 'route' => 'product_growth.creative_asset_manager.product.edit'],
        ['status' => 'logistics', 'stage' => 'logistics-manager', 'label' => 'Logistica', 'hint' => 'stock e envio', 'icon' => 'fa-solid fa-truck-fast', 'route' => 'product_growth.logistics_manager.product.edit'],
        ['status' => 'approved', 'stage' => 'workflow-manager', 'label' => 'Admin', 'hint' => 'validar e publicar', 'icon' => 'fa-solid fa-check-double', 'route' => 'product_growth.workflow_manager.product.edit'],
    ];
@endphp

<section class="product-core-card pc-panel">
    @if($timelineShowPreviewButton)
    <div class="pc-panel-head">
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <button type="button" class="lsg-action-btn lsg-action-btn--primary lsg-action-btn--compact" data-product-preview-open>
                <i class="fa-solid fa-eye"></i> Preview BO
            </button>
        </div>
    </div>
    @endif

    <div class="pc-timeline">
        @foreach($timelineSteps as $step)
            @php
                $stepUrl = Route::has($step['route']) ? route($step['route'], $product) : '#';
                $isActive = $timelineCurrentStage
                    ? $timelineCurrentStage === $step['stage']
                    : $timelineStatus === $step['status'];
            @endphp
            <a class="pc-timeline-step {{ $isActive ? 'is-active' : '' }}" href="{{ $stepUrl }}">
                <span class="pc-timeline-icon"><i class="{{ $step['icon'] }}"></i></span>
                <strong>{{ $step['label'] }}</strong>
                <small>{{ $step['hint'] }}</small>
            </a>
        @endforeach
    </div>
</section>
