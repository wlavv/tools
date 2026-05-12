<?php

return [
    'erp.dashboard' => [
        'label' => 'erp::page_titles.dashboard',
        'parent' => null,
    ],

    'erp.timeline' => [
        'label' => 'erp::page_titles.timeline',
        'parent' => 'erp.dashboard',
    ],

    'erp.settings.index' => [
        'label' => 'erp::page_titles.settings.index',
        'parent' => 'erp.dashboard',
    ],

    'erp.settings.document-types.index' => [
        'label' => 'erp::page_titles.settings.document_types',
        'parent' => 'erp.settings.index',
    ],

    'erp.settings.statuses.index' => [
        'label' => 'erp::page_titles.settings.statuses',
        'parent' => 'erp.settings.index',
    ],

    'erp.settings.workflows.index' => [
        'label' => 'erp::page_titles.settings.workflows',
        'parent' => 'erp.settings.index',
    ],

    'erp.settings.supplier-terms.index' => [
        'label' => 'erp::page_titles.settings.supplier_terms',
        'parent' => 'erp.settings.index',
    ],
];
