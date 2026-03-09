<?php

namespace Modules\AIConsensus\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\AIConsensus\Http\Requests\StoreAIConsensusRequest;
use Modules\AIConsensus\Http\Requests\UpdateAIConsensusRequest;
use Modules\AIConsensus\Models\AIConsensus;
use Modules\AIConsensus\Services\AIConsensusService;

class AIConsensusController extends Controller
{
    public function __construct(
        protected AIConsensusService $service
    ) {
    }

    public function index(Request $request): View
    {
        $data = $this->service->getIndexData($request->all());

        return view('ai-consensus::Index', $data);
    }

    public function create(): View
    {
        return view('ai-consensus::pages.create', [
            'providerSettings' => $this->service->getProviderSettings(),
            'defaults' => [
                'template_key' => config('ai_consensus.defaults.template_key', 'module_scaffold_v1'),
            ],
        ]);
    }

    public function store(StoreAIConsensusRequest $request): RedirectResponse
    {
        $run = $this->service->createRun(
            $request->validated(),
            $request->file('files', []),
            optional($request->user())->id
        );

        return redirect()
            ->route('ai_consensus.show', $run->id)
            ->with('success', 'Pedido AI Consensus criado e processado.');
    }

    public function show(AIConsensus $run): View
    {
        $run->load(['responses', 'files']);

        return view('ai-consensus::pages.show', [
            'run' => $run,
            'providerSettings' => $this->service->getProviderSettings(),
            'parsedFilesPreview' => $this->service->buildStoredFilesPromptBlock($run),
        ]);
    }

    public function edit(AIConsensus $run): View
    {
        $run->load(['responses', 'files']);

        return view('ai-consensus::pages.edit', [
            'run' => $run,
            'providerSettings' => $this->service->getProviderSettings(),
        ]);
    }

    public function update(UpdateAIConsensusRequest $request, AIConsensus $run): RedirectResponse
    {
        $this->service->updateRun(
            $run,
            $request->validated(),
            $request->file('files', [])
        );

        return redirect()
            ->route('ai_consensus.show', $run->id)
            ->with('success', 'Pedido atualizado com sucesso.');
    }

    public function destroy(AIConsensus $run): RedirectResponse
    {
        $this->service->deleteRun($run);

        return redirect()
            ->route('ai_consensus.index')
            ->with('success', 'Pedido removido com sucesso.');
    }

    public function saveCredentials(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'provider' => ['required', 'in:anthropic,gemini,openai'],
            'label' => ['nullable', 'string', 'max:120'],
            'api_key' => ['nullable', 'string', 'max:500'],
            'base_url' => ['nullable', 'string', 'max:255'],
            'default_model' => ['nullable', 'string', 'max:120'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $this->service->saveProviderCredentials($data);

        return back()->with('success', 'Credenciais guardadas com sucesso.');
    }

    public function reprocess(AIConsensus $run): RedirectResponse
    {
        $this->service->reprocessRun($run);

        return redirect()
            ->route('ai_consensus.show', $run->id)
            ->with('success', 'Pedido reprocessado com sucesso.');
    }
}
