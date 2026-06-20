@extends('catalogmanager::layouts.module')

@section('catalogmanager-content')
    <div class="catalog-lsg-hero">
        <div>
            <span class="catalog-lsg-eyebrow">Combinações</span>
            <h1>Atributos de Combinação</h1>
        </div>
    </div>

    <div class="catalog-lsg-card">
        <div class="catalog-lsg-table-wrap">
            <table class="table table-striped catalog-lsg-datatable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Slug</th>
                        <th>Tipo</th>
                        <th>Preço</th>
                        <th>Stock</th>
                        <th>Obrigatório</th>
                        <th>Ativo</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attributes as $attribute)
                        <tr>
                            <td>{{ $attribute->id }}</td>
                            <td>{{ $attribute->name }}</td>
                            <td><code>{{ $attribute->slug }}</code></td>
                            <td>{{ $attribute->display_type }}</td>
                            <td>{{ $attribute->affects_price ? 'Sim' : 'Não' }}</td>
                            <td>{{ $attribute->affects_stock ? 'Sim' : 'Não' }}</td>
                            <td>{{ $attribute->is_required ? 'Sim' : 'Não' }}</td>
                            <td>{{ $attribute->is_active ? 'Sim' : 'Não' }}</td>
                            <td>
                                <a href="{{ route('catalog-manager.combination-attributes.edit', $attribute->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
