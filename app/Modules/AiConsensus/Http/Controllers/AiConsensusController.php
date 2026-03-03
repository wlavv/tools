<?php

namespace App\Modules\AiConsensus\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AiConsensus\Http\Requests\CreateRunRequest;
use App\Modules\AiConsensus\Jobs\RunProviderJob;
use App\Modules\AiConsensus\Models\AiRun;
use App\Modules\AiConsensus\Models\AiRunResponse;
use App\Modules\AiConsensus\Services\Prompt\PromptTemplates;
use Illuminate\Support\Facades\DB;

class AiConsensusController extends Controller
{
    public function index()
    {
        $runs = AiRun::query()->orderByDesc('id')->paginate(20);
        return view('aiconsensus.index', compact('runs'));
    }

    public function create()
    {
        $templates = PromptTemplates::all();
        $draftProviders = config('aiconsensus.draft_providers', ['anthropic','gemini']);
        return view('aiconsensus.create', compact('templates','draftProviders'));
    }

    public function store(CreateRunRequest $request)
    {
        $run = AiRun::create([
            'created_by' => auth()->id(),
            'title' => $request->input('title'),
            'template_key' => $request->input('template_key', 'module_scaffold_v1'),
            'prompt' => $request->input('prompt'),
            'status' => 'running',
            'options' => [
                'draft_providers' => config('aiconsensus.draft_providers', ['anthropic','gemini']),
                'integrator_provider' => config('aiconsensus.integrator_provider', 'openai'),
            ],
        ]);

        $providers = config('aiconsensus.draft_providers', ['anthropic','gemini']);

        foreach ($providers as $p) {
            AiRunResponse::updateOrCreate(
                ['ai_run_id' => $run->id, 'provider' => $p],
                ['status' => 'queued']
            );

            RunProviderJob::dispatch($run->id, $p);
        }

        return redirect()->route('aiconsensus.show', $run->id);
    }

    public function show(int $id)
    {
        $run = AiRun::with('responses')->findOrFail($id);
        return view('aiconsensus.show', compact('run'));
    }

    public function status(int $id)
    {
        $run = AiRun::with('responses')->findOrFail($id);

        return response()->json([
            'status' => $run->status,
            'final_answer' => $run->final_answer,
            'responses' => $run->responses->map(fn($r) => [
                'provider' => $r->provider,
                'status' => $r->status,
                'error' => $r->error,
            ]),
        ]);
    }

    public function usage(){

        $days = (int) request('days', 30);
        $from = now()->subDays($days);

        $byProvider = DB::table('ai_run_responses')
            ->selectRaw('provider,
                COUNT(*) as requests,
                SUM(COALESCE(tokens_in,0)) as tokens_in,
                SUM(COALESCE(tokens_out,0)) as tokens_out,
                AVG(COALESCE(latency_ms,0)) as avg_latency_ms,
                SUM(CASE WHEN status="failed" THEN 1 ELSE 0 END) as failed_count
            ')
            ->where('created_at', '>=', $from)
            ->groupBy('provider')
            ->orderBy('provider')
            ->get();

        $runs = DB::table('ai_runs')
            ->selectRaw('
                COUNT(*) as total_runs,
                SUM(CASE WHEN status="done" THEN 1 ELSE 0 END) as done_runs,
                SUM(CASE WHEN status="failed" THEN 1 ELSE 0 END) as failed_runs
            ')
            ->where('created_at', '>=', $from)
            ->first();

        $providerStatus = [
            'openai' => [
                'key_set' => (bool) config('aiconsensus.keys.openai'),
                'model' => config('aiconsensus.models.openai'),
            ],
            'anthropic' => [
                'key_set' => (bool) config('aiconsensus.keys.anthropic'),
                'model' => config('aiconsensus.models.anthropic'),
            ],
            'gemini' => [
                'key_set' => (bool) config('aiconsensus.keys.gemini'),
                'model' => config('aiconsensus.models.gemini'),
            ],
        ];

        // last errors per provider (from stored ai_run_responses.error)
        $lastErrors = DB::table('ai_run_responses')
            ->select('provider','error','updated_at')
            ->whereNotNull('error')
            ->orderByDesc('updated_at')
            ->get()
            ->groupBy('provider')
            ->map(fn($g) => $g->first());

        return view('aiconsensus.usage', [
            'days' => $days,
            'byProvider' => $byProvider,
            'runs' => $runs,
            'providerStatus' => $providerStatus,
            'lastErrors' => $lastErrors,
        ]);
    }
    }