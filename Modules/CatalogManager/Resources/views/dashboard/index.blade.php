@extends('catalogmanager::layouts.module')

@section('catalogmanager-content')
    <div class="catalog-lsg-hero">
        <div>
            <span class="catalog-lsg-eyebrow">LSG Operational Catalog</span>
            <h1>Catalog Manager</h1>
            <p>Gestao operacional de produtos, lojas, categorias, fornecedores, sync e AI pipeline.</p>
        </div>
    </div>

    <div class="catalog-dashboard-panel-line">
        <div class="catalog-dashboard-panel catalog-dashboard-panel--primary">
            <div class="catalog-dashboard-panel__icon"><i class="fa-solid fa-box-open"></i></div>
            <div><span>Produtos</span><strong>{{ $stats['products'] ?? 0 }}</strong></div>
        </div>

        <div class="catalog-dashboard-panel catalog-dashboard-panel--primary">
            <div class="catalog-dashboard-panel__icon"><i class="fa-solid fa-copyright"></i></div>
            <div><span>Manufacturers</span><strong>{{ $stats['manufacturers'] ?? 0 }}</strong></div>
        </div>

        <div class="catalog-dashboard-panel catalog-dashboard-panel--info">
            <div class="catalog-dashboard-panel__icon"><i class="fa-solid fa-truck-field"></i></div>
            <div><span>Suppliers</span><strong>{{ $stats['suppliers'] ?? 0 }}</strong></div>
        </div>

        <div class="catalog-dashboard-panel catalog-dashboard-panel--primary">
            <div class="catalog-dashboard-panel__icon"><i class="fa-solid fa-store"></i></div>
            <div><span>Lojas</span><strong>{{ $stats['stores'] ?? 0 }}</strong></div>
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

    <div class="catalog-lsg-card">
        <h3>Ultimos produtos</h3>
        <table class="catalog-lsg-table catalog-lsg-datatable">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Referencia</th>
                    <th>Estado</th>
                    <th>Criado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($latestProducts as $product)
                    <tr>
                        <td><a href="{{ route('catalog-manager.products.show', $product->id) }}">{{ $product->name ?: 'Produto #' . $product->id }}</a></td>
                        <td>{{ $product->reference ?: '-' }}</td>
                        <td><span class="catalog-lsg-badge">{{ $product->status }}</span></td>
                        <td>{{ $product->created_at }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
