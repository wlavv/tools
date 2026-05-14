<?php

return [
    'name' => 'Job Queue Monitor',
    'slug' => 'job-queue-monitor',
    'route_prefix' => 'settings/job-queue-monitor',
    'route_name' => 'job_queue_monitor',

    'retention_days' => env('JOB_QUEUE_MONITOR_RETENTION_DAYS', 30),
    'stale_pending_minutes' => env('JOB_QUEUE_MONITOR_STALE_PENDING_MINUTES', 15),
    'stale_processing_minutes' => env('JOB_QUEUE_MONITOR_STALE_PROCESSING_MINUTES', 30),
    'critical_failures_threshold' => env('JOB_QUEUE_MONITOR_CRITICAL_FAILURES_THRESHOLD', 5),
    'critical_failures_window_minutes' => env('JOB_QUEUE_MONITOR_CRITICAL_FAILURES_WINDOW_MINUTES', 30),

    'email_enabled' => env('JOB_QUEUE_MONITOR_EMAIL_ENABLED', true),
    'email_to' => env('JOB_QUEUE_MONITOR_EMAIL_TO', env('MAIL_FROM_ADDRESS')),
    'email_subject_prefix' => env('JOB_QUEUE_MONITOR_EMAIL_SUBJECT_PREFIX', '[WebTools Queue Alert]'),

    'notifications_enabled' => env('JOB_QUEUE_MONITOR_NOTIFICATIONS_ENABLED', true),
    'notifications_table' => env('JOB_QUEUE_MONITOR_NOTIFICATIONS_TABLE', 'notifications'),

    'queues' => [
        'default',
        'emails',
        'imports',
        'sync',
        'vat',
        'moloni',
        'ocr',
    ],

    'critical_jobs' => [
        // 'App\\Jobs\\ExampleCriticalJob' => ['expected_every_minutes' => 60],
    ],
];
