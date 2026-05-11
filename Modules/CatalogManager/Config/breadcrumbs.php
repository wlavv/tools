<?php

return [
    'catalog-manager.dashboard' => ['label' => 'Catalog Manager', 'parent' => null],

    'catalog-manager.products.index' => ['label' => 'Produtos', 'parent' => 'catalog-manager.dashboard'],
    'catalog-manager.products.create' => ['label' => 'Novo Produto', 'parent' => 'catalog-manager.products.index'],
    'catalog-manager.products.show' => ['label' => 'Produto', 'parent' => 'catalog-manager.products.index'],
    'catalog-manager.products.edit' => ['label' => 'Editar Produto', 'parent' => 'catalog-manager.products.index'],

    'catalog-manager.manufacturers.index' => ['label' => 'Manufacturers', 'parent' => 'catalog-manager.dashboard'],
    'catalog-manager.manufacturers.create' => ['label' => 'Novo Manufacturer', 'parent' => 'catalog-manager.manufacturers.index'],
    'catalog-manager.manufacturers.edit' => ['label' => 'Editar Manufacturer', 'parent' => 'catalog-manager.manufacturers.index'],

    'catalog-manager.suppliers.index' => ['label' => 'Fornecedores', 'parent' => 'catalog-manager.dashboard'],
    'catalog-manager.suppliers.create' => ['label' => 'Novo Fornecedor', 'parent' => 'catalog-manager.suppliers.index'],
    'catalog-manager.suppliers.edit' => ['label' => 'Editar Fornecedor', 'parent' => 'catalog-manager.suppliers.index'],

    'catalog-manager.stores.index' => ['label' => 'Lojas', 'parent' => 'catalog-manager.dashboard'],
    'catalog-manager.stores.create' => ['label' => 'Nova Loja', 'parent' => 'catalog-manager.stores.index'],
    'catalog-manager.stores.edit' => ['label' => 'Editar Loja', 'parent' => 'catalog-manager.stores.index'],

    'catalog-manager.categories.index' => ['label' => 'Categorias', 'parent' => 'catalog-manager.dashboard'],
    'catalog-manager.categories.create' => ['label' => 'Nova Categoria', 'parent' => 'catalog-manager.categories.index'],
    'catalog-manager.categories.edit' => ['label' => 'Editar Categoria', 'parent' => 'catalog-manager.categories.index'],

    'catalog-manager.sync.index' => ['label' => 'Sync PrestaShop', 'parent' => 'catalog-manager.dashboard'],
    'catalog-manager.ai.index' => ['label' => 'AI Product Pipeline', 'parent' => 'catalog-manager.dashboard'],
    'catalog-manager.issue-panels.index' => ['label' => 'Problemas Operacionais', 'parent' => 'catalog-manager.dashboard'],
    'catalog-manager.action-panels.index' => ['label' => 'Ações Pendentes', 'parent' => 'catalog-manager.dashboard'],
];
