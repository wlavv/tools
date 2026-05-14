<?php

return [
    'data_import_wizard.dashboard' => [
        'label' => 'data-import-wizard::page_titles.data_import_wizard.dashboard',
        'parent' => 'settings.index',
    ],

    'data_import_wizard.profiles.index' => [
        'label' => 'data-import-wizard::page_titles.data_import_wizard.profiles.index',
        'parent' => 'data_import_wizard.dashboard',
    ],

    'data_import_wizard.profiles.show' => [
        'label' => 'data-import-wizard::page_titles.data_import_wizard.profiles.show',
        'parent' => 'data_import_wizard.profiles.index',
    ],

    'data_import_wizard.profiles.upload' => [
        'label' => 'data-import-wizard::page_titles.data_import_wizard.profiles.upload',
        'parent' => 'data_import_wizard.profiles.show',
    ],

    'data_import_wizard.batches.index' => [
        'label' => 'data-import-wizard::page_titles.data_import_wizard.batches.index',
        'parent' => 'data_import_wizard.dashboard',
    ],

    'data_import_wizard.batches.preview' => [
        'label' => 'data-import-wizard::page_titles.data_import_wizard.batches.preview',
        'parent' => 'data_import_wizard.batches.index',
    ],

    'data_import_wizard.batches.show' => [
        'label' => 'data-import-wizard::page_titles.data_import_wizard.batches.show',
        'parent' => 'data_import_wizard.batches.index',
    ],
];
