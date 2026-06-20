@php
    $routeName = Route::currentRouteName();
    $actions = config("catalogmanager.actions.$routeName", []);
    $fallbackCreateActions = [
        'catalog-manager.manufacturers.index' => ['label' => 'Nova Marca', 'route' => 'catalog-manager.manufacturers.create', 'icon' => 'fa-solid fa-plus', 'class' => 'outline-success'],
        'catalog-manager.suppliers.index' => ['label' => 'Novo Fornecedor', 'route' => 'catalog-manager.suppliers.create', 'icon' => 'fa-solid fa-plus', 'class' => 'outline-success'],
        'catalog-manager.categories.index' => ['label' => 'Nova Categoria', 'route' => 'catalog-manager.categories.create', 'icon' => 'fa-solid fa-plus', 'class' => 'outline-success'],
        'catalog-manager.characteristics.index' => ['label' => 'Nova Caracteristica', 'route' => 'catalog-manager.characteristics.create', 'icon' => 'fa-solid fa-plus', 'class' => 'outline-success'],
        'catalog-manager.combination-attributes.index' => ['label' => 'Novo Atributo', 'route' => 'catalog-manager.combination-attributes.create', 'icon' => 'fa-solid fa-plus', 'class' => 'outline-success'],
    ];

    if (empty($actions) && isset($fallbackCreateActions[$routeName])) {
        $actions = [$fallbackCreateActions[$routeName]];
    }
@endphp

@if(!empty($actions))
    <div class="catalog-lsg-actions">
        @foreach($actions as $action)
            @if(Route::has($action['route']))
                <a href="{{ route($action['route']) }}"
                   class="btn btn-{{ $action['class'] ?? 'outline-primary' }}">
                    <i class="{{ $action['icon'] ?? 'fa-solid fa-circle' }}"></i>
                    {{ $action['label'] }}
                </a>
            @endif
        @endforeach
    </div>
@endif
