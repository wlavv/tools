<?php

namespace Modules\AIConsensus\Services;

use Modules\AIConsensus\Models\AIConsensusTemplate;

class AIConsensusTemplateResolver
{
    public function resolve(string $templateKey, ?string $outputType = null): ?AIConsensusTemplate
    {
        $template = AIConsensusTemplate::query()
            ->where('template_key', $templateKey)
            ->where('is_active', true)
            ->first();

        if ($template || !$outputType) {
            return $template;
        }

        $fallback = data_get(config('ai-consensus-output-types'), $outputType . '.default_template')
            ?: data_get(config('ai_consensus_output_types'), $outputType . '.default_template');

        if (!$fallback) {
            return null;
        }

        return AIConsensusTemplate::query()
            ->where('template_key', $fallback)
            ->where('is_active', true)
            ->first();
    }
}
