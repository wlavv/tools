<?php

return [
    'ai_consensus.index' => [
        'label' => __('ai-consensus::breadcrumbs.ai_consensus'),
        'parent' => 'administration.index',
        'translate' => false,
    ],
    'ai_consensus.create' => [
        'label' => __('ai-consensus::breadcrumbs.ai_consensus_new'),
        'parent' => 'ai_consensus.index',
        'translate' => false,
    ],
    'ai_consensus.show' => [
        'label' => __('ai-consensus::breadcrumbs.ai_consensus_show'),
        'parent' => 'ai_consensus.index',
        'translate' => false,
    ],
    'ai_consensus.edit' => [
        'label' => __('ai-consensus::breadcrumbs.ai_consensus_edit'),
        'parent' => 'ai_consensus.index',
        'translate' => false,
    ],
];