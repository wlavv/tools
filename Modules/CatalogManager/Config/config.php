<?php

return [
    'name' => 'Catalog Manager',
    'slug' => 'catalog-manager',
    'version' => '5.6.2',
    'route_prefix' => 'catalog-manager',
    'prestashop_connection' => 'mysql2',
    'default_locale' => 'pt',
    'default_currency' => 'EUR',

    'product_statuses' => [
        'draft' => 'Draft',
        'analysis' => 'Analysis',
        'ready' => 'Ready',
        'published' => 'Published',
        'archived' => 'Archived',
    ],

    'store_product_statuses' => [
        'draft' => 'Draft',
        'ready' => 'Ready',
        'published' => 'Published',
        'hidden' => 'Hidden',
        'archived' => 'Archived',
    ],
];
