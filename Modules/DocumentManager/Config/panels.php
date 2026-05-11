<?php

return [
    'operational' => [
        'missing_ocr' => [
            'label' => 'Sem OCR',
            'icon' => 'fa-solid fa-file-lines',
            'table' => 'document_core_documents',
            'description' => 'Documentos prontos que ainda nao tem OCR registado.',
        ],
        'ai_failures' => [
            'label' => 'Falhas AI',
            'icon' => 'fa-solid fa-brain',
            'table' => 'document_logs_ai',
            'description' => 'Eventos AI com erro ou provider indisponivel.',
        ],
        'pending_approvals' => [
            'label' => 'Aprovacoes',
            'icon' => 'fa-solid fa-clipboard-check',
            'table' => 'document_workflow_approvals',
            'description' => 'Aprovacoes pendentes no workflow documental.',
        ],
        'expiring_documents' => [
            'label' => 'A expirar',
            'icon' => 'fa-solid fa-hourglass-half',
            'table' => 'document_core_documents',
            'description' => 'Documentos com validade proxima ou retencao critica.',
        ],
    ],
];
