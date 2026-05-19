<?php

namespace Modules\IdeaLab\Services;

use Modules\IdeaLab\Models\Idea;
use Modules\IdeaLab\Models\IdeaProjectConversion;

class IdeaConversionService
{
    public function buildProjectPayload(Idea $idea): array
    {
        $idea->loadMissing(['category', 'tags']);

        $latestAiRun = $idea->aiRuns()
            ->where(function ($query) {
                $query->whereNotNull('summary')
                    ->orWhereNotNull('response_payload')
                    ->orWhereNotNull('response_text');
            })
            ->first();

        $centralConsensus = $this->resolveCentralConsensusResult($latestAiRun?->response_payload ?? []);
        $latestAiSummary = $latestAiRun?->summary ?: $latestAiRun?->response_text ?: $centralConsensus['final_answer'];
        $latestAiPayload = $centralConsensus['output'] ?? $latestAiRun?->response_payload ?? [];

        return [
            'source' => 'idealab',
            'source_id' => $idea->id,
            'name' => $idea->title,
            'description' => $idea->description_refined ?: $idea->description_raw,
            'brief' => $latestAiSummary,
            'category' => $idea->category?->name,
            'priority' => $idea->priority,
            'status' => 'proposed',
            'scores' => [
                'opportunity' => $idea->opportunity_score,
                'effort' => $idea->effort_score,
                'risk' => $idea->risk_score,
                'strategic' => $idea->strategic_score,
                'reusability' => $idea->reusability_score,
                'monetization' => $idea->monetization_score,
                'final' => $idea->final_score,
            ],
            'ai' => [
                'run_id' => $latestAiRun?->id,
                'run_type' => $latestAiRun?->run_type,
                'summary' => $latestAiSummary,
                'payload' => $latestAiPayload,
                'central_consensus' => $centralConsensus,
                'scores' => $latestAiRun?->scores ?? [],
            ],
            'structure' => $this->extractStructure($latestAiPayload, $latestAiSummary),
            'tags' => $idea->tags()->pluck('name')->values()->all(),
            'created_from_idea_at' => now()->toIso8601String(),
        ];
    }

    public function convert(Idea $idea): IdeaProjectConversion
    {
        $payload = $this->buildProjectPayload($idea);
        $serviceClass = config('idealab.project_manager.service_class');
        $projectId = null;
        $status = 'payload_created';

        if ($serviceClass && class_exists($serviceClass)) {
            $project = app($serviceClass)->createFromIdeaPayload($payload);
            $projectId = is_object($project) ? ($project->id ?? null) : ($project['id'] ?? null);
            $status = $projectId ? 'converted' : 'payload_created';
        }

        $conversion = IdeaProjectConversion::query()->create([
            'idea_id' => $idea->id,
            'project_id' => $projectId,
            'status' => $status,
            'conversion_payload' => $payload,
            'converted_by' => auth()->id(),
        ]);

        $idea->update([
            'status' => $projectId ? 'converted' : 'candidate_project',
            'converted_project_id' => $projectId,
            'converted_at' => $projectId ? now() : null,
        ]);

        return $conversion;
    }

    protected function extractStructure(array|string|null $payload, ?string $summary): array
    {
        $data = is_array($payload) ? $payload : [];

        if (empty($data) && is_string($summary)) {
            $decoded = json_decode($summary, true);
            if (is_array($decoded)) {
                $data = $decoded;
            }
        }

        return [
            'executive_summary' => data_get($data, 'executive_summary') ?: data_get($data, 'summary') ?: $summary,
            'problem' => data_get($data, 'problem'),
            'target_users' => data_get($data, 'target_users', []),
            'value_proposition' => data_get($data, 'value_proposition'),
            'concepts' => data_get($data, 'concepts', data_get($data, 'features', [])),
            'mvp' => data_get($data, 'mvp', []),
            'milestones' => data_get($data, 'milestones', data_get($data, 'roadmap', [])),
            'tasks' => data_get($data, 'tasks', []),
            'technical_requirements' => data_get($data, 'technical_requirements', []),
            'risks' => data_get($data, 'risks', []),
            'dependencies' => data_get($data, 'dependencies', []),
            'monetization' => data_get($data, 'monetization', []),
            'complexity' => data_get($data, 'complexity', data_get($data, 'scores.complexity')),
            'recommendation' => data_get($data, 'recommendation'),
        ];
    }

    protected function resolveCentralConsensusResult(array $payload): array
    {
        $runId = data_get($payload, 'ai_consensus_run_id');
        if (!$runId) {
            return ['run_id' => $runId, 'final_answer' => null, 'status' => null];
        }

        if (class_exists(\Modules\AIConsensus\Models\AIConsensusRun::class)) {
            $run = \Modules\AIConsensus\Models\AIConsensusRun::query()
                ->with('outputs')
                ->find($runId);

            return [
                'run_id' => $runId,
                'status' => $run?->status,
                'final_answer' => $run?->final_output,
                'output' => $run?->outputs?->last()?->json_payload,
            ];
        }

        if (!class_exists(\Modules\AIConsensus\Models\AIConsensus::class)) {
            return ['run_id' => $runId, 'final_answer' => null, 'status' => null];
        }

        $run = \Modules\AIConsensus\Models\AIConsensus::query()->find($runId);

        return [
            'run_id' => $runId,
            'status' => $run?->status,
            'final_answer' => $run?->final_answer,
            'final_model' => $run?->final_model,
        ];
    }
}
