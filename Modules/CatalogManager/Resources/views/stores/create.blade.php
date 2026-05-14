@extends('catalogmanager::layouts.module')

@section('catalogmanager-content')
    @php $store = $store ?? null; @endphp
    <div class="catalog-lsg-hero">
        <div>
            <span class="catalog-lsg-eyebrow">Lojas</span>
            <h1>Criar</h1>
        </div>
    </div>

    <div class="catalog-lsg-card">
        <form method="POST" action="{{ route('catalog-manager.stores.store') }}">
            @csrf


            <div class="catalog-lsg-form-grid">
                <div class="catalog-lsg-form-group">
                    <label>Código</label>
                    <input type="text" name="code" value="{{ old('code', $store->code ?? '') }}">
                </div>
<div class="catalog-lsg-form-group">
                    <label>Nome</label>
                    <input type="text" name="name" value="{{ old('name', $store->name ?? '') }}">
                </div>
<div class="catalog-lsg-form-group">
                    <label>Domínio</label>
                    <input type="text" name="domain" value="{{ old('domain', $store->domain ?? '') }}">
                </div>
<div class="catalog-lsg-form-group">
                    <label>Tipo</label>
                    <select name="record_type">
                        <option value="store" @selected(old('record_type', $store->record_type ?? 'store') === 'store')>Loja</option>
                        <option value="domain" @selected(old('record_type', $store->record_type ?? 'store') === 'domain')>Dominio monitorizado</option>
                    </select>
                </div>
<div class="catalog-lsg-form-group">
                    <label>Area LSG</label>
                    <select name="site_kind">
                        <option value="store" @selected(old('site_kind', $store->site_kind ?? 'store') === 'store')>Sites lojas</option>
                        <option value="service" @selected(old('site_kind', $store->site_kind ?? 'store') === 'service')>Sites servicos</option>
                        <option value="showcase" @selected(old('site_kind', $store->site_kind ?? 'store') === 'showcase')>Sites mostra</option>
                        <option value="group" @selected(old('site_kind', $store->site_kind ?? 'store') === 'group')>Site grupo</option>
                        <option value="labs" @selected(old('site_kind', $store->site_kind ?? 'store') === 'labs')>Site labs</option>
                    </select>
                </div>
<div class="catalog-lsg-form-group">
                    <label>Locale</label>
                    <input type="text" name="locale" value="{{ old('locale', $store->locale ?? '') }}">
                </div>
<div class="catalog-lsg-form-group">
                    <label>Moeda</label>
                    <input type="text" name="currency" value="{{ old('currency', $store->currency ?? '') }}">
                </div>
<div class="catalog-lsg-form-group">
                    <label>
                        <input type="checkbox" name="active" value="1" @checked(old('active', $store->active ?? true))>
                        Ativo
                    </label>
                </div>

            </div>

            <div class="catalog-lsg-form-actions">
                <button class="btn btn-outline-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
                <a href="{{ route('catalog-manager.stores.index') }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-angle-left"></i> Voltar
                </a>
            </div>
        </form>
    </div>
@endsection

