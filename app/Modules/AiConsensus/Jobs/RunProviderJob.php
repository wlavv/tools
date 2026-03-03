<?php

namespace App\Modules\AiConsensus\Jobs;

use App\Modules\AiConsensus\Models\AiRun;
use App\Modules\AiConsensus\Models\AiRunResponse;
use App\Modules\AiConsensus\Services\Prompt\PromptTemplates;
use App\Modules\AiConsensus\Services\Providers\AnthropicProvider;
use App\Modules\AiConsensus\Services\Providers\GeminiProvider;
use App\Modules\AiConsensus\Services\Providers\OpenAIProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunProviderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $runId,
        public string $providerName
    ) {}

    public function handle(): void
    {
        $run = AiRun::findOrFail($this->runId);
        $template = PromptTemplates::get($run->template_key);

        $resp = AiRunResponse::firstOrCreate(
            ['ai_run_id' => $run->id, 'provider' => $this->providerName],
            ['status' => 'queued']
        );

        $resp->update(['status' => 'running', 'error' => null]);

        try {
            $provider = match ($this->providerName) {
                'anthropic' => new AnthropicProvider(),
                'gemini' => new GeminiProvider(),
                'openai' => new OpenAIProvider(),
                default => throw new \RuntimeException("Unknown provider: {$this->providerName}"),
            };

            $draftPrompt = $template['draft_instructions'] . "\n\nUser request:\n" . $run->prompt;

            $result = $provider->generate(
                $template['system'],
                $draftPrompt,
                [
                    'model' => config("aiconsensus.models.{$this->providerName}"),
                    'timeout' => config('aiconsensus.timeouts.draft_seconds', 90),
                ]
            );

            $output = $result['output'] ?? '';
            $maxChars = (int) config('aiconsensus.limits.draft_max_output_chars', 30000);
            if (mb_strlen($output) > $maxChars) {
                $output = mb_substr($output, 0, $maxChars) . "\n\n[TRUNCATED]";
            }

            $resp->update([
                'status' => 'done',
                'model' => $result['model'] ?? null,
                'raw_output' => $output,
                'tokens_in' => $result['tokens_in'] ?? null,
                'tokens_out' => $result['tokens_out'] ?? null,
                'cost_estimate_usd' => $result['cost_usd'] ?? null,
                'latency_ms' => $result['meta']['latency_ms'] ?? null,
                'meta' => $result['meta'] ?? null,
            ]);
        } catch (\Throwable $e) {
            $resp->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);
        }

        $draftProviders = config('aiconsensus.draft_providers', ['anthropic','gemini']);

        $failed = AiRunResponse::where('ai_run_id', $run->id)
            ->whereIn('provider', $draftProviders)
            ->where('status', 'failed')
            ->count();

        if ($failed > 0) {
            $run->update(['status' => 'failed']);
            return;
        }

        $done = AiRunResponse::where('ai_run_id', $run->id)
            ->whereIn('provider', $draftProviders)
            ->where('status', 'done')
            ->count();

        if ($done === count($draftProviders)) {
            $run->update(['status' => 'integrating']);
            IntegrateRunJob::dispatch($run->id);
        } else {
            $run->update(['status' => 'running']);
        }
    }
}