<?php

namespace App\Modules\AiConsensus\Jobs;

use App\Modules\AiConsensus\Models\AiRun;
use App\Modules\AiConsensus\Services\Integrator\ConsensusIntegrator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class IntegrateRunJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $runId) {}

    public function handle(): void
    {
        $run = AiRun::findOrFail($this->runId);

        try {
            $integrator = new ConsensusIntegrator();
            $result = $integrator->integrate($run);

            $final = $result['output'] ?? '';
            $maxChars = (int) config('aiconsensus.limits.integrator_max_output_chars', 45000);
            if (mb_strlen($final) > $maxChars) {
                $final = mb_substr($final, 0, $maxChars) . "\n\n[TRUNCATED]";
            }

            $run->update([
                'status' => 'done',
                'final_answer' => $final,
                'final_provider' => config('aiconsensus.integrator_provider', 'openai'),
                'final_model' => $result['model'] ?? null,
                'total_tokens_in' => $result['tokens_in'] ?? null,
                'total_tokens_out' => $result['tokens_out'] ?? null,
            ]);
        } catch (\Throwable $e) {
            $run->update([
                'status' => 'failed',
                'meta' => array_merge($run->meta ?? [], ['integrator_error' => $e->getMessage()]),
            ]);
        }
    }
}