@extends('catalogmanager::layouts.module')

@section('catalogmanager-content')
    @php
        $categoriesByStore = collect($categories ?? [])->groupBy('store_name');
        $renderCategoryTree = function ($items, $parentId = null, $level = 0) use (&$renderCategoryTree) {
            $children = $items
                ->filter(fn ($category) => (string) ($category->parent_id ?? '') === (string) ($parentId ?? ''))
                ->sortBy([
                    ['position', 'asc'],
                    ['name', 'asc'],
                ]);

            if ($children->isEmpty()) {
                return '';
            }

            $html = '<ul class="catalog-category-tree__list">';

            foreach ($children as $category) {
                $name = e($category->name ?: 'Categoria #' . $category->id);
                $code = e($category->code ?: $category->link_rewrite ?: '');
                $activeClass = $category->active ? 'catalog-category-tree__status--active' : 'catalog-category-tree__status--inactive';
                $activeLabel = $category->active ? 'Ativa' : 'Inativa';
                $editUrl = route('catalog-manager.categories.edit', $category->id);
                $childHtml = $renderCategoryTree($items, $category->id, $level + 1);
                $hasChildren = $childHtml !== '';

                $html .= '<li class="catalog-category-tree__item ' . ($hasChildren ? 'has-children is-collapsed' : '') . '" style="--category-level:' . (int) $level . '">';
                $html .= '<div class="catalog-category-tree__node" ' . ($hasChildren ? 'role="button" tabindex="0" aria-expanded="false"' : '') . '>';
                $html .= '<span class="catalog-category-tree__branch"><i class="fa-solid ' . ($hasChildren ? 'fa-chevron-right' : 'fa-folder') . '"></i></span>';
                $html .= '<div class="catalog-category-tree__content">';
                $html .= '<strong>' . $name . '</strong>';
                $html .= '<span>ID #' . (int) $category->id . ($code ? ' · ' . $code : '') . ' · Pos. ' . (int) $category->position . '</span>';
                $html .= '</div>';
                $html .= '<span class="catalog-category-tree__status ' . $activeClass . '">' . $activeLabel . '</span>';
                $html .= '<a href="' . e($editUrl) . '" class="btn btn-sm btn-outline-warning" title="Editar categoria"><i class="fa-solid fa-pencil"></i></a>';
                $html .= '</div>';
                $html .= $childHtml;
                $html .= '</li>';
            }

            $html .= '</ul>';

            return $html;
        };
    @endphp

    <div class="catalog-lsg-hero">
        <div>
            <span class="catalog-lsg-eyebrow">Store categories</span>
            <h1>Categorias por Loja</h1>
            <p>Cada loja tem a sua propria arvore de categorias, com dependencias pai/filho visiveis.</p>
        </div>
    </div>

    <div class="catalog-category-tree">
        @forelse($categoriesByStore as $storeName => $storeCategories)
            <section class="catalog-lsg-card catalog-category-tree__store">
                <div class="catalog-category-tree__store-head">
                    <div>
                        <span class="catalog-lsg-eyebrow">Loja</span>
                        <h2>{{ $storeName ?: 'Loja sem nome' }}</h2>
                    </div>
                    <strong>{{ $storeCategories->count() }} categorias</strong>
                </div>

                {!! $renderCategoryTree($storeCategories) !!}
            </section>
        @empty
            <section class="catalog-lsg-card">
                <div class="catalog-empty-state">
                    <i class="fa-solid fa-folder-tree"></i>
                    <strong>Sem categorias</strong>
                    <span>Cria a primeira categoria para começar a estruturar a loja.</span>
                </div>
            </section>
        @endforelse
    </div>
@endsection
