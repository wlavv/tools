@extends('catalogmanager::layouts.module')

@section('catalogmanager-content')
    <div class="catalog-lsg-hero">
        <div>
            <span class="catalog-lsg-eyebrow">LSG Operational Catalog</span>
            <h1>Catalog Manager</h1>
            <p>Gestão operacional de produtos, lojas, categorias, fornecedores, sync e AI pipeline.</p>
        </div>

    </div>

    <div class="catalog-lsg-grid">
        <div class="catalog-lsg-card catalog-lsg-kpi"><span>Produtos</span><strong>{{ $stats['products'] ?? 0 }}</strong></div>
        <div class="catalog-lsg-card catalog-lsg-kpi"><span>Manufacturers</span><strong>{{ $stats['manufacturers'] ?? 0 }}</strong></div>
        <div class="catalog-lsg-card catalog-lsg-kpi"><span>Suppliers</span><strong>{{ $stats['suppliers'] ?? 0 }}</strong></div>
        <div class="catalog-lsg-card catalog-lsg-kpi"><span>Lojas</span><strong>{{ $stats['stores'] ?? 0 }}</strong></div>
        <div class="catalog-lsg-card catalog-lsg-kpi"><span>Categorias</span><strong>{{ $stats['categories'] ?? 0 }}</strong></div>
        <div class="catalog-lsg-card catalog-lsg-kpi"><span>Sync Pendente</span><strong>{{ $stats['sync_pending'] ?? 0 }}</strong></div>
    </div>

    @include('catalogmanager::components.action-panels.grid', ['panels' => $actionPanels])
    @include('catalogmanager::components.issue-panels.grid', ['panels' => $issuePanels])

    <div class="catalog-lsg-card">
        <h3>Últimos produtos</h3>
        <table class="catalog-lsg-table catalog-lsg-datatable">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Referência</th>
                    <th>Estado</th>
                    <th>Criado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($latestProducts as $product)
                    <tr>
                        <td><a href="{{ route('catalog-manager.products.show', $product->id) }}">{{ $product->name ?: 'Produto #' . $product->id }}</a></td>
                        <td>{{ $product->reference ?: '—' }}</td>
                        <td><span class="catalog-lsg-badge">{{ $product->status }}</span></td>
                        <td>{{ $product->created_at }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

