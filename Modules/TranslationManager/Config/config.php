<?php

return [
    'name' => 'Translation Manager',
    'slug' => 'translation-manager',

    'modules_path' => base_path('Modules'),

    'base_lang_paths' => [
        'Resources/lang',
        'lang',
    ],

    'system_sources' => [
        [
            'name' => 'Application / System',
            'slug' => 'app',
            'path' => base_path(),
            'base_lang_paths' => ['resources/lang'],
        ],
    ],

    'override_path' => storage_path('app/translations/modules'),

    'locales' => [
        'pt' => 'Português',
        'en' => 'English',
        'fr' => 'Français',
        'es' => 'Español',
    ],

    'default_locale' => 'pt',

    'route_prefix' => 'settings/translation-manager',
    'route_middleware' => ['web', 'auth'],
];
