<?php

return [
    'validators' => [
        'structure' => [
            'label' => 'Structure Validator',
            'module' => 'ModuleStructureValidator',
            'service' => \Modules\ModuleStructureValidator\Services\ModuleStructureValidatorService::class,
            'required' => true,
            'weight' => 25,
            'enabled' => true,
        ],
        'design' => [
            'label' => 'Design Validator',
            'module' => 'ModuleDesignValidator',
            'service' => \Modules\ModuleDesignValidator\Services\ModuleDesignValidatorService::class,
            'required' => true,
            'weight' => 20,
            'enabled' => true,
        ],
        'security' => [
            'label' => 'Security Validator',
            'module' => 'ModuleSecurityValidator',
            'service' => \Modules\ModuleSecurityValidator\Services\ModuleSecurityValidatorService::class,
            'required' => true,
            'weight' => 25,
            'enabled' => true,
        ],
        'integration' => [
            'label' => 'Integration Validator',
            'module' => 'ModuleIntegrationValidator',
            'service' => \Modules\ModuleIntegrationValidator\Services\ModuleIntegrationValidatorService::class,
            'required' => true,
            'weight' => 15,
            'enabled' => true,
        ],
        'health' => [
            'label' => 'Health Bridge',
            'module' => 'ModuleHealthBridge',
            'service' => \Modules\ModuleHealthBridge\Services\ModuleHealthBridgeService::class,
            'required' => false,
            'weight' => 15,
            'enabled' => true,
        ],
    ],
];
