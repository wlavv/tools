<?php

return [
    'exportables' => [
        \Modules\Catalog\Models\Product::class,
        \Modules\Orders\Models\Order::class,
    ],

    'dynamic_builder' => [
        'allowed_tables' => [
            'products',
            'suppliers',
            'currencies',
            'orders',
            'order_lines',
        ],
    ],
];
