<?php

return [
    'module_home_routes' => [
        'package_tracker' => 'package_tracker.dashboard',
    ],

    'routes' => [
        'package_tracker.dashboard' => [
            'new' => [
                'label' => 'New shipment',
                'name' => 'New shipment',
                'route' => 'package_tracker.shipments.create',
                'icon' => 'fa-solid fa-plus',
                'class' => 'lsg-action-btn lsg-action-btn--success',
            ],
            'carriers' => [
                'label' => 'Carriers',
                'name' => 'Carriers',
                'route' => 'package_tracker.carriers.index',
                'icon' => 'fa-solid fa-truck',
                'class' => 'lsg-action-btn lsg-action-btn--primary',
            ],
            'clients' => [
                'label' => 'Clients',
                'name' => 'Clients',
                'route' => 'package_tracker.clients.index',
                'icon' => 'fa-solid fa-users',
                'class' => 'lsg-action-btn lsg-action-btn--primary',
            ],
        ],
        'package_tracker.shipments.index' => [
            'new' => [
                'label' => 'New shipment',
                'name' => 'New shipment',
                'route' => 'package_tracker.shipments.create',
                'icon' => 'fa-solid fa-plus',
                'class' => 'lsg-action-btn lsg-action-btn--success',
            ],
        ],
        'package_tracker.shipments.create' => [
            'back' => 'package_tracker.shipments.index',
            'save' => true,
        ],
        'package_tracker.shipments.show' => [
            'back' => 'package_tracker.shipments.index',
            'delete' => false,
            'sync' => [
                'label' => 'Sync',
                'name' => 'Sync',
                'route' => 'package_tracker.shipments.sync',
                'icon' => 'fa-solid fa-rotate',
                'class' => 'lsg-action-btn lsg-action-btn--primary',
                'type' => 'form',
                'method' => 'POST',
            ],
        ],
        'package_tracker.carriers.index' => [
            'new' => [
                'label' => 'New carrier',
                'name' => 'New carrier',
                'route' => 'package_tracker.carriers.create',
                'icon' => 'fa-solid fa-plus',
                'class' => 'lsg-action-btn lsg-action-btn--success',
            ],
        ],
        'package_tracker.carriers.create' => [
            'back' => 'package_tracker.carriers.index',
            'save' => true,
        ],
        'package_tracker.carriers.edit' => [
            'back' => 'package_tracker.carriers.index',
            'save' => true,
            'delete' => false,
        ],
        'package_tracker.clients.index' => [
            'new' => [
                'label' => 'New client',
                'name' => 'New client',
                'route' => 'package_tracker.clients.create',
                'icon' => 'fa-solid fa-plus',
                'class' => 'lsg-action-btn lsg-action-btn--success',
            ],
        ],
        'package_tracker.clients.create' => [
            'back' => 'package_tracker.clients.index',
            'save' => true,
        ],
        'package_tracker.clients.show' => [
            'back' => 'package_tracker.clients.index',
            'edit' => 'package_tracker.clients.edit',
            'delete' => false,
        ],
        'package_tracker.clients.edit' => [
            'back' => 'package_tracker.clients.index',
            'save' => true,
            'show' => 'package_tracker.clients.show',
            'delete' => false,
        ],
    ],
];
