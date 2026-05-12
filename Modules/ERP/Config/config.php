<?php

return [
    'name' => 'ERP',
    'slug' => 'erp',
    'route_prefix' => 'erp',
    'route_name' => 'erp.',
    'view_namespace' => 'erp',
    'translation_namespace' => 'erp',

    'legacy' => [
        'previous_module_name' => 'OMS',
        'previous_slug' => 'oms',
        'migration_mode' => 'non_destructive',
        'keep_legacy_tables' => true,
    ],

    'connections' => [
        'core' => env('DB_CONNECTION', 'mysql'),
        'prestashop' => env('DB2_CONNECTION', 'mysql2'),
    ],

    'ui' => [
        'style' => 'lsg',
        'icon' => 'fa-solid fa-building-columns',
        'accent' => 'primary',
        'cards_radius' => '5px',
        'layout' => [
            'mode' => 'timeline_8_4',
            'timeline' => 12,
            'main' => 8,
            'context' => 4,
        ],
    ],

    'features' => [
        'order_notes' => true,
        'billed_orders' => true,
        'receptions' => true,
        'supplier_terms' => true,
        'price_history' => true,
        'stock_history' => true,
        'eta_tracking' => true,
        'documents' => true,
        'approvals' => false,
        'ai_assistant' => false,
    ],

    'document_numbering' => [
        'order_note' => 'ERP-ON-{Y}-{00000}',
        'invoice' => 'ERP-INV-{Y}-{00000}',
        'reception' => 'ERP-REC-{Y}-{00000}',
        'credit_note' => 'ERP-CN-{Y}-{00000}',
    ],
];
