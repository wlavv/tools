<?php

return [
    'steps' => [
        'supplier_selection' => [
            'label' => 'Fornecedor',
            'description' => 'Selecionar fornecedor e analisar condições comerciais.',
        ],
        'order_note' => [
            'label' => 'Order Note',
            'description' => 'Criar intenção de compra e adicionar produtos.',
        ],
        'billing' => [
            'label' => 'Faturação',
            'description' => 'Converter parcial ou totalmente para encomenda faturada.',
        ],
        'reception' => [
            'label' => 'Receção',
            'description' => 'Receber produtos total ou parcialmente.',
        ],
        'validation' => [
            'label' => 'Validação',
            'description' => 'Resolver discrepâncias, pendentes e documentos associados.',
        ],
        'closed' => [
            'label' => 'Fecho',
            'description' => 'Fechar ciclo operacional e arquivar histórico.',
        ],
    ],
];
