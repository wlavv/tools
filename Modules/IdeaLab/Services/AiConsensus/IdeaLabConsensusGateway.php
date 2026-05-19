<?php

namespace Modules\IdeaLab\Services\AiConsensus;

use Illuminate\Support\Arr;
use Modules\IdeaLab\Models\Idea;
use Modules\IdeaLab\Models\IdeaAiConsensusRun;
use Modules\IdeaLab\Models\IdeaAiRun;
use Modules\IdeaLab\Models\IdeaAiTemplate;

class IdeaLabConsensusGateway
{
    public function buildPayload(Idea $idea, ?IdeaAiTemplate $template = null, ?string $message = null, string $mode = 'template'): array
    {
        $template ??= IdeaAiTemplate::query()
            ->where('key', config('idealab.ai_consensus.default_template_key'))
            ->first();

        return [
            'entrypoint' => 'idealab',
            'entrypoint_type' => $template?->entrypoint_type ?? config('idealab.ai_consensus.entrypoint_type'),
            'mode' => $mode,
            'template' => $template ? [
                'id' => $template->id,
                'key' => $template->key,
                'name' => $template->name,
                'system_prompt' => $template->system_prompt,
                'user_prompt_template' => $template->user_prompt_template,
                'expected_schema' => $template->expected_schema,
            ] : null,
            'input' => [
                'idea_id' => $idea->id,
                'title' => $idea->title,
                'description_raw' => $idea->description_raw,
                'description_refined' => $idea->description_refined,
                'status' => $idea->status,
                'priority' => $idea->priority,
                'scores' => [
                    'opportunity' => $idea->opportunity_score,
                    'effort' => $idea->effort_score,
                    'risk' => $idea->risk_score,
                    'strategic' => $idea->strategic_score,
                    'reusability' => $idea->reusability_score,
                    'monetization' => $idea->monetization_score,
                    'final' => $idea->final_score,
                ],
                'user_message' => $message,
            ],
            'history' => $idea->aiMessages()->oldest()->limit(30)->get(['role', 'content', 'created_at'])->toArray(),
        ];
    }

    public function createRun(Idea $idea, ?IdeaAiTemplate $template = null, ?string $message = null, string $mode = 'template'): IdeaAiRun
    {
        $payload = $this->buildPayload($idea, $template, $message, $mode);
        $promptText = $this->renderPromptText($payload);

        $run = IdeaAiRun::query()->create([
            'idea_id' => $idea->id,
            'template_id' => $template?->id,
            'run_type' => $template?->key ?? 'chat',
            'status' => 'payload_created',
            'prompt_payload' => $payload,
            'prompt_text' => $promptText,
            'requested_by' => auth()->id(),
            'started_at' => now(),
            'finished_at' => now(),
        ]);

        $serviceClass = config('idealab.ai_consensus.service_class');
        if (config('idealab.ai_consensus.enabled') && $serviceClass && class_exists($serviceClass)) {
            try {
                $purpose = $this->purposeForTemplate($template);
                $centralRun = app($serviceClass)->createRun([
                    'source_module' => 'IdeaLab',
                    'source_type' => 'project_idea',
                    'source_id' => $idea->id,
                    'template_key' => $this->centralTemplateKey($template),
                    'output_type' => $this->outputTypeForPurpose($purpose),
                    'title' => 'IdeaLab: ' . $idea->title,
                    'input_payload' => [
                        'idea' => $payload['input'],
                        'history' => $payload['history'],
                        'prompt_text' => $promptText,
                        'business_context' => 'WebTools Manager / B.O. Custom LSG',
                        'technical_context' => [
                            'framework' => 'Laravel',
                            'module_model' => 'LSG Module',
                            'ui' => 'Blade + DataTables + SweetAlerts',
                        ],
                    ],
                    'options' => [
                        'language' => 'pt',
                        'tone' => 'technical',
                        'consensus_mode' => $purpose === 'module_blueprint' ? 'architect_reviewer' : 'single_provider',
                        'return_format' => 'json',
                        'store_result' => true,
                        'allow_code_generation' => false,
                        'async' => true,
                    ],
                    'requested_by' => auth()->id(),
                ]);

                IdeaAiConsensusRun::query()->firstOrCreate([
                    'idea_id' => $idea->id,
                    'ai_consensus_run_id' => $centralRun->id,
                ], [
                    'purpose' => $purpose,
                ]);

                $run->update([
                    'status' => 'queued',
                    'response_payload' => [
                        'ai_consensus_run_id' => $centralRun->id,
                        'ai_consensus_status' => $centralRun->status,
                        'ai_consensus_route' => route('ai_consensus.runs.show', $centralRun),
                        'purpose' => $purpose,
                    ],
                    'finished_at' => null,
                ]);
            } catch (\Throwable $e) {
                $run->update([
                    'status' => 'payload_created',
                    'error_message' => $e->getMessage(),
                ]);
            }
        }

        return $run->fresh();
    }

    protected function centralTemplateKey(?IdeaAiTemplate $template): string
    {
        return match ($template?->key) {
            'idea_deconstruction' => 'idealab.project_idea_discovery',
            'project_conversion_brief' => 'idealab.project_idea_to_project',
            default => $template?->key ?: 'idealab.project_idea_discovery',
        };
    }

    protected function purposeForTemplate(?IdeaAiTemplate $template): string
    {
        return match ($template?->entrypoint_type) {
            'project_conversion' => 'project_conversion',
            default => match ($template?->key) {
                'project_conversion_brief' => 'project_conversion',
                default => 'discovery',
            },
        };
    }

    protected function outputTypeForPurpose(string $purpose): string
    {
        return match ($purpose) {
            'mvp' => 'mvp_definition',
            'module_blueprint' => 'lsg_module_blueprint',
            'project_conversion' => 'task_breakdown',
            'project_brief' => 'project_brief',
            default => 'project_brief',
        };
    }

    public function renderPromptText(array $payload): string
    {
        $template = Arr::get($payload, 'template.user_prompt_template', '');
        $input = Arr::get($payload, 'input', []);

        foreach ($input as $key => $value) {
            if (is_scalar($value) || is_null($value)) {
                $template = str_replace('{{' . $key . '}}', (string) $value, $template);
            }
        }

        $system = Arr::get($payload, 'template.system_prompt');
        $schema = Arr::get($payload, 'template.expected_schema');
        $history = Arr::get($payload, 'history', []);

        $blocks = array_filter([
            $system ? "SYSTEM CONTEXT:\n" . $system : null,
            "IDEA INPUT:\n" . $template,
            $history ? "PREVIOUS DISCUSSION:\n" . json_encode($history, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) : null,
            $schema ? "EXPECTED STRUCTURED OUTPUT:\n" . json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) : null,
            "IMPORTANT:\nReturn a practical project decomposition. Include milestones, task list, execution priorities, dependencies, risk level, MVP scope, complexity notes and recommendation about whether this is ready to become a Project Manager project.",
        ]);

        return implode("\n\n---\n\n", $blocks);
    }
}
