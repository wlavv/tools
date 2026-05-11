<?php

return [
    'enabled' => true,
    'limit_per_panel' => 8,
    'panels' => [
        'missing_ean' => [
            'enabled' => true, 'title' => 'Produtos sem EAN', 'description' => 'Produtos sem código EAN13 definido.',
            'icon' => 'fa-solid fa-barcode', 'tone' => 'warning',
            'provider' => Modules\CatalogManager\Services\IssuePanels\Panels\MissingEanPanel::class, 'order' => 10,
        ],
        'missing_housing' => [
            'enabled' => true, 'title' => 'Produtos sem Housing', 'description' => 'Produtos sem localização logística/housing.',
            'icon' => 'fa-solid fa-location-dot', 'tone' => 'danger',
            'provider' => Modules\CatalogManager\Services\IssuePanels\Panels\MissingHousingPanel::class, 'order' => 20,
        ],
        'missing_supplier' => [
            'enabled' => true, 'title' => 'Produtos sem Fornecedor', 'description' => 'Produtos sem fornecedor associado.',
            'icon' => 'fa-solid fa-truck-field', 'tone' => 'warning',
            'provider' => Modules\CatalogManager\Services\IssuePanels\Panels\MissingSupplierPanel::class, 'order' => 30,
        ],
        'missing_manufacturer' => [
            'enabled' => true, 'title' => 'Produtos sem Marca', 'description' => 'Produtos sem manufacturer/marca definida.',
            'icon' => 'fa-solid fa-copyright', 'tone' => 'warning',
            'provider' => Modules\CatalogManager\Services\IssuePanels\Panels\MissingManufacturerPanel::class, 'order' => 40,
        ],
        'missing_store_category' => [
            'enabled' => true, 'title' => 'Sem Categoria na Loja', 'description' => 'Perfis de produto por loja sem categoria associada.',
            'icon' => 'fa-solid fa-folder-tree', 'tone' => 'info',
            'provider' => Modules\CatalogManager\Services\IssuePanels\Panels\MissingStoreCategoryPanel::class, 'order' => 50,
        ],
        'missing_store_content' => [
            'enabled' => true, 'title' => 'Sem Conteúdo por Loja', 'description' => 'Perfis de produto por loja sem nome/descrição.',
            'icon' => 'fa-solid fa-file-lines', 'tone' => 'info',
            'provider' => Modules\CatalogManager\Services\IssuePanels\Panels\MissingStoreContentPanel::class, 'order' => 60,
        ],
        'missing_price' => [
            'enabled' => true, 'title' => 'Sem Preço', 'description' => 'Produtos/perfis de loja sem preço definido.',
            'icon' => 'fa-solid fa-euro-sign', 'tone' => 'danger',
            'provider' => Modules\CatalogManager\Services\IssuePanels\Panels\MissingPricePanel::class, 'order' => 70,
        ],
    ],
];
