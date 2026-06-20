@extends('layouts.app')

@push('styles')
    @include('areas.lsg.partials.section-cards-css')
@endpush

@section('content')
    <div class="lsg-section-panel">
        <div class="lsg-section-head">
            <div>
                <span>LSG</span>
                <h3>Stores</h3>
            </div>
        </div>

        <div class="lsg-section-grid">
            <section class="lsg-section-card">
                <div class="lsg-section-card__head"><i class="fa-solid fa-diagram-project"></i><strong>Product Growth</strong></div>
                <span>Criação, gestão e evolução de produtos.</span>
                <div class="lsg-section-actions">
                    @if(Route::has('product_growth.product_core.dashboard'))<a class="btn btn-sm btn-outline-primary" href="{{ route('product_growth.product_core.dashboard') }}">Dashboard</a>@endif
                </div>
            </section>

            <section class="lsg-section-card">
                <div class="lsg-section-card__head"><i class="fa-solid fa-store"></i><strong>Lojas e canais</strong></div>
                <span>Sites/storefronts e canais comerciais ligados ao grupo.</span>
                <div class="lsg-section-actions">
                    @if(Route::has('lsg.site_manager.sites.index'))<a class="btn btn-sm btn-outline-primary" href="{{ route('lsg.site_manager.sites.index') }}">Sites</a>@endif
                </div>
            </section>

        </div>
    </div>
@endsection
