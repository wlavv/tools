@extends('catalogmanager::layouts.module')

@section('catalogmanager-content')
    <div class="catalog-lsg-hero">
        <div>
            <span class="catalog-lsg-eyebrow">Catalog Manager</span>
            <h1>Caracteristicas</h1>
        </div>
    </div>

    <div class="catalog-lsg-card">
        <table class="catalog-lsg-table catalog-lsg-datatable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Tipo</th>
                    <th>Unidade</th>
                    <th>Filtros</th>
                    <th>Pesquisa</th>
                    <th>SEO</th>
                    <th>Ativa</th>
                    <th>Acoes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($characteristics as $characteristic)
                    <tr>
                        <td>{{ $characteristic->id }}</td>
                        <td>{{ $characteristic->name }}</td>
                        <td>{{ $characteristic->data_type }}</td>
                        <td>{{ $characteristic->unit ?: '-' }}</td>
                        <td>{{ $characteristic->is_filterable ? 'Sim' : 'Nao' }}</td>
                        <td>{{ $characteristic->is_searchable ? 'Sim' : 'Nao' }}</td>
                        <td>{{ $characteristic->is_seo_keyword ? 'Sim' : 'Nao' }}</td>
                        <td>{{ $characteristic->is_active ? 'Sim' : 'Nao' }}</td>
                        <td>
                            <a href="{{ route('catalog-manager.characteristics.edit', $characteristic->id) }}" class="btn btn-sm btn-outline-warning">
                                <i class="fa-solid fa-pencil"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
