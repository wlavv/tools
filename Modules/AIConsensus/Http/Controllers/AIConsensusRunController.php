<?php

namespace Modules\AIConsensus\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\AIConsensus\Http\Requests\CreateRunRequest;
use Modules\AIConsensus\Models\AIConsensusRun;
use Modules\AIConsensus\Models\AIConsensusTemplate;
use Modules\AIConsensus\Services\AIConsensusGateway;

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
}
