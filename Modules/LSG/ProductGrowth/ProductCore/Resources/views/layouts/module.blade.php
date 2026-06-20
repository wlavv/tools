@extends('layouts.app')

@section('content')
@include('product-core::Includes.css')
<div class="product-core-page">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Existem erros.</strong>
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="pc-shell {{ ($hideProductGrowthNavigation ?? false) ? 'pc-shell--full' : '' }}">
        @unless($hideProductGrowthNavigation ?? false)
        <aside class="product-core-card pc-side-panel">
            <nav class="pc-side-menu" aria-label="Product Growth">
                <a href="{{ route('product_growth.product_core.dashboard') }}"><i class="fa-solid fa-chart-line me-2"></i> Dashboard</a>
                <a href="{{ route('product_growth.product_core.products.index') }}"><i class="fa-solid fa-bullhorn me-2"></i> Anuncios</a>
                @if(Route::has('product_growth.workflow_manager.dashboard'))<a href="{{ route('product_growth.workflow_manager.dashboard') }}"><i class="fa-solid fa-user-shield me-2"></i> Administracao</a>@endif
                @if(Route::has('product_growth.store_brand_manager.dashboard'))<a href="{{ route('product_growth.store_brand_manager.dashboard') }}"><i class="fa-solid fa-cart-shopping me-2"></i> Purchase</a>@endif
                @if(Route::has('product_growth.brand_compliance_manager.dashboard'))<a href="{{ route('product_growth.brand_compliance_manager.dashboard') }}"><i class="fa-solid fa-file-invoice-dollar me-2"></i> Finance</a>@endif
                @if(Route::has('product_growth.marketing_content_manager.dashboard'))<a href="{{ route('product_growth.marketing_content_manager.dashboard') }}"><i class="fa-solid fa-tags me-2"></i> Sales</a>@endif
                @if(Route::has('product_growth.creative_asset_manager.dashboard'))<a href="{{ route('product_growth.creative_asset_manager.dashboard') }}"><i class="fa-solid fa-bullhorn me-2"></i> Marketing</a>@endif
                @if(Route::has('product_growth.logistics_manager.dashboard'))<a href="{{ route('product_growth.logistics_manager.dashboard') }}"><i class="fa-solid fa-truck-fast me-2"></i> Logistica</a>@endif
            </nav>
        </aside>
        @endunless

        <main class="pc-main">
            @yield('module-content')
        </main>
    </div>
</div>
@include('product-core::Includes.js')
@endsection
