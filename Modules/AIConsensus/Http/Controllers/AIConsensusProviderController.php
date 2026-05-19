<?php

namespace Modules\AIConsensus\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\AIConsensus\Models\AIConsensusProvider;

class AIConsensusProviderController extends Controller
{
    public function index(): View
    {
        return view('ai-consensus::providers.index', [
            'providers' => AIConsensusProvider::query()->orderBy('priority')->get(),
        ]);
    }

    public function update(Request $request, AIConsensusProvider $provider): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'model' => ['nullable', 'string', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
            'priority' => ['required', 'integer', 'min:1', 'max:999'],
            'weight' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $provider->update($data);

        return back()->with('success', 'Provider atualizado.');
    }
}
