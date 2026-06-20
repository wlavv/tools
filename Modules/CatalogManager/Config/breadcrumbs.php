<?php

return [
    'catalog-manager.dashboard' => ['label' => 'Catalog Manager', 'parent' => null],

    'catalog-manager.manufacturers.index' => ['label' => 'Manufacturers', 'parent' => 'catalog-manager.dashboard'],
    'catalog-manager.manufacturers.create' => ['label' => 'Novo Manufacturer', 'parent' => 'catalog-manager.manufacturers.index'],
    'catalog-manager.manufacturers.edit' => ['label' => 'Editar Manufacturer', 'parent' => 'catalog-manager.manufacturers.index'],

    'catalog-manager.suppliers.index' => ['label' => 'Fornecedores', 'parent' => 'catalog-manager.dashboard'],
    'catalog-manager.suppliers.create' => ['label' => 'Novo Fornecedor', 'parent' => 'catalog-manager.suppliers.index'],
    'catalog-manager.suppliers.edit' => ['label' => 'Editar Fornecedor', 'parent' => 'catalog-manager.suppliers.index'],

    'catalog-manager.categories.index' => ['label' => 'Categorias', 'parent' => 'catalog-manager.dashboard'],
    'catalog-manager.categories.create' => ['label' => 'Nova Categoria', 'parent' => 'catalog-manager.categories.index'],
    'catalog-manager.categories.edit' => ['label' => 'Editar Categoria', 'parent' => 'catalog-manager.categories.index'],

    'catalog-manager.characteristics.index' => ['label' => 'Caracteristicas', 'parent' => 'catalog-manager.dashboard'],
    'catalog-manager.characteristics.create' => ['label' => 'Nova Caracteristica', 'parent' => 'catalog-manager.characteristics.index'],
    'catalog-manager.characteristics.edit' => ['label' => 'Editar Caracteristica', 'parent' => 'catalog-manager.characteristics.index'],
    'catalog-manager.combination-attributes.index' => ['label' => 'Combinações', 'parent' => 'catalog-manager.dashboard'],
    'catalog-manager.combination-attributes.create' => ['label' => 'Novo Atributo', 'parent' => 'catalog-manager.combination-attributes.index'],
    'catalog-manager.combination-attributes.edit' => ['label' => 'Editar Atributo', 'parent' => 'catalog-manager.combination-attributes.index'],

    'catalog-manager.currencies.index' => ['label' => 'Currencies', 'parent' => 'catalog-manager.dashboard'],
    'catalog-manager.currencies.create' => ['label' => 'Nova Currency', 'parent' => 'catalog-manager.currencies.index'],
    'catalog-manager.currencies.edit' => ['label' => 'Editar Currency', 'parent' => 'catalog-manager.currencies.index'],

    'catalog-manager.sync.index' => ['label' => 'Sync PrestaShop', 'parent' => 'catalog-manager.dashboard'],
    'catalog-manager.issue-panels.index' => ['label' => 'Problemas Operacionais', 'parent' => 'catalog-manager.dashboard'],
    'catalog-manager.action-panels.index' => ['label' => 'Ações Pendentes', 'parent' => 'catalog-manager.dashboard'],
];
