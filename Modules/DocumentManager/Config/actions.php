<?php

$routes = [
    'document-manager.dashboard' => [
        'new_document' => [
            'label' => 'Novo Documento',
            'route' => 'document-manager.documents.create',
            'icon' => 'fa-solid fa-plus',
            'class' => 'lsg-action-btn lsg-action-btn--success',
        ],
        'explorer' => [
            'label' => 'Explorer',
            'route' => 'document-manager.documents.index',
            'icon' => 'fa-solid fa-folder-tree',
        ],
        'workspaces' => [
            'label' => 'Workspaces',
            'route' => 'document-manager.workspaces.index',
            'icon' => 'fa-solid fa-layer-group',
        ],
        'diagnostics' => [
            'label' => 'Diagnostics',
            'route' => 'document-manager.diagnostics.index',
            'icon' => 'fa-solid fa-stethoscope',
        ],
    ],

    'document-manager.documents.index' => [
        'dashboard' => [
            'label' => 'Dashboard',
            'route' => 'document-manager.dashboard',
            'icon' => 'fa-solid fa-chart-line',
        ],
        'new' => [
            'label' => 'Novo Documento',
            'route' => 'document-manager.documents.create',
            'icon' => 'fa-solid fa-plus',
        ],
        'categories' => [
            'label' => 'Categorias',
            'route' => 'document-manager.categories.index',
            'icon' => 'fa-solid fa-folder-tree',
        ],
        'tags' => [
            'label' => 'Tags',
            'route' => 'document-manager.tags.index',
            'icon' => 'fa-solid fa-tags',
        ],
    ],

    'document-manager.documents.create' => [
        'back' => [
            'label' => 'Voltar',
            'route' => 'document-manager.documents.index',
            'icon' => 'fa-solid fa-angle-left',
        ],
        'save' => [
            'label' => 'Guardar',
            'icon' => 'fa-solid fa-floppy-disk',
        ],
    ],

    'document-manager.documents.show' => [
        'back' => [
            'label' => 'Voltar',
            'route' => 'document-manager.documents.index',
            'icon' => 'fa-solid fa-angle-left',
        ],
        'edit' => [
            'label' => 'Editar',
            'route' => 'document-manager.documents.edit',
            'icon' => 'fa-solid fa-pencil',
        ],
        'delete' => false,
        'preview' => [
            'label' => 'Preview',
            'route' => 'document-manager.documents.preview',
            'icon' => 'fa-solid fa-eye',
        ],
        'download' => [
            'label' => 'Download',
            'route' => 'document-manager.documents.download',
            'icon' => 'fa-solid fa-download',
        ],
    ],

    'document-manager.documents.edit' => [
        'back' => [
            'label' => 'Voltar',
            'route' => 'document-manager.documents.show',
            'icon' => 'fa-solid fa-angle-left',
        ],
        'new' => false,
        'show' => false,
        'save' => [
            'label' => 'Guardar',
            'icon' => 'fa-solid fa-floppy-disk',
        ],
        'delete' => false,
    ],

    'document-manager.documents.preview' => [
        'document' => [
            'label' => 'Documento',
            'route' => 'document-manager.documents.show',
            'icon' => 'fa-solid fa-angle-left',
            'class' => 'lsg-action-btn lsg-action-btn--back',
        ],
        'edit_document' => [
            'label' => 'Editar',
            'route' => 'document-manager.documents.edit',
            'icon' => 'fa-solid fa-pencil',
            'class' => 'lsg-action-btn lsg-action-btn--warning',
        ],
        'download' => [
            'label' => 'Download',
            'route' => 'document-manager.documents.download',
            'icon' => 'fa-solid fa-download',
        ],
    ],

    'document-manager.workspaces.index' => [
        'dashboard' => [
            'label' => 'Dashboard',
            'route' => 'document-manager.dashboard',
            'icon' => 'fa-solid fa-chart-line',
        ],
        'new' => [
            'label' => 'Novo Workspace',
            'route' => 'document-manager.workspaces.create',
            'icon' => 'fa-solid fa-plus',
        ],
        'folders' => [
            'label' => 'Pastas',
            'route' => 'document-manager.folders.index',
            'icon' => 'fa-solid fa-folder-open',
        ],
    ],

    'document-manager.workspaces.create' => [
        'back' => [
            'label' => 'Voltar',
            'route' => 'document-manager.workspaces.index',
            'icon' => 'fa-solid fa-angle-left',
        ],
        'save' => [
            'label' => 'Guardar',
            'icon' => 'fa-solid fa-floppy-disk',
        ],
    ],

    'document-manager.workspaces.edit' => [
        'back' => [
            'label' => 'Voltar',
            'route' => 'document-manager.workspaces.index',
            'icon' => 'fa-solid fa-angle-left',
        ],
        'new' => false,
        'show' => false,
        'save' => [
            'label' => 'Guardar',
            'icon' => 'fa-solid fa-floppy-disk',
        ],
        'delete' => false,
    ],

    'document-manager.folders.index' => [
        'dashboard' => [
            'label' => 'Dashboard',
            'route' => 'document-manager.dashboard',
            'icon' => 'fa-solid fa-chart-line',
        ],
        'new' => [
            'label' => 'Nova Pasta',
            'route' => 'document-manager.folders.create',
            'icon' => 'fa-solid fa-plus',
        ],
        'new_document' => [
            'label' => 'Novo Documento',
            'route' => 'document-manager.documents.create',
            'icon' => 'fa-solid fa-file-circle-plus',
            'class' => 'lsg-action-btn lsg-action-btn--success',
        ],
    ],

    'document-manager.folders.create' => [
        'back' => [
            'label' => 'Voltar',
            'route' => 'document-manager.folders.index',
            'icon' => 'fa-solid fa-angle-left',
        ],
        'save' => [
            'label' => 'Guardar',
            'icon' => 'fa-solid fa-floppy-disk',
        ],
    ],

    'document-manager.folders.edit' => [
        'back' => [
            'label' => 'Voltar',
            'route' => 'document-manager.folders.index',
            'icon' => 'fa-solid fa-angle-left',
        ],
        'new' => false,
        'show' => false,
        'save' => [
            'label' => 'Guardar',
            'icon' => 'fa-solid fa-floppy-disk',
        ],
        'delete' => false,
    ],

    'document-manager.categories.index' => [
        'dashboard' => [
            'label' => 'Dashboard',
            'route' => 'document-manager.dashboard',
            'icon' => 'fa-solid fa-chart-line',
        ],
        'new' => [
            'label' => 'Nova Categoria',
            'route' => 'document-manager.categories.create',
            'icon' => 'fa-solid fa-plus',
        ],
        'tags' => [
            'label' => 'Tags',
            'route' => 'document-manager.tags.index',
            'icon' => 'fa-solid fa-tags',
        ],
    ],

    'document-manager.categories.create' => [
        'back' => [
            'label' => 'Voltar',
            'route' => 'document-manager.categories.index',
            'icon' => 'fa-solid fa-angle-left',
        ],
        'save' => [
            'label' => 'Guardar',
            'icon' => 'fa-solid fa-floppy-disk',
        ],
    ],

    'document-manager.categories.edit' => [
        'back' => [
            'label' => 'Voltar',
            'route' => 'document-manager.categories.index',
            'icon' => 'fa-solid fa-angle-left',
        ],
        'new' => false,
        'show' => false,
        'save' => [
            'label' => 'Guardar',
            'icon' => 'fa-solid fa-floppy-disk',
        ],
        'delete' => false,
    ],

    'document-manager.tags.index' => [
        'dashboard' => [
            'label' => 'Dashboard',
            'route' => 'document-manager.dashboard',
            'icon' => 'fa-solid fa-chart-line',
        ],
        'new' => [
            'label' => 'Nova Tag',
            'route' => 'document-manager.tags.create',
            'icon' => 'fa-solid fa-plus',
        ],
        'categories' => [
            'label' => 'Categorias',
            'route' => 'document-manager.categories.index',
            'icon' => 'fa-solid fa-folder-tree',
        ],
    ],

    'document-manager.tags.create' => [
        'back' => [
            'label' => 'Voltar',
            'route' => 'document-manager.tags.index',
            'icon' => 'fa-solid fa-angle-left',
        ],
        'save' => [
            'label' => 'Guardar',
            'icon' => 'fa-solid fa-floppy-disk',
        ],
    ],

    'document-manager.tags.edit' => [
        'back' => [
            'label' => 'Voltar',
            'route' => 'document-manager.tags.index',
            'icon' => 'fa-solid fa-angle-left',
        ],
        'new' => false,
        'show' => false,
        'save' => [
            'label' => 'Guardar',
            'icon' => 'fa-solid fa-floppy-disk',
        ],
        'delete' => false,
    ],

    'document-manager.search.index' => [
        'new' => [
            'label' => 'Novo Documento',
            'route' => 'document-manager.documents.create',
            'icon' => 'fa-solid fa-plus',
        ],
        'dashboard' => [
            'label' => 'Dashboard',
            'route' => 'document-manager.dashboard',
            'icon' => 'fa-solid fa-chart-line',
        ],
        'explorer' => [
            'label' => 'Explorer',
            'route' => 'document-manager.documents.index',
            'icon' => 'fa-solid fa-folder-tree',
        ],
    ],

    'document-manager.workflow.index' => [
        'new' => [
            'label' => 'Novo Documento',
            'route' => 'document-manager.documents.create',
            'icon' => 'fa-solid fa-plus',
        ],
        'dashboard' => [
            'label' => 'Dashboard',
            'route' => 'document-manager.dashboard',
            'icon' => 'fa-solid fa-chart-line',
        ],
        'explorer' => [
            'label' => 'Explorer',
            'route' => 'document-manager.documents.index',
            'icon' => 'fa-solid fa-folder-tree',
        ],
    ],

    'document-manager.ai.index' => [
        'new' => [
            'label' => 'Novo Documento',
            'route' => 'document-manager.documents.create',
            'icon' => 'fa-solid fa-plus',
        ],
        'dashboard' => [
            'label' => 'Dashboard',
            'route' => 'document-manager.dashboard',
            'icon' => 'fa-solid fa-chart-line',
        ],
        'search' => [
            'label' => 'Search',
            'route' => 'document-manager.search.index',
            'icon' => 'fa-solid fa-magnifying-glass',
        ],
    ],

    'document-manager.diagnostics.index' => [
        'dashboard' => [
            'label' => 'Dashboard',
            'route' => 'document-manager.dashboard',
            'icon' => 'fa-solid fa-chart-line',
        ],
    ],
];

return [
    'module_home_routes' => [
        'document-manager' => 'document-manager.dashboard',
    ],

    'routes' => $routes,
];
