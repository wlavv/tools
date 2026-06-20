@extends('product-core::layouts.module')

@section('module-content')
<section class="product-core-card pc-panel">
    <div class="pc-panel-head">
        <div>
            <h2 class="pc-panel-title">Pesquisa e filtros</h2>
            <p class="pc-panel-subtitle">Lista de anuncios que entram no workflow de criacao.</p>
        </div>
    </div>

    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="pc-label">Pesquisa</label>
            <input class="pc-input" name="q" value="{{ request('q') }}" placeholder="Nome, SKU, referencia, EAN">
        </div>
        <div class="col-md-3">
            <label class="pc-label">Estado</label>
            <select class="pc-select" name="status">
                <option value="">Todos</option>
                @foreach(config('product-core.states') as $key=>$label)
                    <option value="{{ $key }}" @selected(request('status')===$key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-5">
            <button class="lsg-action-btn lsg-action-btn--primary w-100"><i class="fa-solid fa-magnifying-glass"></i> Filtrar</button>
        </div>
    </form>
</section>

<section class="product-core-card pc-panel">
    <div class="pc-panel-head">
        <div>
            <h2 class="pc-panel-title">Anuncios</h2>
            <p class="pc-panel-subtitle">{{ $products->total() }} registos encontrados.</p>
        </div>
    </div>

    <div class="product-core-table-wrap">
        <table class="product-core-table">
            <thead><tr><th>Anuncio</th><th>Fabricante</th><th>Fornecedor</th><th>Preco</th><th>Lojas</th><th>Estado</th><th>Sync</th><th>Acoes</th></tr></thead>
            <tbody>
            @forelse($products as $product)
                <tr>
                    <td><div class="pc-table-title"><strong>{{ $product->name }}</strong><span>{{ $product->internal_sku }} · {{ $product->reference ?: 'sem ref.' }}</span></div></td>
                    <td>{{ data_get($product->metadata ?? [], 'product_growth.manufacturer_name') ?? $product->brand?->name ?? '-' }}</td>
                    <td>{{ data_get($product->metadata ?? [], 'product_growth.supplier_name') ?? $product->supplier?->name ?? '-' }}</td>
                    <td>{{ $product->base_price ? number_format($product->base_price,2,',','.') . ' EUR' : '-' }}</td>
                    <td>{{ $product->storeProducts->count() }}</td>
                    <td><span class="pc-badge">{{ $product->status }}</span></td>
                    <td>{{ $product->storeProducts->whereIn('sync_status',['ready_to_sync','needs_resync','sync_failed'])->count() }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('product_growth.product_core.products.show',$product) }}" class="lsg-action-btn lsg-action-btn--primary lsg-action-btn--compact"><i class="fa-solid fa-eye"></i></a>
                            <a href="{{ route('product_growth.product_core.products.edit',$product) }}" class="lsg-action-btn lsg-action-btn--warning lsg-action-btn--compact"><i class="fa-solid fa-pencil"></i></a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Sem anuncios.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $products->links() }}</div>
</section>
@endsection
