<?php

return [
    'rules' => [
        'All modules are Laravel modules under /Modules/{ModuleName}.',
        'Every module requires a valid module.json and a module ServiceProvider.',
        'Modules may expose routes/web.php and routes/api.php.',
        'Use Config files, translations loaded with loadTranslationsFrom, and permissions prefixed permission_*.',
        'Migrations must use clear prefixes and short index names.',
        'Views must follow the LSG layout, with breadcrumbs and page actions.',
        'Lists should use DataTables where appropriate.',
        'Alerts should use SweetAlerts where appropriate.',
        'Uploads should use Dropzone where applicable.',
        'Use Font Awesome icons according to LSG conventions.',
        'Load CSS/JS through module includes or layout stacks.',
        'Avoid changing core code unless necessary.',
        'Integrations must be incremental, reversible, and production-safe.',
        'Preserve backward compatibility.',
    ],
    'security' => [
        'Never apply generated code automatically in production.',
        'Never execute generated code without validation.',
        'Never alter .env directly.',
        'Never write outside authorized module paths.',
        'Never execute shell commands supplied by AI.',
        'Store all prompts and responses.',
        'Require human approval for critical outputs.',
        'Use sandbox mode for future module generation.',
        'Log all critical actions.',
    ],
];
