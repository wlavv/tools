<?php

namespace Modules\AIConsensus\Services;

class AIConsensusContextBuilder
{
    public function build(array $payload): array
    {
        return [
            'source' => [
                'module' => $payload['source_module'] ?? null,
                'type' => $payload['source_type'] ?? null,
                'id' => $payload['source_id'] ?? null,
            ],
            'runtime' => [
                'app' => config('app.name'),
                'environment' => config('app.env'),
                'created_at' => now()->toIso8601String(),
            ],
            'lsg_standard' => config('ai-consensus-lsg', config('ai_consensus_lsg', [])),
        ];
    }
}
