<?php

return [
    'layout' => 'layouts.app',

    'tables' => [
        'runs' => 'ai_runs',
        'responses' => 'ai_run_responses',
        'files' => 'ai_files',
        'credentials' => 'ai_provider_credentials',
    ],

    'storage' => [
        'disk' => env('AI_CONSENSUS_STORAGE_DISK', 'local'),
        'folder' => env('AI_CONSENSUS_STORAGE_FOLDER', 'ai-consensus'),
        'max_files' => 10,
        'max_file_size_kb' => 10240,
    ],

    'http' => [
        'timeout' => 180,
        'connect_timeout' => 20,
    ],

    'providers' => [
        'anthropic' => [
            'label' => 'Claude',
            'api_base' => env('ANTHROPIC_API_BASE', 'https://api.anthropic.com'),
            'default_model' => env('ANTHROPIC_DEFAULT_MODEL', 'claude-3-7-sonnet-latest'),
        ],
        'gemini' => [
            'label' => 'Gemini',
            'api_base' => env('GEMINI_API_BASE', 'https://generativelanguage.googleapis.com'),
            'default_model' => env('GEMINI_DEFAULT_MODEL', 'gemini-2.0-flash'),
        ],
        'openai' => [
            'label' => 'OpenAI',
            'api_base' => env('OPENAI_API_BASE', 'https://api.openai.com/v1'),
            'default_model' => env('OPENAI_DEFAULT_MODEL', 'gpt-5'),
        ],
    ],

    'pricing' => [
        'anthropic' => [
            'claude-3-7-sonnet-latest' => ['input_per_million' => 3.00, 'output_per_million' => 15.00],
            'default' => ['input_per_million' => 3.00, 'output_per_million' => 15.00],
        ],
        'gemini' => [
            'gemini-2.0-flash' => ['input_per_million' => 0.10, 'output_per_million' => 0.40],
            'default' => ['input_per_million' => 0.10, 'output_per_million' => 0.40],
        ],
        'openai' => [
            'gpt-5' => ['input_per_million' => 1.25, 'output_per_million' => 10.00],
            'gpt-4.1' => ['input_per_million' => 2.00, 'output_per_million' => 8.00],
            'default' => ['input_per_million' => 1.25, 'output_per_million' => 10.00],
        ],
    ],

    'defaults' => [
        'template_key' => 'module_scaffold_v1',
    ],
];
