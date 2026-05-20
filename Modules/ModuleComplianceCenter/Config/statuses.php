<?php

return [
    'run' => [
        'pending',
        'processing',
        'completed',
        'failed',
        'approved',
        'approved_with_warnings',
        'changes_required',
        'rejected',
        'manual_review_required',
        'archived',
    ],
    'validator' => [
        'available',
        'unavailable',
        'disabled',
        'error',
    ],
    'run_validator' => [
        'pending',
        'processing',
        'passed',
        'failed',
        'warning',
        'skipped',
        'unavailable',
        'error',
    ],
    'finding' => [
        'passed',
        'failed',
        'warning',
        'skipped',
        'manual_review_required',
    ],
    'severity' => [
        'info',
        'low',
        'medium',
        'high',
        'critical',
        'blocker',
    ],
];
