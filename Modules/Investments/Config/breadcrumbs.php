<?php

return [
    'investments.index' => [
        'label' => 'Investments',
        'parent' => 'finance.index',
        'translate' => false,
    ],
    'investments.assets.index' => [
        'label' => 'Assets',
        'parent' => 'investments.index',
        'translate' => false,
    ],
    'investments.assets.create' => [
        'label' => 'New Asset',
        'parent' => 'investments.assets.index',
        'translate' => false,
    ],
    'investments.broker_accounts.index' => [
        'label' => 'Broker Accounts',
        'parent' => 'investments.index',
        'translate' => false,
    ],
    'investments.broker_accounts.create' => [
        'label' => 'New Broker Account',
        'parent' => 'investments.broker_accounts.index',
        'translate' => false,
    ],
    'investments.broker_accounts.edit' => [
        'label' => 'Edit Broker Account',
        'parent' => 'investments.broker_accounts.index',
        'translate' => false,
    ],
    'investments.positions.index' => [
        'label' => 'Positions',
        'parent' => 'investments.index',
        'translate' => false,
    ],
    'investments.positions.create' => [
        'label' => 'New Position',
        'parent' => 'investments.positions.index',
        'translate' => false,
    ],
    'investments.positions.show' => [
        'label' => 'Position Details',
        'parent' => 'investments.positions.index',
        'translate' => false,
    ],
];
