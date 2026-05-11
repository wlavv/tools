@extends('catalogmanager::layouts.module')

@section('catalogmanager-content')
    @php
        $productLabel = $product->name ?: 'Produto #' . $product->id;
        $completeness = [
            'Master' => !empty($product->name) && !empty($product->reference),
            'EAN' => !empty($product->ean13),
            'Housing' => !empty($product->housing),
            'Marca' => !empty($product->manufacturer_id),
            'Supplier' => $suppliers->isNotEmpty(),
            'Loja' => ($builderStats['stores_created'] ?? 0) > 0,
            'Conteudo' => ($builderStats['missing_content'] ?? 0) === 0 && ($builderStats['stores_total'] ?? 0) > 0,
            'Preco' => ($builderStats['missing_price'] ?? 0) === 0 && ($builderStats['stores_total'] ?? 0) > 0,
            'Categoria' => ($builderStats['missing_category'] ?? 0) === 0 && ($builderStats['stores_total'] ?? 0) > 0,
        ];
    @endphp

    <div class="catalog-lsg-hero">
        <div>
            <span class="catalog-lsg-eyebrow">Product Builder</span>
            <h1>{{ $productLabel }}</h1>
            <p>
                {{ $product->reference ?: 'Sem referencia' }}
                · {{ $product->ean13 ?: 'Sem EAN' }}
                · {{ $product->manufacturer_name ?: 'Sem marca' }}
            </p>
        </div>
        <div class="catalog-lsg-actions">
            <a href="{{ route('catalog-manager.products.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-angle-left"></i> Voltar
            </a>
            <a href="{{ route('catalog-manager.products.edit', $product->id) }}" class="btn btn-outline-warning">
                <i class="fa-solid fa-pencil"></i> Editar
            </a>
        </div>
    </div>

    <div class="catalog-lsg-grid">
        <div class="catalog-lsg-card catalog-lsg-kpi"><span>Estado</span><strong>{{ $product->status }}</strong></div>
        <div class="catalog-lsg-card catalog-lsg-kpi"><span>Lojas</span><strong>{{ $builderStats['stores_created'] ?? 0 }}/{{ $builderStats['stores_total'] ?? 0 }}</strong></div>
        <div class="catalog-lsg-card catalog-lsg-kpi"><span>Publicado</span><strong>{{ $builderStats['stores_published'] ?? 0 }}</strong></div>
        <div class="catalog-lsg-card catalog-lsg-kpi"><span>Issues</span><strong>{{ ($builderStats['missing_content'] ?? 0) + ($builderStats['missing_price'] ?? 0) + ($builderStats['missing_category'] ?? 0) }}</strong></div>
    </div>

    <div class="catalog-lsg-card">
        <div class="catalog-builder-head">
            <div>
                <h3>Readiness checklist</h3>
                <p>Estado operacional antes de publicar ou colocar em sync.</p>
            </div>
            <span class="catalog-lsg-badge">{{ collect($completeness)->filter()->count() }}/{{ count($completeness) }} ok</span>
        </div>
        <div class="catalog-builder-checks">
            @foreach($completeness as $label => $isOk)
                <span class="catalog-builder-check {{ $isOk ? 'is-ok' : 'is-missing' }}">
                    <i class="fa-solid {{ $isOk ? 'fa-check' : 'fa-triangle-exclamation' }}"></i>
                    {{ $label }}
                </span>
            @endforeach
        </div>
    </div>

    <div class="catalog-builder-layout">
        <div class="catalog-lsg-card">
            <h3>Master data</h3>
            <div class="catalog-builder-kv"><span>ID</span><strong>{{ $product->id }}</strong></div>
            <div class="catalog-builder-kv"><span>Nome</span><strong>{{ $productLabel }}</strong></div>
            <div class="catalog-builder-kv"><span>Referencia</span><strong>{{ $product->reference ?: '—' }}</strong></div>
            <div class="catalog-builder-kv"><span>SKU interno</span><strong>{{ $product->internal_sku ?: '—' }}</strong></div>
            <div class="catalog-builder-kv"><span>EAN13</span><strong>{{ $product->ean13 ?: '—' }}</strong></div>
            <div class="catalog-builder-kv"><span>Marca</span><strong>{{ $product->manufacturer_name ?: '—' }}</strong></div>
            <div class="catalog-builder-kv"><span>Tipo</span><strong>{{ $product->type ?: 'simple' }}</strong></div>
            <div class="catalog-builder-kv"><span>Housing</span><strong>{{ $product->housing ?: '—' }}</strong></div>
        </div>

        <div class="catalog-lsg-card">
            <h3>Dimensoes e notas</h3>
            <div class="catalog-builder-kv"><span>Peso</span><strong>{{ $product->weight ?: '—' }}</strong></div>
            <div class="catalog-builder-kv"><span>Largura</span><strong>{{ $product->width ?: '—' }}</strong></div>
            <div class="catalog-builder-kv"><span>Altura</span><strong>{{ $product->height ?: '—' }}</strong></div>
            <div class="catalog-builder-kv"><span>Profundidade</span><strong>{{ $product->depth ?: '—' }}</strong></div>
            <div class="catalog-builder-notes">
                {{ $product->internal_notes ?: 'Sem notas internas.' }}
            </div>
        </div>
    </div>

    <div class="catalog-lsg-card">
        <div class="catalog-builder-head">
            <div>
                <h3>Store Publication Matrix</h3>
                <p>Mostra o estado do produto em cada loja, incluindo conteudo, preco e categorias.</p>
            </div>
            <a href="{{ route('catalog-manager.stores.index') }}" class="btn btn-outline-primary">
                <i class="fa-solid fa-store"></i> Lojas
            </a>
        </div>
        <table class="catalog-lsg-table catalog-lsg-datatable">
            <thead>
                <tr>
                    <th>Loja</th>
                    <th>Registo</th>
                    <th>Estado</th>
                    <th>Publicacao</th>
                    <th>Conteudo</th>
                    <th>Categoria</th>
                    <th>Preco</th>
                    <th>Flags</th>
                </tr>
            </thead>
            <tbody>
                @forelse($storeMatrix as $storeRow)
                    <tr>
                        <td>
                            <strong>{{ $storeRow->store_name }}</strong>
                            <small class="catalog-builder-muted">{{ $storeRow->store_code }} · {{ $storeRow->store_locale }}</small>
                        </td>
                        <td>
                            @if($storeRow->store_product_id)
                                <span class="catalog-lsg-badge">#{{ $storeRow->store_product_id }}</span>
                            @else
                                <span class="catalog-lsg-badge catalog-lsg-badge--danger">Nao criado</span>
                            @endif
                        </td>
                        <td><span class="catalog-lsg-badge">{{ $storeRow->status }}</span></td>
                        <td>
                            <span class="catalog-builder-dot {{ $storeRow->is_published ? 'is-ok' : 'is-missing' }}"></span>
                            {{ $storeRow->is_published ? 'Publicado' : 'Nao publicado' }}
                        </td>
                        <td>
                            <span class="catalog-builder-dot {{ $storeRow->has_content ? 'is-ok' : 'is-missing' }}"></span>
                            {{ $storeRow->has_content ? $storeRow->content_count . ' locale(s)' : 'Em falta' }}
                        </td>
                        <td>
                            <span class="catalog-builder-dot {{ $storeRow->has_category ? 'is-ok' : 'is-missing' }}"></span>
                            {{ $storeRow->has_category ? $storeRow->category_count . ' categoria(s)' : 'Em falta' }}
                        </td>
                        <td>
                            @if($storeRow->has_price)
                                {{ number_format((float) $storeRow->price, 2, ',', ' ') }} {{ $storeRow->store_currency }}
                            @else
                                <span class="catalog-builder-muted">Em falta</span>
                            @endif
                        </td>
                        <td>
                            <div class="catalog-builder-flags">
                                <span class="{{ $storeRow->active ? 'is-ok' : '' }}">active</span>
                                <span class="{{ $storeRow->visible ? 'is-ok' : '' }}">visible</span>
                                <span class="{{ $storeRow->available_for_order ? 'is-ok' : '' }}">order</span>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8">Nao existem lojas configuradas ou as tabelas de loja ainda nao existem.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="catalog-builder-layout">
        <div class="catalog-lsg-card">
            <div class="catalog-builder-head">
                <div>
                    <h3>Fornecedores</h3>
                    <p>Relações globais do produto com suppliers.</p>
                </div>
                <a href="{{ route('catalog-manager.suppliers.index') }}" class="btn btn-outline-primary">
                    <i class="fa-solid fa-truck-field"></i> Suppliers
                </a>
            </div>
            <table class="catalog-lsg-table catalog-lsg-datatable">
                <thead>
                    <tr>
                        <th>Fornecedor</th>
                        <th>Ref.</th>
                        <th>Custo</th>
                        <th>Default</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                        <tr>
                            <td>{{ $supplier->supplier_name }}</td>
                            <td>{{ $supplier->supplier_reference ?: '—' }}</td>
                            <td>{{ $supplier->cost !== null ? number_format((float) $supplier->cost, 2, ',', ' ') . ' ' . $supplier->currency : '—' }}</td>
                            <td>{{ $supplier->is_default ? 'Sim' : 'Nao' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">Sem fornecedores associados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="catalog-lsg-card">
            <h3>Media / Uploads</h3>
            <form class="dropzone catalog-lsg-dropzone"
                  data-url="#"
                  data-param="file"
                  data-accepted-files="image/*,.pdf"
                  data-max-filesize="8">
                @csrf
                <div class="dz-message">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    Arrasta imagens, manuais ou documentos do produto para aqui.
                </div>
            </form>
            <p class="catalog-builder-muted" style="margin-top:10px">
                Zona preparada para endpoint real de media. Nesta fase nao executa alteracoes destrutivas.
            </p>
        </div>
    </div>

    <div class="catalog-lsg-card">
        <h3>Activity log</h3>
        <table class="catalog-lsg-table catalog-lsg-datatable">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Acao</th>
                    <th>User</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->created_at }}</td>
                        <td>{{ $log->action }}</td>
                        <td>{{ $log->user_id ?: '—' }}</td>
                        <td>{{ $log->ip ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">Sem logs para este produto.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
