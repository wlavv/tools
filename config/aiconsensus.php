<?php

return [
    'keys' => [
        'openai' => env('OPENAI_API_KEY'),
        'anthropic' => env('ANTHROPIC_API_KEY'),
        'gemini' => env('GEMINI_API_KEY'),
    ],
    // Draft providers run in parallel
    'draft_providers' => ['anthropic', 'gemini'],

    // Integrator provider returns ONE final answer
    'integrator_provider' => 'openai',

    'models' => [
        'openai' => env('AICONSENSUS_OPENAI_MODEL', 'gpt-5'),
        'anthropic' => env('AICONSENSUS_ANTHROPIC_MODEL', 'claude-sonnet-4'),
        'gemini' => env('AICONSENSUS_GEMINI_MODEL', 'gemini-2.5-pro'),
    ],

    'timeouts' => [
        'draft_seconds' => (int) env('AICONSENSUS_DRAFT_TIMEOUT', 90),
        'integrator_seconds' => (int) env('AICONSENSUS_INTEGRATOR_TIMEOUT', 120),
    ],

    'limits' => [
        'draft_max_output_chars' => (int) env('AICONSENSUS_DRAFT_MAX_CHARS', 30000),
        'integrator_max_output_chars' => (int) env('AICONSENSUS_FINAL_MAX_CHARS', 45000),
    ],
];