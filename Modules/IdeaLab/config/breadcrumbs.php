<?php

return [
    'idealab.index' => [
        'label' => 'idealab::page_titles.idealab.index',
        'parent' => 'administration.index',
    ],

    'idealab.create' => [
        'label' => 'idealab::page_titles.idealab.create',
        'parent' => 'idealab.index',
    ],

    'idealab.show' => [
        'label' => 'idealab::page_titles.idealab.show',
        'parent' => 'idealab.index',
    ],

    'idealab.edit' => [
        'label' => 'idealab::page_titles.idealab.edit',
        'parent' => 'idealab.show',
    ],

    'idealab.templates.index' => [
        'label' => 'idealab::page_titles.idealab.templates.index',
        'parent' => 'idealab.index',
    ],

    'idealab.templates.create' => [
        'label' => 'idealab::page_titles.idealab.templates.create',
        'parent' => 'idealab.templates.index',
    ],

    'idealab.templates.edit' => [
        'label' => 'idealab::page_titles.idealab.templates.edit',
        'parent' => 'idealab.templates.index',
    ],
];
