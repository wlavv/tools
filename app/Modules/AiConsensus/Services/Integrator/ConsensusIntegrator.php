<?php

namespace App\Modules\AiConsensus\Services\Integrator;

use App\Modules\AiConsensus\Models\AiRun;
use App\Modules\AiConsensus\Services\Prompt\PromptTemplates;
use App\Modules\AiConsensus\Services\Providers\OpenAIProvider;

class ConsensusIntegrator
{
    public function integrate(AiRun $run): array
    {
        $template = PromptTemplates::get($run->template_key);

        $responses = $run->responses()->where('status', 'done')->get()->keyBy('provider');

        $claude = $responses['anthropic']->raw_output ?? '';
        $gemini = $responses['gemini']->raw_output ?? '';

        $integratorPrompt = <<<TXT
Original prompt:
<<<PROMPT>>>
{$run->prompt}
<<<END>>>

Claude response:
<<<CLAUDE>>>
{$claude}
<<<END>>>

Gemini response:
<<<GEMINI>>>
{$gemini}
<<<END>>>

Integrator instructions:
{$template['integrator_instructions']}
TXT;

        $provider = new OpenAIProvider();

        return $provider->generate(
            $template['system'],
            $integratorPrompt,
            [
                'model' => config('aiconsensus.models.openai', 'gpt-5'),
                'timeout' => config('aiconsensus.timeouts.integrator_seconds', 120),
            ]
        );
    }
}