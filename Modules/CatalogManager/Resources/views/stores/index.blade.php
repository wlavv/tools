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
<th>Código</th>
<th>Nome</th>
<th>Domínio</th>
<th>Ativo</th>

                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stores as $store)
                    <tr>
                        <td>{{ $store->id ?? '—' }}</td>
<td>{{ $store->code ?? '—' }}</td>
<td>{{ $store->name ?? '—' }}</td>
<td>{{ $store->domain ?? '—' }}</td>
<td>{{ $store->active ?? '—' }}</td>

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

