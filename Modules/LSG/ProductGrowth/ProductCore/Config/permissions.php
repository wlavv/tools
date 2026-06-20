<?php

return [
    'group' => 'Product Core',
    'permissions' => [
        'permission_product_core_view' => [
            'label' => 'Ver Product Core',
            'description' => 'Pode consultar produtos, lojas, marcas, fornecedores, categorias e assets.',
            'risk' => 'medium',
        ],
        'permission_product_core_manage' => [
            'label' => 'Gerir Product Core',
            'description' => 'Pode gerir entidades principais do Product Core.',
            'risk' => 'high',
        ],
        'permission_product_core_create' => [
            'label' => 'Criar produtos',
            'description' => 'Pode criar novos produtos base.',
            'risk' => 'high',
        ],
        'permission_product_core_edit' => [
            'label' => 'Editar produtos',
            'description' => 'Pode alterar dados comerciais e operacionais de produtos.',
            'risk' => 'high',
        ],
        'permission_product_core_delete' => [
            'label' => 'Arquivar produtos',
            'description' => 'Pode arquivar produtos e marcar canais para desativacao.',
            'risk' => 'critical',
        ],
        'permission_product_core_approve' => [
            'label' => 'Aprovar produtos para venda',
            'description' => 'Pode aprovar produtos antes de sincronizacao ou publicacao.',
            'risk' => 'critical',
        ],
        'permission_product_core_sync' => [
            'label' => 'Marcar produtos para sincronizacao',
            'description' => 'Pode marcar produtos aprovados como prontos para o PrestaShop Bridge.',
            'risk' => 'critical',
        ],
        'permission_product_core_assets_manage' => [
            'label' => 'Gerir assets de produto',
            'description' => 'Pode gerir imagens, documentos e outros recursos associados a produtos.',
            'risk' => 'high',
        ],
        'permission_product_core_settings' => [
            'label' => 'Gerir configuracoes Product Core',
            'description' => 'Pode alterar configuracoes operacionais do Product Core.',
            'risk' => 'critical',
        ],
    ],
];
