<?php

return [
    'enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | Module Health location
    |--------------------------------------------------------------------------
    | The bridge does not replace Module Health. It tries to detect the existing
    | Module Health module/service and normalizes the result into the common
    | ModuleComplianceCore result contract.
    */
    'module_health_module_name' => 'ModuleHealth',
    'module_health_path' => base_path('Modules/ModuleHealth'),

    /*
    |--------------------------------------------------------------------------
    | Candidate services
    |--------------------------------------------------------------------------
    | Add here the concrete service class used by your Module Health module when
    | available. The bridge will try these classes in order.
    */
    'candidate_services' => [
        Modules\ModuleHealth\Services\ModuleHealthScanService::class,
        Modules\ModuleHealth\Services\ModuleHealthService::class,
        Modules\ModuleHealth\Services\ModuleHealthAnalyzerService::class,
        Modules\ModuleHealth\Services\ModuleDependencyAnalyzerService::class,
        Modules\ModuleHealth\Services\ModuleHealthScannerService::class,
    ],

    'candidate_methods' => [
        'latestOrRun',
        'latestScan',
        'analyzeModule',
        'scanModule',
        'validateModule',
        'runModuleCheck',
        'run',
        'analyze',
        'validate',
        'scan',
    ],

    /*
    | If false, missing Module Health returns warning/manual review instead of
    | failing the validation run. Recommended while the ecosystem is evolving.
    */
    'fail_without_module_health' => false,

    'known_result_keys' => [
        'findings', 'results', 'checks', 'items', 'issues', 'warnings', 'errors', 'data'
    ],

    'status_aliases' => [
        'ok' => 'passed',
        'pass' => 'passed',
        'passed' => 'passed',
        'success' => 'passed',
        'healthy' => 'passed',
        'functional' => 'passed',
        'enhanced' => 'passed',
        'fail' => 'failed',
        'failed' => 'failed',
        'error' => 'failed',
        'critical' => 'failed',
        'broken' => 'failed',
        'incomplete' => 'warning',
        'warning' => 'warning',
        'warn' => 'warning',
        'skipped' => 'skipped',
        'skip' => 'skipped',
        'manual_review_required' => 'manual_review_required',
        'manual' => 'manual_review_required',
    ],

    'severity_aliases' => [
        'info' => 'info',
        'notice' => 'info',
        'low' => 'low',
        'minor' => 'low',
        'medium' => 'medium',
        'warning' => 'medium',
        'high' => 'high',
        'error' => 'high',
        'critical' => 'critical',
        'blocker' => 'blocker',
    ],
];
