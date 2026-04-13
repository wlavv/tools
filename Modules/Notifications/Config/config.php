<?php

return [
    'default_channels' => ['internal'],
    'default_queue' => 'default',
    'polling_seconds' => 30,
    'external_channels' => ['email', 'whatsapp', 'discord', 'sms', 'webhook'],
    'supported_channels' => ['internal', 'email', 'whatsapp', 'discord', 'sms', 'webhook'],
    'component_alias' => 'notifications-dropdown',
    'test_route_enabled' => true,
    'test_default_channels' => ['internal'],
];
