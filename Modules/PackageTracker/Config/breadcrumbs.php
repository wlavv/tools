<?php

return [
    'package_tracker.dashboard' => [
        'label' => 'Package Tracker',
        'parent' => null,
        'translate' => false,
    ],

    'package_tracker.shipments.index' => [
        'label' => 'Shipments',
        'parent' => 'package_tracker.dashboard',
        'translate' => false,
    ],

    'package_tracker.shipments.create' => [
        'label' => 'New shipment',
        'parent' => 'package_tracker.shipments.index',
        'translate' => false,
    ],

    'package_tracker.shipments.show' => [
        'label' => 'Shipment detail',
        'parent' => 'package_tracker.shipments.index',
        'translate' => false,
    ],

    'package_tracker.carriers.index' => [
        'label' => 'Carriers',
        'parent' => 'package_tracker.dashboard',
        'translate' => false,
    ],

    'package_tracker.carriers.create' => [
        'label' => 'New carrier',
        'parent' => 'package_tracker.carriers.index',
        'translate' => false,
    ],

    'package_tracker.carriers.edit' => [
        'label' => 'Edit carrier',
        'parent' => 'package_tracker.carriers.index',
        'translate' => false,
    ],

    'package_tracker.clients.index' => [
        'label' => 'Clients',
        'parent' => 'package_tracker.dashboard',
        'translate' => false,
    ],

    'package_tracker.clients.create' => [
        'label' => 'New client',
        'parent' => 'package_tracker.clients.index',
        'translate' => false,
    ],

    'package_tracker.clients.show' => [
        'label' => 'Client account',
        'parent' => 'package_tracker.clients.index',
        'translate' => false,
    ],

    'package_tracker.clients.edit' => [
        'label' => 'Edit client',
        'parent' => 'package_tracker.clients.index',
        'translate' => false,
    ],
];
