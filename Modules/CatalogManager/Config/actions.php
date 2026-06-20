<?php

return [
    'catalog-manager.dashboard' => [
        ['label' => 'Nova Marca', 'route' => 'catalog-manager.manufacturers.create', 'icon' => 'fa-solid fa-copyright', 'class' => 'outline-primary'],
        ['label' => 'Novo Fornecedor', 'route' => 'catalog-manager.suppliers.create', 'icon' => 'fa-solid fa-truck-field', 'class' => 'outline-primary'],
        ['label' => 'Nova Categoria', 'route' => 'catalog-manager.categories.create', 'icon' => 'fa-solid fa-folder-plus', 'class' => 'outline-primary'],
        ['label' => 'Nova Currency', 'route' => 'catalog-manager.currencies.create', 'icon' => 'fa-solid fa-coins', 'class' => 'outline-primary'],
    ],

    'catalog-manager.manufacturers.index' => [
        ['label' => 'Nova Marca', 'route' => 'catalog-manager.manufacturers.create', 'icon' => 'fa-solid fa-plus', 'class' => 'outline-success'],
    ],
    'catalog-manager.suppliers.index' => [
        ['label' => 'Novo Fornecedor', 'route' => 'catalog-manager.suppliers.create', 'icon' => 'fa-solid fa-plus', 'class' => 'outline-success'],
    ],
    'catalog-manager.categories.index' => [
        ['label' => 'Nova Categoria', 'route' => 'catalog-manager.categories.create', 'icon' => 'fa-solid fa-plus', 'class' => 'outline-success'],
    ],
    'catalog-manager.characteristics.index' => [
        ['label' => 'Nova Caracteristica', 'route' => 'catalog-manager.characteristics.create', 'icon' => 'fa-solid fa-plus', 'class' => 'outline-success'],
    ],
    'catalog-manager.combination-attributes.index' => [
        ['label' => 'Novo Atributo', 'route' => 'catalog-manager.combination-attributes.create', 'icon' => 'fa-solid fa-plus', 'class' => 'outline-success'],
    ],
    'catalog-manager.currencies.index' => [
        ['label' => 'Nova Currency', 'route' => 'catalog-manager.currencies.create', 'icon' => 'fa-solid fa-plus', 'class' => 'outline-success'],
    ],
];
