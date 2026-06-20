@php
    $attribute = $attribute ?? null;
    $valuesText = old('values', $valuesText ?? '');
@endphp

<div class="catalog-lsg-form-grid">
    <div class="catalog-lsg-form-group">
        <label>Nome</label>
        <input type="text" name="name" value="{{ old('name', $attribute->name ?? '') }}" required>
    </div>

    <div class="catalog-lsg-form-group">
        <label>Slug</label>
        <input type="text" name="slug" value="{{ old('slug', $attribute->slug ?? '') }}" placeholder="condition">
    </div>

    <div class="catalog-lsg-form-group">
        <label>Tipo de apresentação</label>
        <select name="display_type">
            @foreach(['select' => 'Select', 'swatch' => 'Swatch', 'text' => 'Texto'] as $value => $label)
                <option value="{{ $value }}" @selected(old('display_type', $attribute->display_type ?? 'select') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="catalog-lsg-form-group">
        <label>Posição</label>
        <input type="number" name="position" value="{{ old('position', $attribute->position ?? 0) }}">
    </div>

    <div class="catalog-lsg-form-group catalog-lsg-form-group--full">
        <label>Valores da combinação</label>
        <textarea name="values" rows="8" placeholder="Um valor por linha">{{ $valuesText }}</textarea>
        <small>Estes valores ficam disponíveis nas variações do produto quando a categoria usar este atributo.</small>
    </div>

    <div class="catalog-lsg-form-group">
        <label><input type="checkbox" name="is_required" value="1" @checked(old('is_required', $attribute->is_required ?? false))> Obrigatório por defeito</label>
    </div>

    <div class="catalog-lsg-form-group">
        <label><input type="checkbox" name="affects_price" value="1" @checked(old('affects_price', $attribute->affects_price ?? true))> Afeta preço</label>
    </div>

    <div class="catalog-lsg-form-group">
        <label><input type="checkbox" name="affects_stock" value="1" @checked(old('affects_stock', $attribute->affects_stock ?? true))> Afeta stock</label>
    </div>

    <div class="catalog-lsg-form-group">
        <label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $attribute->is_active ?? true))> Ativo</label>
    </div>
</div>
