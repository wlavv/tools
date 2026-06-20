@extends('product-core::layouts.module')

@section('module-content')
<section class="pc-grid">
    @foreach([
        'products' => ['label' => 'Anuncios', 'icon' => 'fa-solid fa-bullhorn', 'tone' => 'blue'],
        'in_review' => ['label' => 'Em revisao', 'icon' => 'fa-solid fa-list-check', 'tone' => 'amber'],
        'approved' => ['label' => 'Aprovados', 'icon' => 'fa-solid fa-check', 'tone' => 'green'],
        'ready_to_sync' => ['label' => 'Prontos sync', 'icon' => 'fa-solid fa-rotate', 'tone' => 'purple'],
    ] as $key => $metric)
        <div class="product-core-card pc-stat pc-stat--{{ $metric['tone'] }}">
            <div>
                <small>{{ $metric['label'] }}</small>
                <strong>
                    @if($key === 'in_review' || $key === 'approved')
                        {{ $productsByStatus[$key] ?? 0 }}
                    @else
                        {{ $stats[$key] ?? 0 }}
                    @endif
                </strong>
            </div>
            <span class="pc-stat-icon"><i class="{{ $metric['icon'] }}"></i></span>
        </div>
    @endforeach
</section>

<section class="product-core-card pc-panel">
    <div class="pc-panel-head">
        <div>
            <h2 class="pc-panel-title">Anuncios em workflow</h2>
            <p class="pc-panel-subtitle">Anuncios recentes e o ponto atual do processo.</p>
        </div>
        <a href="{{ route('product_growth.product_core.products.index') }}" class="lsg-action-btn lsg-action-btn--primary lsg-action-btn--compact">
            <i class="fa-solid fa-eye"></i> Ver todos
        </a>
    </div>

    <div class="product-core-table-wrap">
        <table class="product-core-table">
            <thead>
                <tr>
                    <th>Anuncio</th>
                    <th>Marca</th>
                    <th>Lojas</th>
                    <th>Estado</th>
                    <th>Qualidade</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($recentProducts as $product)
                <tr>
                    <td>
                        <div class="pc-table-title">
                            <strong>{{ $product->name }}</strong>
                            <span>{{ $product->internal_sku }} · {{ $product->reference ?: 'sem ref.' }}</span>
                        </div>
                    </td>
                    <td>{{ $product->brand?->name ?? '-' }}</td>
                    <td>{{ $product->storeProducts->count() }}</td>
                    <td><span class="pc-badge">{{ config('product-core.states.' . $product->status, $product->status) }}</span></td>
                    <td>{{ number_format($product->data_quality_score, 0) }}%</td>
                    <td>
                        <a href="{{ route('product_growth.product_core.products.show', $product) }}" class="lsg-action-btn lsg-action-btn--primary lsg-action-btn--compact">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Sem anuncios em workflow.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>

<section class="pc-dashboard-columns">
    <div class="product-core-card pc-panel">
        <div class="pc-panel-head">
            <div>
                <h2 class="pc-panel-title">Estados</h2>
                <p class="pc-panel-subtitle">Distribuicao atual dos anuncios.</p>
            </div>
        </div>

        <div class="pc-status-list">
            @forelse(config('product-core.states') as $status => $label)
                <div class="pc-status-row">
                    <span>{{ $label }}</span>
                    <strong>{{ $productsByStatus[$status] ?? 0 }}</strong>
                </div>
            @empty
                <span class="text-muted">Sem estados configurados.</span>
            @endforelse
        </div>
    </div>

    <div class="product-core-card pc-panel">
        <div class="pc-panel-head">
            <div>
                <h2 class="pc-panel-title">Zonas premium</h2>
                <p class="pc-panel-subtitle">Entrada rapida para extensoes, publicacao, integracoes e performance.</p>
            </div>
        </div>

        <div class="pc-work-area-list">
            @if(Route::has('product_growth.webcatalogue_premium_layer.dashboard'))<a href="{{ route('product_growth.webcatalogue_premium_layer.dashboard') }}"><i class="fa-solid fa-cube"></i><span>WebCatalogue Premium</span><strong>3D / AR</strong></a>@endif
            @if(Route::has('product_growth.product_buzz_manager.dashboard'))<a href="{{ route('product_growth.product_buzz_manager.dashboard') }}"><i class="fa-solid fa-bullhorn"></i><span>Product Buzz</span><strong>Launch</strong></a>@endif
            @if(Route::has('product_growth.ai_ads_manager.dashboard'))<a href="{{ route('product_growth.ai_ads_manager.dashboard') }}"><i class="fa-solid fa-rectangle-ad"></i><span>AI Ads</span><strong>Campanhas</strong></a>@endif
            @if(Route::has('product_growth.product_evolution_manager.dashboard'))<a href="{{ route('product_growth.product_evolution_manager.dashboard') }}"><i class="fa-solid fa-chart-line"></i><span>Product Evolution</span><strong>Iteracao</strong></a>@endif
            @if(Route::has('product_growth.publisher_export_manager.dashboard'))<a href="{{ route('product_growth.publisher_export_manager.dashboard') }}"><i class="fa-solid fa-upload"></i><span>Publisher</span><strong>Export</strong></a>@endif
            @if(Route::has('product_growth.prestashop_bridge.dashboard'))<a href="{{ route('product_growth.prestashop_bridge.dashboard') }}"><i class="fa-solid fa-plug-circle-bolt"></i><span>PrestaShop Bridge</span><strong>Sync</strong></a>@endif
            @if(Route::has('product_growth.performance_manager.dashboard'))<a href="{{ route('product_growth.performance_manager.dashboard') }}"><i class="fa-solid fa-gauge-high"></i><span>Performance</span><strong>Metrica</strong></a>@endif
        </div>
    </div>
</section>
@endsection
