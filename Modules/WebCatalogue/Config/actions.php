<?php

return [
    'module_home_routes' => [
        'webcatalogue' => 'webcatalogue.index',
    ],
    'routes' => [
        'webcatalogue.index' => [
            'new' => 'webcatalogue.stores.create',
        ],
        'webcatalogue.stores.index' => [
            'back' => 'webcatalogue.index',
            'new' => 'webcatalogue.stores.create',
        ],
        'webcatalogue.stores.create' => [
            'back' => 'webcatalogue.stores.index',
            'save' => true,
        ],
        'webcatalogue.stores.show' => [
            'back' => 'webcatalogue.stores.index',
            'edit' => 'webcatalogue.stores.edit',
            'new' => 'webcatalogue.stores.create',
            'delete' => true,
        ],
        'webcatalogue.stores.edit' => [
            'back' => 'webcatalogue.stores.index',
            'save' => true,
            'delete' => true,
        ],
        'webcatalogue.catalogues.index' => [
            'back' => 'webcatalogue.index',
            'new' => 'webcatalogue.catalogues.create',
        ],
        'webcatalogue.catalogues.create' => [
            'back' => 'webcatalogue.catalogues.index',
            'save' => true,
        ],
        'webcatalogue.catalogues.show' => [
            'back' => 'webcatalogue.catalogues.index',
            'edit' => 'webcatalogue.catalogues.edit',
            'delete' => true,
        ],
        'webcatalogue.catalogues.edit' => [
            'back' => 'webcatalogue.catalogues.index',
            'save' => true,
            'delete' => true,
        ],
        'webcatalogue.products.index' => [
            'back' => 'webcatalogue.index',
            'new' => 'webcatalogue.products.create',
            'import' => 'webcatalogue.imports.index',
        ],
        'webcatalogue.products.create' => [
            'back' => 'webcatalogue.products.index',
            'save' => true,
        ],
        'webcatalogue.products.show' => [
            'back' => 'webcatalogue.products.index',
            'edit' => 'webcatalogue.products.edit',
            'delete' => true,
        ],
        'webcatalogue.products.edit' => [
            'back' => 'webcatalogue.products.index',
            'save' => true,
            'delete' => true,
        ],
        'webcatalogue.resources.index' => [
            'back' => 'webcatalogue.index',
            'new' => 'webcatalogue.resources.create',
            'import' => 'webcatalogue.imports.index',
        ],
        'webcatalogue.resources.create' => [
            'back' => 'webcatalogue.resources.index',
            'save' => true,
        ],

        'webcatalogue.studio.3d_jobs.index' => [
            'back' => 'webcatalogue.index',
            'new' => 'webcatalogue.studio.3d_jobs.create',
        ],
        'webcatalogue.studio.3d_jobs.create' => [
            'back' => 'webcatalogue.studio.3d_jobs.index',
            'save' => true,
        ],
        'webcatalogue.studio.3d_jobs.show' => [
            'back' => 'webcatalogue.studio.3d_jobs.index',
            'edit' => 'webcatalogue.studio.3d_jobs.edit',
            'delete' => true,
        ],
        'webcatalogue.studio.3d_jobs.edit' => [
            'back' => 'webcatalogue.studio.3d_jobs.index',
            'save' => true,
            'delete' => true,
        ],
        'webcatalogue.imports.index' => [
            'back' => 'webcatalogue.index',
        ],
        'webcatalogue.imports.show' => [
            'back' => 'webcatalogue.imports.index',
        ],
        'webcatalogue.imports.preview' => [
            'back' => 'webcatalogue.imports.index',
            'save' => true,
        ],
        'webcatalogue.imports.upload' => [
            'back' => 'webcatalogue.imports.index',
        ],
        'webcatalogue.pricing.index' => [
            'back' => 'webcatalogue.index',
            'new' => 'webcatalogue.pricing.create',
            'import' => 'webcatalogue.imports.index',
        ],
        'webcatalogue.pricing.create' => [
            'back' => 'webcatalogue.pricing.index',
            'save' => true,
        ],
        'webcatalogue.pricing.show' => [
            'back' => 'webcatalogue.pricing.index',
            'edit' => 'webcatalogue.pricing.edit',
            'delete' => true,
        ],
        'webcatalogue.pricing.edit' => [
            'back' => 'webcatalogue.pricing.index',
            'save' => true,
            'delete' => true,
        ],
        'webcatalogue.promotions.index' => [
            'back' => 'webcatalogue.index',
            'new' => 'webcatalogue.promotions.create',
            'import' => 'webcatalogue.imports.index',
        ],
        'webcatalogue.themes.index' => [
            'back' => 'webcatalogue.index',
            'new' => 'webcatalogue.themes.create',
            'import' => 'webcatalogue.imports.index',
        ],
        'webcatalogue.environments.index' => [
            'back' => 'webcatalogue.index',
            'new' => 'webcatalogue.environments.create',
            'import' => 'webcatalogue.imports.index',
        ],

        'webcatalogue.recognition.index' => [
            'back' => 'webcatalogue.index',
        ],
        'webcatalogue.recognition.pipeline.index' => [
            'back' => 'webcatalogue.recognition.index',
            'summary' => [
                'label' => 'JSON',
                'name' => 'JSON',
                'icon' => 'fa-solid fa-code',
                'class' => 'lsg-action-btn lsg-action-btn--primary',
                'route' => 'webcatalogue.recognition.pipeline.summary',
                'type' => 'link',
            ],
            'export_csv' => [
                'label' => 'Export CSV',
                'name' => 'Export CSV',
                'icon' => 'fa-solid fa-file-csv',
                'class' => 'lsg-action-btn lsg-action-btn--neutral',
                'route' => 'webcatalogue.recognition.pipeline.export_csv',
                'type' => 'link',
            ],
        ],
        'webcatalogue.recognition.pipeline.summary' => [
            'back' => 'webcatalogue.recognition.pipeline.index',
        ],
        'webcatalogue.recognition.pipeline.export_csv' => [
            'back' => 'webcatalogue.recognition.pipeline.index',
        ],
        'webcatalogue.recognition.sessions.index' => [
            'back' => 'webcatalogue.recognition.index',
        ],
        'webcatalogue.recognition.sessions.show' => [
            'back' => 'webcatalogue.recognition.sessions.index',
        ],
        'webcatalogue.recognition.leads.index' => [
            'back' => 'webcatalogue.recognition.index',
        ],
        'webcatalogue.recognition.leads.show' => [
            'back' => 'webcatalogue.recognition.leads.index',
        ],
    ],
];
