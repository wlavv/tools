<?php

return [
    'internal_rules_engine' => [
        'name' => 'Internal Rules Engine',
        'driver' => 'internal_rules_engine',
        'model' => 'rules-v1',
        'is_active' => true,
        'priority' => 1,
        'weight' => 1.0,
    ],
    'openai_gpt' => [
        'name' => 'OpenAI GPT',
        'driver' => 'openai',
        'model' => env('OPENAI_DEFAULT_MODEL', 'gpt-4o'),
        'is_active' => false,
        'priority' => 20,
        'weight' => 1.0,
    ],
    'anthropic_claude' => [
        'name' => 'Anthropic Claude',
        'driver' => 'anthropic',
        'model' => env('ANTHROPIC_DEFAULT_MODEL', 'claude-3-5-sonnet-latest'),
        'is_active' => false,
        'priority' => 30,
        'weight' => 1.0,
    ],
    'google_gemini' => [
        'name' => 'Google Gemini',
        'driver' => 'gemini',
        'model' => env('GEMINI_DEFAULT_MODEL', 'gemini-2.0-flash'),
        'is_active' => false,
        'priority' => 40,
        'weight' => 1.0,
    ],
    'local_llm' => [
        'name' => 'Local LLM',
        'driver' => 'local',
        'model' => null,
        'is_active' => false,
        'priority' => 50,
        'weight' => 0.8,
    ],
];
