@extends('catalogmanager::layouts.module')

@section('catalogmanager-content')
    <div class="catalog-lsg-hero">
        <div>
            <span class="catalog-lsg-eyebrow">Catalog Manager</span>
            <h1>Produtos</h1>
            <p>Produto master operacional independente das lojas.</p>
        </div>
        @include('catalogmanager::partials.actions')
    </div>

    <div class="catalog-lsg-card">

        <table class="catalog-lsg-table catalog-lsg-datatable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Produto</th>
                    <th>Referência</th>
                    <th>EAN</th>
                    <th>Marca</th>
                    <th>Estado</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                    <tr>
                        <td>{{ $product->id }}</td>
                        <td><a href="{{ route('catalog-manager.products.show', $product->id) }}">{{ $product->name ?: '—' }}</a></td>
                        <td>{{ $product->reference ?: '—' }}</td>
                        <td>{{ $product->ean13 ?: '—' }}</td>
                        <td>{{ $product->manufacturer_name ?: '—' }}</td>
                        <td><span class="catalog-lsg-badge">{{ $product->status }}</span></td>
                        <td>
                            <a href="{{ route('catalog-manager.products.show', $product->id) }}" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-eye"></i></a>
                            <a href="{{ route('catalog-manager.products.edit', $product->id) }}" class="btn btn-sm btn-outline-warning"><i class="fa-solid fa-pencil"></i></a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        
    </div>
@endsection

