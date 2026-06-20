@extends('catalogmanager::layouts.module')

@section('catalogmanager-content')
    <div class="catalog-lsg-hero">
        <div>
            <span class="catalog-lsg-eyebrow">Catalog Manager</span>
            <h1>Fornecedores</h1>
        </div>
    </div>

    <div class="catalog-lsg-card">
        <table class="catalog-lsg-table catalog-lsg-datatable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Codigo</th>
                    <th>Moeda</th>
                    <th>Lojas</th>
                    <th>Ativo</th>
                    <th>Acoes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($suppliers as $supplier)
                    <tr>
                        <td>{{ $supplier->id ?? '-' }}</td>
                        <td>{{ $supplier->name ?? '-' }}</td>
                        <td>{{ $supplier->code ?? '-' }}</td>
                        <td>{{ $supplier->currency ?? '-' }}</td>
                        <td>{{ $supplier->store_names ?? 'Todas / nao definido' }}</td>
                        <td>{{ !empty($supplier->active) ? 'Sim' : 'Nao' }}</td>
                        <td>
                            <a href="{{ route('catalog-manager.suppliers.edit', $supplier->id) }}" class="btn btn-sm btn-outline-warning">
                                <i class="fa-solid fa-pencil"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
