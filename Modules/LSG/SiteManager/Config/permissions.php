<?php

return [
    'group' => 'LSG Site Manager',
    'permissions' => [
        'permission_lsg_site_manager_view' => [
            'label' => 'Ver LSG Site Manager',
            'description' => 'Pode consultar sites do grupo, estados e metricas.',
            'risk' => 'medium',
        ],
        'permission_lsg_site_manager_manage' => [
            'label' => 'Gerir sites LSG',
            'description' => 'Pode criar e editar lojas, servicos e sites de apresentacao.',
            'risk' => 'high',
        ],
        'permission_lsg_site_manager_pagespeed' => [
            'label' => 'Executar PageSpeed',
            'description' => 'Pode executar e consultar relatorios PageSpeed Insights.',
            'risk' => 'high',
        ],
    ],
];
