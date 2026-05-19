<?php

namespace Modules\AIConsensus\Services;

use Modules\AIConsensus\Database\Seeders\AIConsensusCentralSeeder;
use Modules\AIConsensus\Models\AIConsensusTemplate;

class AIConsensusTemplateResolver
{
    public function resolve(string $templateKey, ?string $outputType = null): ?AIConsensusTemplate
    {
        $template = $this->findActive($templateKey);

        if (!$template && AIConsensusTemplate::query()->count() === 0) {
            app(AIConsensusCentralSeeder::class)->run();
            $template = $this->findActive($templateKey);
        }

        if ($template || !$outputType) {
            return $template;
        }

        $fallback = data_get(config('ai-consensus-output-types'), $outputType . '.default_template')
            ?: data_get(config('ai_consensus_output_types'), $outputType . '.default_template');

        if (!$fallback) {
            return null;
        }

        return $this->findActive($fallback);
    }

    protected function findActive(string $templateKey): ?AIConsensusTemplate
    {
        return AIConsensusTemplate::query()
            ->where('template_key', $templateKey)
            ->where('is_active', true)
            ->first();
    }
}
