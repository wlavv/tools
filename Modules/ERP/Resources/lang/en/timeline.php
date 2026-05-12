<?php

return [
    'steps' => [
        'supplier_selection' => [
            'label' => 'Supplier',
            'description' => 'Select supplier and review commercial conditions.',
        ],
        'order_note' => [
            'label' => 'Order Note',
            'description' => 'Create purchase intent and add products.',
        ],
        'billing' => [
            'label' => 'Billing',
            'description' => 'Convert partially or fully into billed supplier order.',
        ],
        'reception' => [
            'label' => 'Reception',
            'description' => 'Receive products fully or partially.',
        ],
        'validation' => [
            'label' => 'Validation',
            'description' => 'Resolve differences, pending items and associated documents.',
        ],
        'closed' => [
            'label' => 'Closure',
            'description' => 'Close the operational cycle and archive history.',
        ],
    ],
];
