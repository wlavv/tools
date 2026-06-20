@extends('catalogmanager::layouts.module')

@section('catalogmanager-content')
    <div class="catalog-lsg-hero">
        <div>
            <span class="catalog-lsg-eyebrow">Categorias por Loja</span>
            <h1>Nova Categoria</h1>
        </div>
    </div>

    <form method="POST" action="{{ route('catalog-manager.categories.store') }}" class="catalog-category-form">
        @csrf

        @include('catalogmanager::categories._form')

        <div class="catalog-lsg-card catalog-category-actions-panel">
            <div class="catalog-lsg-form-actions">
                <button class="btn btn-outline-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
                <a href="{{ route('catalog-manager.categories.index') }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-angle-left"></i> Voltar
                </a>
            </div>
        </div>
    </form>
@endsection
