<?php

return [
    'name' => 'IdeaLab',

    'route_prefix' => 'idealab',
    'route_name_prefix' => 'idealab.',
    'middleware' => ['web', 'auth'],

    'permissions' => [
        'view' => 'permission_idealab_view',
        'create' => 'permission_idealab_create',
        'edit' => 'permission_idealab_edit',
        'delete' => 'permission_idealab_delete',
        'ai_consensus' => 'permission_idealab_ai_consensus',
        'convert_project' => 'permission_idealab_convert_project',
        'manage_templates' => 'permission_idealab_manage_templates',
    ],

    'statuses' => [
        'draft' => 'Draft',
        'captured' => 'Captured',
        'under_ai_review' => 'Under AI Review',
        'refined' => 'Refined',
        'candidate_project' => 'Candidate Project',
        'sandbox_generated' => 'Sandbox Generated',
        'needs_revision' => 'Needs Revision',
        'validation_passed' => 'Validation Passed',
        'approved' => 'Approved',
        'converted' => 'Converted',
        'archived' => 'Archived',
    ],

    'priorities' => [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'strategic' => 'Strategic',
    ],

    'sources' => [
        'manual' => 'Manual',
        'chatgpt' => 'ChatGPT',
        'meeting' => 'Meeting',
        'client' => 'Client',
        'internal_problem' => 'Internal Problem',
        'market_opportunity' => 'Market Opportunity',
    ],

    'ai_consensus' => [
        'enabled' => true,
        'entrypoint_type' => 'idea_discussion',
        'service_class' => \Modules\AIConsensus\Services\AIConsensusGateway::class,
        'default_template_key' => 'idea_deconstruction',
        'allow_chat_mode' => true,
        'store_prompt_payload' => true,
        'store_response_payload' => true,
    ],

    'project_manager' => [
        'enabled' => true,
        'service_class' => \Modules\ProjectManager\Services\ProjectCreationFromIdeaService::class,
        'fallback_mode' => 'payload_only',
    ],

    'scoring' => [
        'weights' => [
            'opportunity' => 0.35,
            'strategic_fit' => 0.30,
            'reusability' => 0.15,
            'monetization' => 0.15,
            'effort_penalty' => 0.20,
            'risk_penalty' => 0.15,
        ],
    ],
];
