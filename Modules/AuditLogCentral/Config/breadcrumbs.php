<?php

return [
    'audit_log_central.dashboard' => [
        'label' => 'audit-log-central::page_titles.audit_log_central.dashboard',
        'parent' => 'settings.index',
    ],

    'audit_log_central.index' => [
        'label' => 'audit-log-central::page_titles.audit_log_central.index',
        'parent' => 'audit_log_central.dashboard',
    ],

    'audit_log_central.show' => [
        'label' => 'audit-log-central::page_titles.audit_log_central.show',
        'parent' => 'audit_log_central.index',
    ],

    'audit_log_central.entity.timeline' => [
        'label' => 'audit-log-central::page_titles.audit_log_central.entity.timeline',
        'parent' => 'audit_log_central.index',
    ],
];
