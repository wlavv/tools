<?php

return [
    'module_root' => base_path('Modules'),

    'dangerous_php_functions' => [
        'exec', 'shell_exec', 'system', 'passthru', 'proc_open', 'popen', 'pcntl_exec',
    ],

    'filesystem_write_patterns' => [
        'file_put_contents', 'fopen', 'fwrite', 'Storage::put', 'Storage::disk', 'mkdir', 'rename', 'unlink',
    ],

    'request_path_patterns' => [
        '$request->path', '$request->file', '$request->input', '$request->get',
        'request()->input', 'request()->get', 'request()->file',
    ],

    'route_protection_patterns' => [
        'middleware', 'auth', 'permission', 'can:',
    ],

    'upload_validation_patterns' => [
        'mimes:', 'mimetypes:', 'image', 'max:', 'dimensions:', 'File::types', 'Rule::file', 'extensions:',
    ],

    'core_forbidden_paths' => [
        'app/', 'routes/', 'config/', 'database/migrations/', 'resources/views/', 'public/index.php', '.env',
    ],

    'allowed_external_write_roots' => [
        'storage/app', 'storage/logs', 'public/uploads', 'public/storage',
    ],

    'severity' => [
        'module_path_missing' => 'blocker',
        'env_write' => 'blocker',
        'shell_execution' => 'critical',
        'unprotected_route' => 'high',
        'unsafe_upload' => 'high',
        'path_traversal' => 'critical',
        'core_write' => 'critical',
        'raw_sql' => 'medium',
        'csrf_disabled' => 'high',
        'debug_code' => 'medium',
        'mass_assignment' => 'medium',
    ],

    'scan_extensions' => ['php', 'blade.php', 'js'],
];
