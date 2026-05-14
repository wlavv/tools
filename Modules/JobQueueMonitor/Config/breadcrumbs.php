<?php

return [
    'job_queue_monitor.index' => [
        'label' => 'job-queue-monitor::page_titles.index',
        'parent' => 'settings.index',
    ],
    'job_queue_monitor.show' => [
        'label' => 'job-queue-monitor::page_titles.job_queue_monitor.show',
        'parent' => 'job_queue_monitor.index',
    ],
    'job_queue_monitor.failed.index' => [
        'label' => 'job-queue-monitor::page_titles.job_queue_monitor.failed.index',
        'parent' => 'job_queue_monitor.index',
    ],
    'job_queue_monitor.health.index' => [
        'label' => 'job-queue-monitor::page_titles.job_queue_monitor.health.index',
        'parent' => 'job_queue_monitor.index',
    ],
    'job_queue_monitor.settings.index' => [
        'label' => 'job-queue-monitor::page_titles.job_queue_monitor.settings.index',
        'parent' => 'job_queue_monitor.index',
    ],
];
