@extends('catalogmanager::layouts.module')

@section('catalogmanager-content')
    @php $product = $product ?? null; @endphp

    <div class="catalog-lsg-hero">
        <div>
            <span class="catalog-lsg-eyebrow">Product Core</span>
            <h1>Editar Produto</h1>
        </div>
    </div>

    <div class="catalog-lsg-card">
        <form method="POST" action="{{ route('catalog-manager.products.update', $product->id) }}">
            @csrf
            @method('PUT')

            <div class="catalog-lsg-form-grid">
    <div class="catalog-lsg-form-group">
        <label>Nome</label>
        <input type="text" name="name" value="{{ old('name', $product->name ?? '') }}" required>
    </div>

    <div class="catalog-lsg-form-group">
        <label>Referência</label>
        <input type="text" name="reference" value="{{ old('reference', $product->reference ?? '') }}">
    </div>

    <div class="catalog-lsg-form-group">
        <label>EAN13</label>
        <input type="text" name="ean13" value="{{ old('ean13', $product->ean13 ?? '') }}">
    </div>

    <div class="catalog-lsg-form-group">
        <label>Manufacturer / Marca</label>
        <select name="manufacturer_id">
            <option value="">—</option>
            @foreach($manufacturers as $manufacturer)
                <option value="{{ $manufacturer->id }}" @selected(old('manufacturer_id', $product->manufacturer_id ?? null) == $manufacturer->id)>
                    {{ $manufacturer->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="catalog-lsg-form-group">
        <label>Tipo</label>
        <input type="text" name="type" value="{{ old('type', $product->type ?? 'simple') }}">
    </div>

    <div class="catalog-lsg-form-group">
        <label>Estado</label>
        <select name="status">
            @foreach(config('catalogmanager.product_statuses', []) as $key => $label)
                <option value="{{ $key }}" @selected(old('status', $product->status ?? 'draft') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="catalog-lsg-form-group">
        <label>Weight</label>
        <input type="number" step="0.001" name="weight" value="{{ old('weight', $product->weight ?? '') }}">
    </div>

    <div class="catalog-lsg-form-group">
        <label>Housing</label>
        <input type="text" name="housing" value="{{ old('housing', $product->housing ?? '') }}">
    </div>

    <div class="catalog-lsg-form-group">
        <label>Width</label>
        <input type="number" step="0.001" name="width" value="{{ old('width', $product->width ?? '') }}">
    </div>

    <div class="catalog-lsg-form-group">
        <label>Height</label>
        <input type="number" step="0.001" name="height" value="{{ old('height', $product->height ?? '') }}">
    </div>

    <div class="catalog-lsg-form-group">
        <label>Depth</label>
        <input type="number" step="0.001" name="depth" value="{{ old('depth', $product->depth ?? '') }}">
    </div>

    <div class="catalog-lsg-form-group catalog-lsg-form-group--full">
        <label>Notas internas</label>
        <textarea name="internal_notes" rows="4">{{ old('internal_notes', $product->internal_notes ?? '') }}</textarea>
    </div>
</div>


            <div class="catalog-lsg-form-actions">
                <button class="btn btn-outline-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
                <a href="{{ route('catalog-manager.products.index') }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-angle-left"></i> Voltar
                </a>
            </div>
        </form>
    </div>
@endsection

