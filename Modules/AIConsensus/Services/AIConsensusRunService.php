<?php

namespace Modules\AIConsensus\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\AIConsensus\Models\AIConsensusContext;
use Modules\AIConsensus\Models\AIConsensusLog;
use Modules\AIConsensus\Models\AIConsensusOutput;
use Modules\AIConsensus\Models\AIConsensusRun;
use Modules\AIConsensus\Models\AIConsensusTemplate;

class AIConsensusRunService
{
    public function __construct(
        protected AIConsensusContextBuilder $contextBuilder,
        protected AIConsensusProviderOrchestrator $providerOrchestrator,
        protected AIConsensusEngine $engine,
        protected AIConsensusOutputNormalizer $outputNormalizer,
    ) {
    }

    public function create(array $payload, ?AIConsensusTemplate $template = null): AIConsensusRun
    {
        return DB::transaction(function () use ($payload, $template) {
            $options = array_merge($template?->default_options ?? [], $payload['options'] ?? []);
            $context = $this->contextBuilder->build($payload);

            $run = AIConsensusRun::query()->create([
                'uuid' => (string) Str::uuid(),
                'source_module' => $payload['source_module'],
                'source_type' => $payload['source_type'],
                'source_id' => isset($payload['source_id']) ? (string) $payload['source_id'] : null,
                'template_id' => $template?->id,
                'output_type' => $payload['output_type'] ?? $template?->default_output_type ?? 'structured_report',
                'status' => 'pending',
                'title' => $payload['title'] ?? $template?->name,
                'input_payload' => $payload['input_payload'],
                'context_payload' => $context,
                'options' => $options,
                'requested_by' => $payload['requested_by'] ?? null,
            ]);

            $run->messages()->create([
                'role' => 'system',
                'message' => $template?->system_prompt ?: 'AI Consensus central run.',
                'payload' => ['template_key' => $template?->template_key],
                'created_by' => $payload['requested_by'] ?? null,
            ]);

            $run->messages()->create([
                'role' => 'user',
                'message' => (string) ($payload['message'] ?? 'Initial AI Consensus request.'),
                'payload' => $payload['input_payload'],
                'created_by' => $payload['requested_by'] ?? null,
            ]);

            AIConsensusContext::query()->create([
                'run_id' => $run->id,
                'context_key' => 'initial',
                'payload' => $context,
            ]);

            $this->log($run, 'info', 'run.created', 'AI Consensus run created.', ['source' => $payload['source_module']]);

            return $run;
        });
    }

    public function process(AIConsensusRun|int $run): AIConsensusRun
    {
        $run = $run instanceof AIConsensusRun ? $run : AIConsensusRun::query()->findOrFail($run);

        try {
            $run->update(['status' => 'processing', 'started_at' => now(), 'error_message' => null]);
            $this->log($run, 'info', 'run.processing', 'Processing started.');

            $responses = $this->providerOrchestrator->execute($run->fresh('template'));
            $content = $this->engine->build($run->fresh('template'), $responses);
            $normalized = $this->outputNormalizer->normalize($run->fresh('template'), $content);

            AIConsensusOutput::query()->create(array_merge($normalized, [
                'run_id' => $run->id,
                'output_type' => $run->output_type,
            ]));

            $run->update([
                'status' => 'completed',
                'final_output' => $content,
                'final_score' => collect($responses)->avg('score'),
                'finished_at' => now(),
            ]);

            $this->log($run, 'info', 'run.completed', 'Processing completed.');

            return $run->fresh(['template', 'outputs', 'providerResponses']);
        } catch (\Throwable $e) {
            $run->update([
                'status' => 'failed',
                'failed_at' => now(),
                'error_message' => $e->getMessage(),
            ]);
            $this->log($run, 'error', 'run.failed', $e->getMessage());

            throw $e;
        }
    }

    public function log(?AIConsensusRun $run, string $level, string $event, ?string $message = null, array $context = []): void
    {
        AIConsensusLog::query()->create([
            'run_id' => $run?->id,
            'level' => $level,
            'event' => $event,
            'message' => $message,
            'context' => $context,
            'created_at' => now(),
        ]);
    }
}
