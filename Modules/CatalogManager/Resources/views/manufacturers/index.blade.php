@extends('catalogmanager::layouts.module')

@section('catalogmanager-content')
    <div class="catalog-lsg-hero">
        <div>
            <span class="catalog-lsg-eyebrow">Catalog Manager</span>
            <h1>Manufacturers / Marcas</h1>
        </div>
    </div>

    <div class="catalog-lsg-card">
        <table class="catalog-lsg-table catalog-lsg-datatable">
            <thead>
                <tr>
                    <th>ID</th>
<th>Nome</th>
<th>Website</th>
<th>Ativo</th>

                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($manufacturers as $manufacturer)
                    <tr>
                        <td>{{ $manufacturer->id ?? '—' }}</td>
<td>{{ $manufacturer->name ?? '—' }}</td>
<td>{{ $manufacturer->website ?? '—' }}</td>
<td>{{ $manufacturer->active ?? '—' }}</td>

                        <td>
                            <a href="{{ route('catalog-manager.manufacturers.edit', $manufacturer->id) }}" class="btn btn-sm btn-outline-warning">
                                <i class="fa-solid fa-pencil"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        
    </div>
@endsection

