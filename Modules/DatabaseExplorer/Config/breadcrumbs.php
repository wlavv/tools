<?php

return [
    'database_explorer.index' => [
        'label' => 'database-explorer::breadcrumbs.database_explorer',
        'parent' => 'settings.index',
        'translate' => true,
    ],

    'database_explorer.show' => [
        'label' => 'database-explorer::breadcrumbs.database_explorer_table',
        'parent' => 'database_explorer.index',
        'translate' => true,
    ],

    'database_explorer.health' => [
        'label' => 'database-explorer::breadcrumbs.database_explorer_health',
        'parent' => 'database_explorer.index',
        'translate' => true,
    ],

    'database_explorer.snapshots' => [
        'label' => 'database-explorer::breadcrumbs.database_explorer_snapshots',
        'parent' => 'database_explorer.index',
        'translate' => true,
    ],
];
