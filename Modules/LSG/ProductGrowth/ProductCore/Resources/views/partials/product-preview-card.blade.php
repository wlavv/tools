@php
    $product->loadMissing(['assets', 'storeProducts.store', 'brand', 'supplier']);

    $previewAsset = $productPreviewAsset ?? $product->primary_asset;
    $previewStoreProduct = $productPreviewStoreProduct ?? $product->storeProducts->first();
    $salesData = data_get($product->metadata ?? [], 'department_content.marketing-content-manager', []);
    $assetData = $productPreviewAssetData ?? [];

    $previewImage = $productPreviewImage
        ?? ($assetData['cover_image'] ?? null)
        ?? ($assetData['main_image'] ?? null)
        ?? $previewAsset?->public_url;
    $previewImagePath = $previewImage ? parse_url($previewImage, PHP_URL_PATH) : null;
    if ($previewImagePath && \Illuminate\Support\Str::contains($previewImagePath, '/storage/')) {
        $previewImage = '/storage/' . \Illuminate\Support\Str::after($previewImagePath, '/storage/');
    } elseif (is_string($previewImage) && \Illuminate\Support\Str::startsWith($previewImage, 'storage/')) {
        $previewImage = '/' . $previewImage;
    }
    $previewTitle = $productPreviewTitle
        ?? ($previewStoreProduct?->name ?: ($salesData['name'] ?? $product->name));
    $previewDescription = $productPreviewDescription
        ?? ($previewStoreProduct?->short_description ?: ($salesData['short_description'] ?? $product->description));
    $previewManufacturer = data_get($product->metadata ?? [], 'product_growth.manufacturer_name') ?? $product->brand?->name;
    $previewSupplier = data_get($product->metadata ?? [], 'product_growth.supplier_name') ?? $product->supplier?->name;
    $previewWorkflowAreas = collect($workflowAreas ?? config('product-core.workflow_areas.areas', []))
        ->only(['purchase', 'finance', 'sales', 'marketing', 'logistics']);
    $previewWorkflowStatuses = [
        'approved' => ['label' => 'approved', 'class' => 'success'],
        'rejected' => ['label' => 'rejected', 'class' => 'danger'],
        'submitted' => ['label' => 'Submitted', 'class' => 'warning'],
        'resubmitted' => ['label' => 'Submitted', 'class' => 'warning'],
        'in_review' => ['label' => 'In review', 'class' => 'review'],
        'pending' => ['label' => 'Pending', 'class' => 'muted'],
    ];
@endphp

<div class="pc-product-preview pc-department-preview">
    <div class="pc-product-preview__media">
        @if($previewImage)
            <div
                class="pc-product-preview__image"
                role="img"
                aria-label="{{ $previewTitle }}"
                style="background-image:url('{{ $previewImage }}')"
            ></div>
        @else
            <div class="pc-product-preview__placeholder"><i class="fa-solid fa-image"></i></div>
        @endif
    </div>
    <div class="pc-product-preview__content">
        <h3>{{ $previewTitle }}</h3>
        @if($previewDescription)
            <p>{{ \Illuminate\Support\Str::limit(strip_tags((string) $previewDescription), 220) }}</p>
        @endif
        <div class="pc-product-preview__meta">
            <span><i class="fa-solid fa-barcode"></i>{{ $product->reference ?: ($product->internal_sku ?: 'Sem referencia') }}</span>
            <span><i class="fa-solid fa-copyright"></i>{{ $previewManufacturer ?: 'Sem fabricante' }}</span>
            <span><i class="fa-solid fa-truck-field"></i>{{ $previewSupplier ?: 'Sem fornecedor' }}</span>
            <span><i class="fa-solid fa-euro-sign"></i>{{ $product->base_price ? number_format((float) $product->base_price, 2) : 'Sem preco' }}</span>
        </div>
        <div class="pc-product-preview__badges">
            <span class="pc-badge">{{ config('product-core.states.' . $product->status, $product->status) }}</span>
            <span class="pc-badge">{{ $product->assets->count() }} assets</span>
            <span class="pc-badge">{{ $product->storeProducts->count() }} lojas</span>
        </div>
        @if($previewWorkflowAreas->isNotEmpty())
            <div class="pc-workflow-preview-table" role="table" aria-label="Estado do workflow por departamento">
                <div class="pc-workflow-preview-table__row pc-workflow-preview-table__row--head" role="row">
                    @foreach($previewWorkflowAreas as $area)
                        <div role="columnheader">{{ $area['label'] ?? 'Departamento' }}</div>
                    @endforeach
                </div>
                <div class="pc-workflow-preview-table__row" role="row">
                    @foreach($previewWorkflowAreas as $areaKey => $area)
                        @php
                            $rawAreaStatus = data_get($product->metadata ?? [], 'department_reviews.' . $areaKey . '.status', 'pending');
                            $areaStatus = $previewWorkflowStatuses[$rawAreaStatus] ?? [
                                'label' => \Illuminate\Support\Str::headline((string) $rawAreaStatus),
                                'class' => 'muted',
                            ];
                        @endphp
                        <div role="cell">
                            <span class="pc-workflow-preview-status pc-workflow-preview-status--{{ $areaStatus['class'] }}">
                                {{ $areaStatus['label'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
