<?php

return [
    'name' => 'Mtg',
    'slug' => 'mtg',
    'enabled' => true,
    'route_prefix' => 'webmaster/mtg',
    'middleware' => ['web', 'auth'],
    'layout' => 'layouts.app',
    'sync_sets_on_index' => true,
    'sync_sets_limit' => 10,
];
