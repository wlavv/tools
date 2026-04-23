<?php

return [
    'dashboard'             => [ 'label'  => 'Home',            'parent' => null ],
    'dashboard.index'       => [ 'label'  => 'Dashboard',       'parent' => null ],
    'home.index'            => [ 'label'  => 'Dashboard',       'parent' => null ],
    'administration.index'  => [ 'label'  => 'Administration',  'parent' => 'dashboard.index' ],
    'web.index'             => [ 'label'  => 'web',             'parent' => 'dashboard.index' ],
    'hr.index'              => [ 'label'  => 'HR',              'parent' => 'dashboard.index' ],
    'finance.index'         => [ 'label'  => 'finance',         'parent' => 'dashboard.index' ],
    'marketing.index'       => [ 'label'  => 'marketing',       'parent' => 'dashboard.index' ],
    'customerSupport.index' => [ 'label'  => 'customerSupport', 'parent' => 'dashboard.index' ],
    'sales.index'           => [ 'label'  => 'sales',           'parent' => 'dashboard.index' ],
    'family.index'          => [ 'label'  => 'family',          'parent' => 'dashboard.index' ],
    'webCatalogue.index'    => [ 'label'  => 'webCatalogue',    'parent' => 'dashboard.index' ],
    'multiStore.index'      => [ 'label'  => 'multiStore',      'parent' => 'dashboard.index' ],
    'shortcuts.index'       => [ 'label'  => 'shortcuts',       'parent' => 'dashboard.index' ],
    'settings.index'        => [ 'label'  => 'settings',        'parent' => 'dashboard.index' ],
];