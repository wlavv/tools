<?php

return [
    'steps' => [
        'supplier_selection' => [
            'label' => 'erp::timeline.steps.supplier_selection.label',
            'description' => 'erp::timeline.steps.supplier_selection.description',
            'icon' => 'fa-solid fa-truck-field',
            'sort_order' => 10,
            'requires_previous' => false,
        ],
        'order_note' => [
            'label' => 'erp::timeline.steps.order_note.label',
            'description' => 'erp::timeline.steps.order_note.description',
            'icon' => 'fa-solid fa-clipboard-list',
            'sort_order' => 20,
            'requires_previous' => true,
        ],
        'billing' => [
            'label' => 'erp::timeline.steps.billing.label',
            'description' => 'erp::timeline.steps.billing.description',
            'icon' => 'fa-solid fa-file-invoice',
            'sort_order' => 30,
            'requires_previous' => true,
        ],
        'reception' => [
            'label' => 'erp::timeline.steps.reception.label',
            'description' => 'erp::timeline.steps.reception.description',
            'icon' => 'fa-solid fa-boxes-stacked',
            'sort_order' => 40,
            'requires_previous' => true,
        ],
        'validation' => [
            'label' => 'erp::timeline.steps.validation.label',
            'description' => 'erp::timeline.steps.validation.description',
            'icon' => 'fa-solid fa-shield-check',
            'sort_order' => 50,
            'requires_previous' => true,
        ],
        'closed' => [
            'label' => 'erp::timeline.steps.closed.label',
            'description' => 'erp::timeline.steps.closed.description',
            'icon' => 'fa-solid fa-circle-check',
            'sort_order' => 60,
            'requires_previous' => true,
        ],
    ],
];
