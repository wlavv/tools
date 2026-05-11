<?php

return [
    'layout' => 'layouts.app',
    'route_prefix' => 'investments',
    'middleware' => ['web', 'auth'],
    'pagination' => 25,
    'brokers' => [
        'ibkr' => 'IBKR',
    ],
    'asset_types' => [
        'stock' => 'Stock',
        'etf' => 'ETF',
        'forex' => 'Forex',
        'crypto' => 'Crypto',
        'fund' => 'Fund',
        'other' => 'Other',
    ],
];
