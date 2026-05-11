<?php

return [
    'mtg.index' => [
        'label' => 'mtg::breadcrumbs.mtg',
        'parent' => 'shortcuts.index',
        'translate' => true,
    ],

    'mtg.showSet' => [
        'label' => 'mtg::breadcrumbs.mtg_set',
        'parent' => 'mtg.index',
        'translate' => true,
    ],

    'mtg.findCard' => [
        'label' => 'mtg::breadcrumbs.mtg_find_card',
        'parent' => 'mtg.index',
        'translate' => true,
    ],
];
