@extends('documentmanager::layouts.module')

@section('documentmanager-content')
    @php
        $categoriesByParent = collect($categories)->groupBy(fn ($category) => (int) ($category->parent_id ?? 0));
    @endphp
    <div class="dms-card">
        <div class="dms-card__head">
            <div>
                <span class="dms-eyebrow">Arvore operacional</span>
                <h3>Pai, filho, neto</h3>
            </div>
            <span class="dms-badge dms-badge--soft">{{ collect($categories)->count() }} categorias</span>
        </div>

        <div class="dms-category-tree">
            @forelse($categoriesByParent->get(0, collect()) as $category)
                @include('documentmanager::categories.tree-item', [
                    'category' => $category,
                    'childrenByParent' => $categoriesByParent,
                    'level' => 0,
                ])
            @empty
                <div class="dms-empty">Sem categorias.</div>
            @endforelse
        </div>
    </div>
@endsection
