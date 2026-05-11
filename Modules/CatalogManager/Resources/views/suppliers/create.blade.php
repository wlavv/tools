@extends('catalogmanager::layouts.module')

@section('catalogmanager-content')
    @php $supplier = $supplier ?? null; @endphp
    <div class="catalog-lsg-hero">
        <div>
            <span class="catalog-lsg-eyebrow">Fornecedores</span>
            <h1>Criar</h1>
        </div>
    </div>

    <div class="catalog-lsg-card">
        <form method="POST" action="{{ route('catalog-manager.suppliers.store') }}">
            @csrf


            <div class="catalog-lsg-form-grid">
                <div class="catalog-lsg-form-group">
                    <label>Nome</label>
                    <input type="text" name="name" value="{{ old('name', $supplier->name ?? '') }}">
                </div>
<div class="catalog-lsg-form-group">
                    <label>Código</label>
                    <input type="text" name="code" value="{{ old('code', $supplier->code ?? '') }}">
                </div>
<div class="catalog-lsg-form-group">
                    <label>Email</label>
                    <input type="text" name="email" value="{{ old('email', $supplier->email ?? '') }}">
                </div>
<div class="catalog-lsg-form-group">
                    <label>Telefone</label>
                    <input type="text" name="phone" value="{{ old('phone', $supplier->phone ?? '') }}">
                </div>
<div class="catalog-lsg-form-group">
                    <label>Moeda</label>
                    <input type="text" name="currency" value="{{ old('currency', $supplier->currency ?? '') }}">
                </div>
<div class="catalog-lsg-form-group">
                    <label>Lead time dias</label>
                    <input type="number" name="lead_time_days" value="{{ old('lead_time_days', $supplier->lead_time_days ?? '') }}">
                </div>
<div class="catalog-lsg-form-group">
                    <label>
                        <input type="checkbox" name="active" value="1" @checked(old('active', $supplier->active ?? true))>
                        Ativo
                    </label>
                </div>

            </div>

            <div class="catalog-lsg-form-actions">
                <button class="btn btn-outline-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
                <a href="{{ route('catalog-manager.suppliers.index') }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-angle-left"></i> Voltar
                </a>
            </div>
        </form>
    </div>
@endsection

