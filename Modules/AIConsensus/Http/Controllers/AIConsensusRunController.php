<?php

namespace Modules\AIConsensus\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\AIConsensus\Database\Seeders\AIConsensusCentralSeeder;
use Modules\AIConsensus\Http\Requests\CreateRunRequest;
use Modules\AIConsensus\Models\AIConsensusRun;
use Modules\AIConsensus\Models\AIConsensusTemplate;
use Modules\AIConsensus\Services\AIConsensusGateway;
use Modules\AIConsensus\Services\AIConsensusRunService;

class AIConsensusRunController extends Controller
{
    public function index(Request $request): View
    {
        $runs = AIConsensusRun::query()
            ->with('template')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('source_module'), fn ($query) => $query->where('source_module', $request->string('source_module')))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('ai-consensus::runs.index', compact('runs'));
    }

    public function create(): View
    {
        if (AIConsensusTemplate::query()->count() === 0) {
            app(AIConsensusCentralSeeder::class)->run();
        }

        return view('ai-consensus::runs.create', [
            'templates' => AIConsensusTemplate::query()->where('is_active', true)->orderBy('template_key')->get(),
            'outputTypes' => config('ai-consensus-output-types', []),
        ]);
    }

    public function store(CreateRunRequest $request, AIConsensusGateway $gateway): RedirectResponse
    {
        $data = $request->validated();
        $data['requested_by'] ??= optional($request->user())->id;
        $data['options']['async'] = (bool) data_get($data, 'options.async', true);

        $run = $gateway->createRun($data);

        return redirect()
            ->route('ai_consensus.runs.show', $run)
            ->with('success', 'AI Consensus Run criado.');
    }

    public function show(AIConsensusRun $run): View
    {
        return view('ai-consensus::runs.show', [
            'run' => $run->load(['template', 'messages', 'providerResponses.provider', 'outputs', 'logs']),
        ]);
    }

    public function process(AIConsensusRun $run, AIConsensusRunService $runService): RedirectResponse
    {
        $runService->process($run);

        return redirect()
            ->route('ai_consensus.runs.show', $run)
            ->with('success', 'AI Consensus Run processado.');
    }

    public function download(AIConsensusRun $run): Response
    {
        $run->loadMissing(['template', 'outputs']);
        $output = $run->outputs->last();
        $format = $output?->format ?: data_get($run->options, 'return_format', 'json');
        $extension = $format === 'markdown' ? 'md' : ($format === 'text' ? 'txt' : 'json');
        $content = $output?->content ?: $run->final_output ?: json_encode([
            'run_id' => $run->id,
            'status' => $run->status,
            'error' => $run->error_message,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

        $filename = sprintf(
            'ai-consensus-run-%s-%s.%s',
            $run->id,
            str($run->output_type ?: 'output')->slug(),
            $extension
        );

        return response($content, 200, [
            'Content-Type' => $extension === 'json' ? 'application/json; charset=UTF-8' : 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
