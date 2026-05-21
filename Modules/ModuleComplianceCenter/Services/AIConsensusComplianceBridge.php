<?php

namespace Modules\ModuleComplianceCenter\Services;

use Modules\ModuleComplianceCenter\Models\ComplianceRun;

class AIConsensusComplianceBridge
{
    public function sendRunToAIConsensus(ComplianceRun $run): ?int
    {
        $gatewayClass = '\\Modules\\AIConsensus\\Services\\AIConsensusGateway';

        if (!class_exists($gatewayClass)) {
            return null;
        }

        $payload = [
            'source_module' => 'ModuleComplianceCenter',
            'source_type' => $run->source_type,
            'source_id' => $run->source_id,
            'template_key' => 'modules.lsg_validation',
            'output_type' => 'risk_analysis',
            'title' => 'Compliance review: ' . $run->module_name,
            'message' => 'Analisa este run de compliance e devolve prioridades, riscos e correcoes recomendadas.',
            'requested_by' => auth()->id(),
            'input_payload' => [
                'module_name' => $run->module_name,
                'scores' => $run->only(['structure_score', 'design_score', 'security_score', 'integration_score', 'health_score', 'final_score']),
                'findings' => $run->results()->get()->toArray(),
                'report' => optional($run->report)->report_payload,
                'source_type' => $run->source_type,
                'source_id' => $run->source_id,
            ],
            'options' => [
                'async' => true,
                'language' => 'pt',
                'return_format' => 'json',
                'consensus_mode' => 'architect_reviewer',
            ],
        ];

        $response = app($gatewayClass)->createRun($payload);

        return is_numeric($response) ? (int) $response : data_get($response, 'id');
    }
}
