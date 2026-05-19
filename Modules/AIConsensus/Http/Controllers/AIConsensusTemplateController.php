<?php

namespace Modules\AIConsensus\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\AIConsensus\Database\Seeders\AIConsensusCentralSeeder;
use Modules\AIConsensus\Http\Requests\StoreTemplateRequest;
use Modules\AIConsensus\Models\AIConsensusTemplate;

class AIConsensusTemplateController extends Controller
{
    public function index(Request $request): View
    {
        if (AIConsensusTemplate::query()->count() === 0) {
            app(AIConsensusCentralSeeder::class)->run();
        }

        $templates = AIConsensusTemplate::query()
            ->when($request->filled('module_scope'), fn ($query) => $query->where('module_scope', $request->string('module_scope')))
            ->orderBy('module_scope')
            ->orderBy('template_key')
            ->paginate(30)
            ->withQueryString();

        return view('ai-consensus::templates.index', compact('templates'));
    }

    public function edit(AIConsensusTemplate $template): View
    {
        return view('ai-consensus::templates.edit', [
            'template' => $template,
            'outputTypes' => config('ai-consensus-output-types', []),
        ]);
    }

    public function update(StoreTemplateRequest $request, AIConsensusTemplate $template): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        $template->update($data);

        return redirect()
            ->route('ai_consensus.templates.index')
            ->with('success', 'Template atualizado.');
    }
}
