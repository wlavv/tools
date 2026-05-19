<?php

return [
    'ai_consensus.index' => [
        'label' => 'ai-consensus::breadcrumbs.ai_consensus',
        'parent' => 'administration.index',
        'translate' => true,
    ],

    'ai_consensus.create' => [
        'label' => 'ai-consensus::breadcrumbs.ai_consensus_new',
        'parent' => 'ai_consensus.index',
        'translate' => true,
    ],

    'ai_consensus.runs.index' => [
        'label' => 'AI Consensus Runs',
        'parent' => 'ai_consensus.index',
    ],

    'ai_consensus.runs.create' => [
        'label' => 'New AI Consensus Run',
        'parent' => 'ai_consensus.runs.index',
    ],

    'ai_consensus.runs.show' => [
        'label' => 'AI Consensus Run',
        'parent' => 'ai_consensus.runs.index',
    ],

    'ai_consensus.runs.store' => [
        'label' => 'New AI Consensus Run',
        'parent' => 'ai_consensus.runs.index',
    ],

    'ai_consensus.templates.index' => [
        'label' => 'AI Consensus Templates',
        'parent' => 'ai_consensus.index',
    ],

    'ai_consensus.templates.edit' => [
        'label' => 'Edit Template',
        'parent' => 'ai_consensus.templates.index',
    ],

    'ai_consensus.providers.index' => [
        'label' => 'AI Consensus Providers',
        'parent' => 'ai_consensus.index',
    ],

    'ai_consensus.logs.index' => [
        'label' => 'AI Consensus Logs',
        'parent' => 'ai_consensus.index',
    ],

    'ai_consensus.store' => [
        'label' => 'ai-consensus::breadcrumbs.ai_consensus_new',
        'parent' => 'ai_consensus.index',
        'translate' => true,
    ],

    'ai_consensus.show' => [
        'label' => 'ai-consensus::breadcrumbs.ai_consensus_show',
        'parent' => 'ai_consensus.index',
        'translate' => true,
    ],

    'ai_consensus.edit' => [
        'label' => 'ai-consensus::breadcrumbs.ai_consensus_edit',
        'parent' => 'ai_consensus.index',
        'translate' => true,
    ],

    'ai_consensus.update' => [
        'label' => 'ai-consensus::breadcrumbs.ai_consensus_edit',
        'parent' => 'ai_consensus.index',
        'translate' => true,
    ],

    'ai_consensus.destroy' => [
        'label' => 'ai-consensus::breadcrumbs.ai_consensus',
        'parent' => 'ai_consensus.index',
        'translate' => true,
    ],

    'ai_consensus.credentials.save' => [
        'label' => 'ai-consensus::breadcrumbs.ai_consensus',
        'parent' => 'ai_consensus.index',
        'translate' => true,
    ],

    'ai_consensus.reprocess' => [
        'label' => 'ai-consensus::breadcrumbs.ai_consensus_show',
        'parent' => 'ai_consensus.show',
        'translate' => true,
    ],
];
