<?php

return [
    'dashboard'             => [ 'label'  => 'app::breadcrumbs.dashboard.index',        'parent' => null ],
    'dashboard.index'       => [ 'label'  => 'app::breadcrumbs.dashboard.index',        'parent' => null ],
    'home.index'            => [ 'label'  => 'app::breadcrumbs.home.index',             'parent' => null ],
    'administration.index'  => [ 'label'  => 'app::breadcrumbs.administration.index',   'parent' => 'dashboard.index' ],
    'web.index'             => [ 'label'  => 'app::breadcrumbs.web.index',              'parent' => 'dashboard.index' ],
    'hr.index'              => [ 'label'  => 'app::breadcrumbs.hr.index',               'parent' => 'dashboard.index' ],
    'finance.index'         => [ 'label'  => 'app::breadcrumbs.finance.index',          'parent' => 'dashboard.index' ],
    'purchasing.index'      => [ 'label'  => 'app::breadcrumbs.purchasing.index',       'parent' => 'dashboard.index' ],
    'marketing.index'       => [ 'label'  => 'app::breadcrumbs.marketing.index',        'parent' => 'dashboard.index' ],
    'customerSupport.index' => [ 'label'  => 'app::breadcrumbs.customerSupport.index',  'parent' => 'dashboard.index' ],
    'sales.index'           => [ 'label'  => 'app::breadcrumbs.sales.index',            'parent' => 'dashboard.index' ],
    'family.index'          => [ 'label'  => 'app::breadcrumbs.family.index',           'parent' => 'dashboard.index' ],
    'webCatalogue.index'    => [ 'label'  => 'app::breadcrumbs.webCatalogue.index',     'parent' => 'lsg.index' ],
    'webcatalogue.index'    => [ 'label'  => 'app::breadcrumbs.webCatalogue.index',     'parent' => 'lsg.index' ],
    'multiStore.index'      => [ 'label'  => 'app::breadcrumbs.multiStore.index',       'parent' => 'lsg.index' ],
    'lsg.index'             => [ 'label'  => 'app::breadcrumbs.lsg.index',              'parent' => 'dashboard.index' ],
    'shortcuts.index'       => [ 'label'  => 'app::breadcrumbs.shortcuts.index',        'parent' => 'dashboard.index' ],
    'settings.index'        => [ 'label'  => 'app::breadcrumbs.settings.index',         'parent' => 'dashboard.index' ],
];
