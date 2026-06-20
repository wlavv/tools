@extends('layouts.app')

@push('styles')
    @include('areas.multiStore.includes.css')
@endpush

@push('scripts')
    @include('areas.multiStore.includes.js')
@endpush

@section('content')

    <div class="customPanel multistore-dashboard-panel">
        <div class="multistore-dashboard-panel__head">
            <div>
                <span class="multistore-dashboard-panel__eyebrow">Store's dashboard</span>
                <h3>Lojas e PageSpeed Insights</h3>
            </div>
            <a href="{{ route('lsg.site_manager.sites.index') }}" class="btn btn-outline-primary">
                <i class="fa-solid fa-store"></i> Ver lojas
            </a>
        </div>

        <div class="multistore-store-grid">
            @forelse($stores ?? collect() as $store)
                <a class="multistore-store-card" href="{{ route('lsg.site_manager.sites.edit', $store->id) }}">
                    <i class="fa-solid fa-store"></i>
                    <strong>{{ $store->name }}</strong>
                    <span>{{ $store->domain ?: '-' }}</span>
                </a>
            @empty
                <div class="text-muted">Sem lojas Product Growth registadas.</div>
            @endforelse
        </div>
    </div>

    <div class="customPanel multistore-dashboard-panel">
        <div class="multistore-dashboard-panel__head">
            <div>
                <span class="multistore-dashboard-panel__eyebrow">Master data</span>
                <h3>Dados partilhados por loja</h3>
            </div>
            @if(Route::has('catalog-manager.dashboard'))
                <a href="{{ route('catalog-manager.dashboard') }}" class="btn btn-outline-primary">
                    <i class="fa-solid fa-database"></i> Catalog Manager
                </a>
            @endif
        </div>

        <div class="multistore-master-grid">
            @if(Route::has('catalog-manager.manufacturers.index'))
                <a class="multistore-master-card" href="{{ route('catalog-manager.manufacturers.index') }}">
                    <span class="multistore-master-card__icon multistore-master-card__icon--manufacturers">
                        <i class="fa-solid fa-industry"></i>
                    </span>
                    <strong>Manufacturers</strong>
                    <small>Fabricantes associados a uma ou varias lojas.</small>
                </a>
            @endif
            @if(Route::has('catalog-manager.suppliers.index'))
                <a class="multistore-master-card" href="{{ route('catalog-manager.suppliers.index') }}">
                    <span class="multistore-master-card__icon multistore-master-card__icon--suppliers">
                        <i class="fa-solid fa-truck-field"></i>
                    </span>
                    <strong>Suppliers</strong>
                    <small>Fornecedores disponiveis por loja.</small>
                </a>
            @endif
            @if(Route::has('catalog-manager.categories.index'))
                <a class="multistore-master-card" href="{{ route('catalog-manager.categories.index') }}">
                    <span class="multistore-master-card__icon multistore-master-card__icon--categories">
                        <i class="fa-solid fa-layer-group"></i>
                    </span>
                    <strong>Categorias</strong>
                    <small>Estrutura comercial replicavel por loja.</small>
                </a>
            @endif
            @if(Route::has('catalog-manager.currencies.index'))
                <a class="multistore-master-card" href="{{ route('catalog-manager.currencies.index') }}">
                    <span class="multistore-master-card__icon multistore-master-card__icon--suppliers">
                        <i class="fa-solid fa-coins"></i>
                    </span>
                    <strong>Currencies</strong>
                    <small>Moedas e taxas de conversao para precos multi-loja.</small>
                </a>
            @endif
            @unless(Route::has('catalog-manager.manufacturers.index') || Route::has('catalog-manager.suppliers.index') || Route::has('catalog-manager.categories.index') || Route::has('catalog-manager.currencies.index'))
                <div class="text-muted">Catalog Manager ainda nao esta ativo neste ambiente.</div>
            @endunless
        </div>
    </div>

@endsection
