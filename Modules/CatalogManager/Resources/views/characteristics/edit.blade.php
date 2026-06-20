@extends('catalogmanager::layouts.module')

@section('catalogmanager-content')
    <div class="catalog-lsg-hero">
        <div>
            <span class="catalog-lsg-eyebrow">Caracteristicas</span>
            <h1>Editar Caracteristica</h1>
        </div>
    </div>

    <div class="catalog-lsg-card catalog-lsg-card--full">
        <form method="POST" action="{{ route('catalog-manager.characteristics.update', $characteristic->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            @include('catalogmanager::characteristics._form')

            <div class="catalog-lsg-form-actions">
                <button class="btn btn-outline-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
                <a href="{{ route('catalog-manager.characteristics.index') }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-angle-left"></i> Voltar
                </a>
            </div>
        </form>
    </div>
@endsection
