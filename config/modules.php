<?php

return [
    'path' => base_path('Modules'),
    'cache' => false,
    'required_files' => [
        'module.json',
        'Routes/web.php',
        'Providers',
        'Resources/views/Index.blade.php',
        'Resources/views/Includes/css.blade.php',
        'Resources/views/Includes/js.blade.php',
    ],
];
