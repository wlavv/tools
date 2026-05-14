<?php

return [
    'name' => 'ModuleHealth',
    'slug' => 'module-health',
    'route_prefix' => 'settings/module-health',
    'route_name' => 'module_health.',
    'modules_path' => base_path('Modules'),
    'default_profile' => 'structural',
    'store_scan_history' => true,
    'scan_hidden_modules' => false,
];
