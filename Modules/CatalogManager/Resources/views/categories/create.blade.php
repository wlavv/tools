@extends('catalogmanager::layouts.module')

@section('catalogmanager-content')
    @php $category = $category ?? null; @endphp

    <div class="catalog-lsg-hero">
        <div>
            <span class="catalog-lsg-eyebrow">Categorias por Loja</span>
            <h1>Nova Categoria</h1>
        </div>
    </div>

    <div class="catalog-lsg-card">
        <form method="POST" action="{{ route('catalog-manager.categories.store') }}">
            @csrf


            <div class="catalog-lsg-form-grid">
    <div class="catalog-lsg-form-group">
        <label>Loja</label>
        <select name="store_id" required>
            <option value="">—</option>
            @foreach($stores as $store)
                <option value="{{ $store->id }}" @selected(old('store_id', $category->store_id ?? null) == $store->id)>
                    {{ $store->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="catalog-lsg-form-group">
        <label>Categoria Pai</label>
        <select name="parent_id">
            <option value="">Sem parent</option>
            @foreach($parents as $parent)
                <option value="{{ $parent->id }}" @selected(old('parent_id', $category->parent_id ?? null) == $parent->id)>
                    {{ $parent->store_name }} / {{ $parent->name ?: 'Categoria #' . $parent->id }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="catalog-lsg-form-group">
        <label>Nome</label>
        <input type="text" name="name" value="{{ old('name', $category->name ?? '') }}" required>
    </div>

    <div class="catalog-lsg-form-group">
        <label>Code</label>
        <input type="text" name="code" value="{{ old('code', $category->code ?? '') }}">
    </div>

    <div class="catalog-lsg-form-group">
        <label>Locale</label>
        <input type="text" name="locale" value="{{ old('locale', $category->locale ?? 'pt') }}">
    </div>

    <div class="catalog-lsg-form-group">
        <label>Link rewrite</label>
        <input type="text" name="link_rewrite" value="{{ old('link_rewrite', $category->link_rewrite ?? '') }}">
    </div>

    <div class="catalog-lsg-form-group">
        <label>Meta title</label>
        <input type="text" name="meta_title" value="{{ old('meta_title', $category->meta_title ?? '') }}">
    </div>

    <div class="catalog-lsg-form-group">
        <label>Posição</label>
        <input type="number" name="position" value="{{ old('position', $category->position ?? 0) }}">
    </div>

    <div class="catalog-lsg-form-group">
        <label>
            <input type="checkbox" name="active" value="1" @checked(old('active', $category->active ?? true))>
            Ativa
        </label>
    </div>

    <div class="catalog-lsg-form-group catalog-lsg-form-group--full">
        <label>Descrição</label>
        <textarea name="description" rows="4">{{ old('description', $category->description ?? '') }}</textarea>
    </div>

    <div class="catalog-lsg-form-group catalog-lsg-form-group--full">
        <label>Meta description</label>
        <textarea name="meta_description" rows="3">{{ old('meta_description', $category->meta_description ?? '') }}</textarea>
    </div>
</div>


            <div class="catalog-lsg-form-actions">
                <button class="btn btn-outline-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
                <a href="{{ route('catalog-manager.categories.index') }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-angle-left"></i> Voltar
                </a>
            </div>
        </form>
    </div>
@endsection

