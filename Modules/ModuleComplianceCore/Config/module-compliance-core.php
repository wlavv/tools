<?php

return [
    'severity_weights' => [
        'info' => 0,
        'low' => 2,
        'medium' => 5,
        'high' => 10,
        'critical' => 20,
        'blocker' => 35,
    ],

    'status_weights' => [
        'passed' => 0,
        'warning' => 0.5,
        'failed' => 1,
        'manual_review_required' => 0.75,
        'skipped' => 0,
    ],
];
