@extends('catalogmanager::layouts.module')

@section('catalogmanager-content')
    <div class="catalog-lsg-hero">
        <div>
            <span class="catalog-lsg-eyebrow">Combinações</span>
            <h1>Editar Atributo de Combinação</h1>
        </div>
    </div>

    <div class="catalog-lsg-card">
        <form method="POST" action="{{ route('catalog-manager.combination-attributes.update', $attribute->id) }}">
            @csrf
            @method('PUT')
            @include('catalogmanager::combination_attributes._form', ['attribute' => $attribute])

            <div class="catalog-lsg-form-actions">
                <button class="btn btn-outline-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
                <a href="{{ route('catalog-manager.combination-attributes.index') }}" class="btn btn-outline-secondary"><i class="fa-solid fa-angle-left"></i> Voltar</a>
            </div>
        </form>
    </div>
@endsection
