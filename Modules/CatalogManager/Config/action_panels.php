<?php

return [
    'enabled' => true,
    'limit_per_panel' => 8,
    'panels' => [
        'sync_pending' => [
            'enabled' => true, 'title' => 'Sync Pendente', 'description' => 'Itens em fila para sincronização PrestaShop.',
            'icon' => 'fa-solid fa-clock', 'tone' => 'primary',
            'provider' => Modules\CatalogManager\Services\ActionPanels\Panels\SyncStatusPanel::class, 'status' => 'pending', 'order' => 20,
        ],
        'sync_processing' => [
            'enabled' => true, 'title' => 'Sync em Curso', 'description' => 'Itens atualmente marcados como em processamento.',
            'icon' => 'fa-solid fa-arrows-rotate', 'tone' => 'info',
            'provider' => Modules\CatalogManager\Services\ActionPanels\Panels\SyncStatusPanel::class, 'status' => 'processing', 'order' => 30,
        ],
        'sync_failed' => [
            'enabled' => true, 'title' => 'Sync Falhado', 'description' => 'Itens com erro na sincronização.',
            'icon' => 'fa-solid fa-triangle-exclamation', 'tone' => 'danger',
            'provider' => Modules\CatalogManager\Services\ActionPanels\Panels\SyncStatusPanel::class, 'status' => 'failed', 'order' => 40,
        ],
    ],
];
