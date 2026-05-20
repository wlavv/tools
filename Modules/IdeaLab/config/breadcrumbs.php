<?php

return [
    'idealab.index' => [
        'label' => 'idealab::page_titles.idealab.index',
        'parent' => 'administration.index',
        'translate' => true,
    ],

    'idealab.create' => [
        'label' => 'idealab::page_titles.idealab.create',
        'parent' => 'idealab.index',
        'translate' => true,
    ],

    'idealab.workflow.index' => [
        'label' => 'idealab::page_titles.idealab.workflow.index',
        'parent' => 'idealab.index',
        'translate' => true,
    ],

    'idealab.show' => [
        'label' => 'idealab::page_titles.idealab.show',
        'parent' => 'idealab.index',
        'translate' => true,
    ],

    'idealab.edit' => [
        'label' => 'idealab::page_titles.idealab.edit',
        'parent' => 'idealab.show',
        'translate' => true,
    ],

    'idealab.update' => [
        'label' => 'idealab::page_titles.idealab.edit',
        'parent' => 'idealab.show',
        'translate' => true,
    ],

    'idealab.ai.run' => [
        'label' => 'AI Consensus',
        'parent' => 'idealab.show',
    ],

    'idealab.convert' => [
        'label' => 'Convert to Project',
        'parent' => 'idealab.show',
    ],

    'idealab.templates.index' => [
        'label' => 'idealab::page_titles.idealab.templates.index',
        'parent' => 'idealab.index',
        'translate' => true,
    ],

    'idealab.templates.create' => [
        'label' => 'idealab::page_titles.idealab.templates.create',
        'parent' => 'idealab.templates.index',
        'translate' => true,
    ],

    'idealab.templates.edit' => [
        'label' => 'idealab::page_titles.idealab.templates.edit',
        'parent' => 'idealab.templates.index',
        'translate' => true,
    ],
];
