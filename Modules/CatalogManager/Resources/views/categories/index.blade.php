@extends('catalogmanager::layouts.module')

@section('catalogmanager-content')
    <div class="catalog-lsg-hero">
        <div>
            <span class="catalog-lsg-eyebrow">Store categories</span>
            <h1>Categorias por Loja</h1>
            <p>Cada loja tem a sua propria arvore de categorias.</p>
        </div>
    </div>

    <div class="catalog-lsg-card">
        <table class="catalog-lsg-table catalog-lsg-datatable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Loja</th>
                    <th>Categoria</th>
                    <th>Parent</th>
                    <th>Ativa</th>
                    <th>Posicao</th>
                    <th>Acoes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $category)
                    <tr>
                        <td>{{ $category->id }}</td>
                        <td>{{ $category->store_name }}</td>
                        <td>{{ $category->name ?: 'Categoria #' . $category->id }}</td>
                        <td>{{ $category->parent_id ?: '-' }}</td>
                        <td>{{ $category->active ? 'Sim' : 'Nao' }}</td>
                        <td>{{ $category->position }}</td>
                        <td>
                            <a href="{{ route('catalog-manager.categories.edit', $category->id) }}" class="btn btn-sm btn-outline-warning">
                                <i class="fa-solid fa-pencil"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
