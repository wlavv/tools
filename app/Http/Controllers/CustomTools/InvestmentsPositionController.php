<?php

namespace App\Http\Controllers\CustomTools;

use App\Models\webToolsManager\wt_investments_position;
use App\Models\webToolsManager\wt_investments_asset;
use App\Models\webToolsManager\wt_investments_brokerAccount;
use Illuminate\Http\Request;
use App\Services\Trading\StopEngineService;

class InvestmentsPositionController extends customToolsController
{
    public $breadcrumbs;
    
    public function index()
    {
        $data = [
            'positions'        => wt_investments_position::with(['asset', 'brokerAccount'])->orderByDesc('created_at')->paginate(20),
            'breadcrumbs'   => $this->breadcrumbs,
        ];

        $this->setViewData($data);
        return view('customTools/investments_positions/index')->with( $this->viewData );
    }

    public function create()
    {
        $data = [
            'assets' => wt_investments_asset::orderBy('symbol')->get(),
            'accounts' => wt_investments_brokerAccount::where('user_id', auth()->id())->get(),
            'breadcrumbs'   => $this->breadcrumbs,
        ];

        $this->setViewData($data);
        return view('customTools/investments_positions/create')->with( $this->viewData );

    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'broker_account_id'   => 'required|exists:broker_accounts,id',
            'asset_id'            => 'required|exists:assets,id',
            'side'                => 'required|in:long,short',
            'quantity'            => 'required|numeric|min:0.0001',
            'entry_price'         => 'required|numeric|min:0',
            'initial_stop_loss'   => 'required|numeric|min:0',
            'initial_stop_earn'   => 'required|numeric|min:0',
            'step_value'          => 'required|numeric|min:0',
            'auto_manage'         => 'nullable|boolean',
        ]);

        $data['current_stop_loss'] = $data['initial_stop_loss'];
        $data['current_stop_earn'] = $data['initial_stop_earn'];
        $data['status']            = 'open';
        $data['opened_at']         = now();
        $data['auto_manage']       = $request->boolean('auto_manage', true);

        $position = wt_investments_position::create($data);

        $position->stopLevels()->create([
            'step_index'   => 0,
            'stop_loss'    => $position->current_stop_loss,
            'stop_earn'    => $position->current_stop_earn,
            'activated_at' => now(),
        ]);

        return redirect()
            ->route('positions.show', $position)
            ->with('success', 'Posição criada com sucesso.');
    }

    public function show(wt_investments_position $position)
    {
        $position->load(['asset', 'brokerAccount', 'stopLevels', 'events']);
        return view('customTools.investments_positions.show', compact('position'));
    }

    public function destroy(wt_investments_position $position)
    {
        if ($position->isOpen()) {
            $position->status   = 'closed';
            $position->closed_at = now();
            $position->save();
        }

        return redirect()->route('positions.index')->with('success', 'Posição marcada como fechada.');
    }

    // Endpoint para testar manualmente o motor de stops
    public function simulateStep(Request $request, wt_investments_osition $position, StopEngineService $engine)
    {
        $data = $request->validate([
            'current_price' => 'required|numeric|min:0',
        ]);

        $engine->evaluate($position, $data['current_price']);

        return back()->with('success', 'Simulação executada.');
    }
}
