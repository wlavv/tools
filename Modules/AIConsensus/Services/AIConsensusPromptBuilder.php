<?php

namespace Modules\AIConsensus\Services;

use Modules\AIConsensus\Models\AIConsensusRun;

class AIConsensusPromptBuilder
{
    public function build(AIConsensusRun $run): string
    {
        $template = $run->template;
        $inputJson = json_encode($run->input_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        $contextJson = json_encode($run->context_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        $optionsJson = json_encode($run->options, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

        $user = $template?->user_prompt_template ?: '{{input_payload}}';
        $user = str_replace('{{input_payload}}', $inputJson ?: '{}', $user);
        foreach (($run->input_payload ?? []) as $key => $value) {
            if (is_scalar($value) || $value === null) {
                $user = str_replace('{{' . $key . '}}', (string) $value, $user);
            }
        }

        return implode("\n\n---\n\n", array_filter([
            $template?->system_prompt ? "SYSTEM:\n" . $template->system_prompt : null,
            "USER REQUEST:\n" . $user,
            "CONTEXT:\n" . ($contextJson ?: '{}'),
            "OPTIONS:\n" . ($optionsJson ?: '{}'),
            "OUTPUT TYPE: " . $run->output_type,
            "Return only the requested format when return_format is json. Do not execute actions; provide reviewable output.",
        ]));
    }
}
