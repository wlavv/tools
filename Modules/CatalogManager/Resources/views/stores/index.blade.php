@extends('catalogmanager::layouts.module')

@section('catalogmanager-content')
    <div class="catalog-lsg-hero">
        <div>
            <span class="catalog-lsg-eyebrow">Catalog Manager</span>
            <h1>Lojas</h1>
        </div>
    </div>

    <div class="catalog-lsg-card">
        <table class="catalog-lsg-table catalog-lsg-datatable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Codigo</th>
                    <th>Nome</th>
                    <th>Dominio</th>
                    <th>Tipo</th>
                    <th>Area LSG</th>
                    <th>Ativo</th>
                    <th>Acoes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stores as $store)
                    <tr>
                        <td>{{ $store->id ?? '-' }}</td>
                        <td>{{ $store->code ?? '-' }}</td>
                        <td>{{ $store->name ?? '-' }}</td>
                        <td>{{ $store->domain ?? '-' }}</td>
                        <td>
                            <span class="catalog-lsg-badge">
                                {{ ($store->record_type ?? 'store') === 'domain' ? 'Dominio' : 'Loja' }}
                            </span>
                        </td>
                        <td>
                            <span class="catalog-lsg-badge">
                                {{ [
                                    'store' => 'Sites lojas',
                                    'service' => 'Sites servicos',
                                    'showcase' => 'Sites mostra',
                                    'group' => 'Site grupo',
                                    'labs' => 'Site labs',
                                ][$store->site_kind ?? 'store'] ?? 'Sites lojas' }}
                            </span>
                        </td>
                        <td>{{ $store->active ?? '-' }}</td>
                        <td>
                            <a href="{{ route('catalog-manager.stores.edit', $store->id) }}" class="btn btn-sm btn-outline-warning">
                                <i class="fa-solid fa-pencil"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
