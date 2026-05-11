<?php

return [
    'catalog-manager.dashboard' => [
        ['label' => 'Novo Produto', 'route' => 'catalog-manager.products.create', 'icon' => 'fa-solid fa-plus', 'class' => 'outline-success'],
        ['label' => 'Nova Marca', 'route' => 'catalog-manager.manufacturers.create', 'icon' => 'fa-solid fa-copyright', 'class' => 'outline-primary'],
        ['label' => 'Novo Fornecedor', 'route' => 'catalog-manager.suppliers.create', 'icon' => 'fa-solid fa-truck-field', 'class' => 'outline-primary'],
        ['label' => 'Nova Loja', 'route' => 'catalog-manager.stores.create', 'icon' => 'fa-solid fa-store', 'class' => 'outline-primary'],
        ['label' => 'Nova Categoria', 'route' => 'catalog-manager.categories.create', 'icon' => 'fa-solid fa-folder-plus', 'class' => 'outline-primary'],
    ],

    'catalog-manager.products.index' => [
        ['label' => 'Novo Produto', 'route' => 'catalog-manager.products.create', 'icon' => 'fa-solid fa-plus', 'class' => 'outline-success'],
    ],
    'catalog-manager.manufacturers.index' => [
        ['label' => 'Nova Marca', 'route' => 'catalog-manager.manufacturers.create', 'icon' => 'fa-solid fa-plus', 'class' => 'outline-success'],
    ],
    'catalog-manager.suppliers.index' => [
        ['label' => 'Novo Fornecedor', 'route' => 'catalog-manager.suppliers.create', 'icon' => 'fa-solid fa-plus', 'class' => 'outline-success'],
    ],
    'catalog-manager.stores.index' => [
        ['label' => 'Nova Loja', 'route' => 'catalog-manager.stores.create', 'icon' => 'fa-solid fa-plus', 'class' => 'outline-success'],
    ],
    'catalog-manager.categories.index' => [
        ['label' => 'Nova Categoria', 'route' => 'catalog-manager.categories.create', 'icon' => 'fa-solid fa-plus', 'class' => 'outline-success'],
    ],
];
