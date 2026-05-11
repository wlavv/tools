@extends('catalogmanager::layouts.module')

@section('catalogmanager-content')
    @php $manufacturer = $manufacturer ?? null; @endphp
    <div class="catalog-lsg-hero">
        <div>
            <span class="catalog-lsg-eyebrow">Manufacturers / Marcas</span>
            <h1>Criar</h1>
        </div>
    </div>

    <div class="catalog-lsg-card">
        <form method="POST" action="{{ route('catalog-manager.manufacturers.store') }}">
            @csrf


            <div class="catalog-lsg-form-grid">
                <div class="catalog-lsg-form-group">
                    <label>Nome</label>
                    <input type="text" name="name" value="{{ old('name', $manufacturer->name ?? '') }}">
                </div>
<div class="catalog-lsg-form-group">
                    <label>Website</label>
                    <input type="text" name="website" value="{{ old('website', $manufacturer->website ?? '') }}">
                </div>
<div class="catalog-lsg-form-group">
                    <label>
                        <input type="checkbox" name="active" value="1" @checked(old('active', $manufacturer->active ?? true))>
                        Ativo
                    </label>
                </div>

            </div>

            <div class="catalog-lsg-form-actions">
                <button class="btn btn-outline-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
                <a href="{{ route('catalog-manager.manufacturers.index') }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-angle-left"></i> Voltar
                </a>
            </div>
        </form>
    </div>
@endsection

