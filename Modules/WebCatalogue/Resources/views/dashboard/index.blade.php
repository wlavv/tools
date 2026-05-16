@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell">
@if(session('success'))<div class="wc-alert">{{ session('success') }}</div>@endif

<div class="wc-hero-card">
    <div>
        <div class="wc-eyebrow"><i class="fa-solid fa-cubes-stacked"></i> WebCatalogue Foundation</div>
        <h2>Visual B2B / ecommerce catalogue platform</h2>
        <p>Stores, catalogues, products, 3D/AR/VR tools and import flows prepared for sustainable growth.</p>
    </div>
    <div class="wc-hero-actions">
        <a class="wc-primary-btn" href="{{ route('webcatalogue.imports.index') }}"><i class="fa-solid fa-file-import"></i> Import Center</a>
        <a class="wc-secondary-btn" href="{{ route('webcatalogue.products.create') }}"><i class="fa-solid fa-plus"></i> New Product</a>
    </div>
</div>

<div class="wc-grid wc-kpi-grid">
    <div class="wc-kpi-card wc-kpi-card-store">
        <div class="wc-kpi-content"><h3>Stores</h3><div class="wc-kpi">{{ $storesCount }}</div><div class="wc-muted">Commercial spaces / clients</div></div>
        <i class="fa-solid fa-store wc-kpi-bg-icon"></i>
    </div>
    <div class="wc-kpi-card wc-kpi-card-catalogue">
        <div class="wc-kpi-content"><h3>Catalogues</h3><div class="wc-kpi">{{ $cataloguesCount }}</div><div class="wc-muted">Visual catalogues</div></div>
        <i class="fa-solid fa-book-open wc-kpi-bg-icon"></i>
    </div>
    <div class="wc-kpi-card wc-kpi-card-product">
        <div class="wc-kpi-content"><h3>Products</h3><div class="wc-kpi">{{ $productsCount }}</div><div class="wc-muted">{{ $readyProductsCount ?? 0 }} ready - {{ $needsWorkProductsCount ?? 0 }} need work</div></div>
        <i class="fa-solid fa-boxes-stacked wc-kpi-bg-icon"></i>
    </div>
    <div class="wc-kpi-card wc-kpi-card-resource">
        <div class="wc-kpi-content"><h3>Resources</h3><div class="wc-kpi">{{ $resourcesCount }}</div><div class="wc-muted">Images, 3D, AR, VR, audio, PDFs</div></div>
        <i class="fa-solid fa-photo-film wc-kpi-bg-icon"></i>
    </div>
    <div class="wc-kpi-card wc-kpi-card-product">
        <div class="wc-kpi-content"><h3>Recognition</h3><div class="wc-kpi">{{ $recognitionSessionsCount ?? 0 }}</div><div class="wc-muted">{{ $recognitionLeadsCount ?? 0 }} new product leads</div></div>
        <i class="fa-solid fa-camera wc-kpi-bg-icon"></i>
    </div>
</div>

<div class="wc-card wc-platform-card">
    <div class="wc-platform-counter-grid">
        @foreach($platformCounters ?? [] as $counter)
            <a class="wc-platform-counter" href="{{ $counter['url'] ?? '#' }}">
                <i class="{{ $counter['icon'] }}"></i>
                <div>
                    <strong>{{ $counter['value'] }}</strong>
                    <span>{{ $counter['label'] }}</span>
                    <small>{{ $counter['hint'] }}</small>
                </div>
            </a>
        @endforeach
    </div>
</div>

<div class="wc-card" style="margin-top:16px">
    <div class="wc-area-grid wc-area-grid-wide">
        <a class="wc-area-card wc-area-store" href="{{ route('webcatalogue.stores.index') }}"><i class="fa-solid fa-store"></i><strong>Stores</strong><span>Multi-store base</span><small>Lojas, clientes, domínio, branding e identidade comercial.</small></a>
        <a class="wc-area-card wc-area-catalogue" href="{{ route('webcatalogue.catalogues.index') }}"><i class="fa-solid fa-book-open"></i><strong>Catalogues</strong><span>Visual catalogues</span><small>Catálogos visuais, páginas públicas, publicação e organização comercial.</small></a>
        <a class="wc-area-card wc-area-product" href="{{ route('webcatalogue.products.index') }}"><i class="fa-solid fa-boxes-stacked"></i><strong>Products</strong><span>Manual or imported</span><small>Produtos criados manualmente ou importados por CSV/API futura.</small></a>
        <a class="wc-area-card wc-area-import" href="{{ route('webcatalogue.imports.index') }}"><i class="fa-solid fa-file-csv"></i><strong>CSV Import Center</strong><span>Guided imports</span><small>Escolher tipo, descarregar template, preencher, upload e registar batch.</small></a>
        <a class="wc-area-card wc-area-resource" href="{{ route('webcatalogue.studio.3d_jobs.index') }}"><i class="fa-solid fa-wand-magic-sparkles"></i><strong>3D Studio</strong><span>Image to 3D pipeline</span><small>Jobs para gerar/associar modelos 3D, exports AR e ficheiros VR aos produtos.</small></a>
        <a class="wc-area-card wc-area-recognition" href="{{ route('webcatalogue.recognition.index') }}"><i class="fa-solid fa-camera"></i><strong>Visual Recognition</strong><span>Camera discovery</span><small>Captura via câmara, produtos não encontrados, leads de prospeção e procura futura por IA.</small></a>
    </div>
</div>

</div>
@endsection
