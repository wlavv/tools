<?php

return [
    'name' => 'DocumentManager',
    'version' => '1.0.0',
    'route_prefix' => 'document-manager',
    'storage_disk' => env('DOCUMENT_MANAGER_DISK', 'local'),
    'storage_root' => env('DOCUMENT_MANAGER_STORAGE_ROOT', 'document-manager'),
    'checksum_algorithm' => 'sha256',
    'process_after_upload' => env('DOCUMENT_MANAGER_PROCESS_AFTER_UPLOAD', true),
    'process_after_upload_sync' => env('DOCUMENT_MANAGER_PROCESS_AFTER_UPLOAD_SYNC', true),

    'queues' => [
        'ocr' => 'dms_ocr',
        'ai' => 'dms_ai',
        'embeddings' => 'dms_embeddings',
        'preview' => 'dms_preview',
        'notifications' => 'dms_notifications',
        'cleanup' => 'dms_cleanup',
    ],

    'pipeline' => [
        'uploaded',
        'checksum',
        'thumbnail',
        'ocr',
        'text_extraction',
        'ai_classification',
        'tagging',
        'embeddings',
        'relations',
        'indexing',
        'ready',
    ],

    'workflow_states' => [
        'draft',
        'pending_review',
        'pending_approval',
        'approved',
        'rejected',
        'archived',
        'expired',
        'locked',
    ],

    'providers' => [
        'ocr' => env('DOCUMENT_MANAGER_OCR_PROVIDER', 'stub'),
        'ai' => env('DOCUMENT_MANAGER_AI_PROVIDER', 'stub'),
        'embeddings' => env('DOCUMENT_MANAGER_EMBEDDING_PROVIDER', 'stub'),
        'search' => env('DOCUMENT_MANAGER_SEARCH_PROVIDER', 'database'),
    ],

    'limits' => [
        'upload_max_mb' => (int) env('DOCUMENT_MANAGER_UPLOAD_MAX_MB', 50),
        'diagnostics_log_lines' => 60,
    ],
];
