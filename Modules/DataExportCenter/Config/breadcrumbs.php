<?php

return [
    'data_export_center.dashboard' => [
        'label' => 'data-export-center::page_titles.data_export_center.dashboard',
        'parent' => 'settings.index',
    ],

    'data_export_center.profiles.index' => [
        'label' => 'data-export-center::page_titles.data_export_center.profiles.index',
        'parent' => 'data_export_center.dashboard',
    ],

    'data_export_center.profiles.show' => [
        'label' => 'data-export-center::page_titles.data_export_center.profiles.show',
        'parent' => 'data_export_center.profiles.index',
    ],

    'data_export_center.batches.show' => [
        'label' => 'data-export-center::page_titles.data_export_center.batches.show',
        'parent' => 'data_export_center.profiles.index',
    ],

    'data_export_center.templates.index' => [
        'label' => 'data-export-center::page_titles.data_export_center.templates.index',
        'parent' => 'data_export_center.dashboard',
    ],
];
