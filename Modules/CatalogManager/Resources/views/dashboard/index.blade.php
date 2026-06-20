@extends('catalogmanager::layouts.module')

@section('catalogmanager-content')
    <div class="catalog-lsg-hero">
        <div>
            <span class="catalog-lsg-eyebrow">LSG Operational Catalog</span>
            <h1>Catalog Manager</h1>
            <p>Gestao central de dados mestre para fabricantes, fornecedores, categorias e sincronizacao.</p>
        </div>
    </div>

    <div class="catalog-dashboard-panel-line">
        <div class="catalog-dashboard-panel catalog-dashboard-panel--primary">
            <div class="catalog-dashboard-panel__icon"><i class="fa-solid fa-copyright"></i></div>
            <div><span>Manufacturers</span><strong>{{ $stats['manufacturers'] ?? 0 }}</strong></div>
        </div>

        <div class="catalog-dashboard-panel catalog-dashboard-panel--info">
            <div class="catalog-dashboard-panel__icon"><i class="fa-solid fa-truck-field"></i></div>
            <div><span>Suppliers</span><strong>{{ $stats['suppliers'] ?? 0 }}</strong></div>
        </div>

        <div class="catalog-dashboard-panel catalog-dashboard-panel--info">
            <div class="catalog-dashboard-panel__icon"><i class="fa-solid fa-globe"></i></div>
            <div><span>Dominios</span><strong>{{ $stats['monitored_domains'] ?? 0 }}</strong></div>
        </div>

        <div class="catalog-dashboard-panel catalog-dashboard-panel--warning">
            <div class="catalog-dashboard-panel__icon"><i class="fa-solid fa-layer-group"></i></div>
            <div><span>Categorias</span><strong>{{ $stats['categories'] ?? 0 }}</strong></div>
        </div>

        <div class="catalog-dashboard-panel catalog-dashboard-panel--danger">
            <div class="catalog-dashboard-panel__icon"><i class="fa-solid fa-rotate"></i></div>
            <div><span>Sync Pendente</span><strong>{{ $stats['sync_pending'] ?? 0 }}</strong></div>
        </div>
    </div>

    @include('catalogmanager::components.action-panels.grid', ['panels' => $actionPanels])
    @include('catalogmanager::components.issue-panels.grid', ['panels' => $issuePanels])
@endsection
