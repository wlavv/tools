<?php

return [
    'integration_health.index' => [
        'label' => 'integration-health::page_titles.integration_health.index',
        'parent' => 'settings.index',
    ],

    'integration_health.integrations.index' => [
        'label' => 'integration-health::page_titles.integration_health.integrations.index',
        'parent' => 'integration_health.index',
    ],

    'integration_health.integrations.create' => [
        'label' => 'integration-health::page_titles.integration_health.integrations.create',
        'parent' => 'integration_health.integrations.index',
    ],

    'integration_health.integrations.edit' => [
        'label' => 'integration-health::page_titles.integration_health.integrations.edit',
        'parent' => 'integration_health.integrations.index',
    ],

    'integration_health.events.index' => [
        'label' => 'integration-health::page_titles.integration_health.events.index',
        'parent' => 'integration_health.index',
    ],
];
