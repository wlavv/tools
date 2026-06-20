<?php

return [
    'name' => 'Product Core',
    'slug' => 'product-core',
    'route_prefix' => env('PRODUCT_CORE_ROUTE_PREFIX', 'product-growth/product-core'),
    'route_name' => 'product_growth.product_core.',
    'layout' => 'product-core::layouts.module',
    'middleware' => ['web', 'auth'],
    'pagination' => 25,
    'currency' => env('PRODUCT_CORE_DEFAULT_CURRENCY', 'EUR'),
    'finance' => [
        'default_vat_rule' => 'pt_vat_23',
        'vat_rules' => [
            'pt_vat_23' => [
                'label' => 'Portugal VAT 23%',
                'rate' => 0.23,
            ],
        ],
        'currencies' => [
            'EUR' => ['label' => 'EUR - Euro', 'rate_to_eur' => 1],
            'USD' => ['label' => 'USD - US Dollar', 'rate_to_eur' => null],
            'GBP' => ['label' => 'GBP - Pound Sterling', 'rate_to_eur' => null],
            'CHF' => ['label' => 'CHF - Swiss Franc', 'rate_to_eur' => null],
            'JPY' => ['label' => 'JPY - Japanese Yen', 'rate_to_eur' => null],
        ],
    ],
    'tables_prefix' => 'lsg_catalog_',
    'sync' => [
        'default_status' => 'not_synced',
        'bridge_module' => 'PrestaShopBridge',
        'bridge_route' => 'product_growth.prestashop_bridge.sync_jobs.store',
    ],
    'states' => [
        'draft' => 'Rascunho',
        'in_review' => 'Em revisao',
        'approved' => 'Aprovado',
        'ready_to_sync' => 'Pronto para sincronizar',
        'synced' => 'Sincronizado',
        'needs_resync' => 'Requer ressincronizacao',
        'archived' => 'Arquivado',
        'blocked' => 'Bloqueado',
    ],
];
