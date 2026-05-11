<?php

namespace Modules\Investments\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Investments\Models\Asset;
use Modules\Investments\Models\BrokerAccount;
use Modules\Investments\Models\Position;
use Modules\Investments\Services\InvestmentPositionService;

class PositionController extends Controller
{
    public function __construct(protected InvestmentPositionService $positions)
    {
        parent::__construct();
        $this->pageTitle = $this->resolvePageTitle();
    }

    public function index(): View
    {
        return $this->view('investments::positions.index', [
            'positions' => Position::with(['asset', 'brokerAccount'])
                ->latest()
                ->paginate(config('investments.pagination', 25)),
        ]);
    }

    public function create(): View
    {
        return $this->view('investments::positions.form', [
            'position' => new Position(['side' => 'long', 'auto_manage' => true]),
            'accounts' => BrokerAccount::where('user_id', auth()->id())->orderBy('name')->get(),
            'assets' => Asset::orderBy('symbol')->get(),
            'action' => route('investments.positions.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'broker_account_id' => ['required', 'exists:wt_investments_broker_accounts,id'],
            'asset_id' => ['required', 'exists:wt_investments_assets,id'],
            'side' => ['required', 'in:long,short'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'entry_price' => ['required', 'numeric', 'gt:0'],
            'initial_stop_loss' => ['required', 'numeric', 'gt:0'],
            'initial_stop_earn' => ['required', 'numeric', 'gt:0'],
            'step_value' => ['required', 'numeric', 'gt:0'],
            'auto_manage' => ['nullable', 'boolean'],
        ]);

        $account = BrokerAccount::findOrFail($data['broker_account_id']);
        abort_unless((int) $account->user_id === (int) auth()->id(), 403);

        $data['auto_manage'] = $request->boolean('auto_manage');
        $position = $this->positions->create($data);

        return redirect()->route('investments.positions.show', $position)->with('success', 'Posicao criada com sucesso.');
    }

    public function show(Position $position): View
    {
        $position->load(['asset', 'brokerAccount', 'stopLevels', 'events']);

        return $this->view('investments::positions.show', [
            'position' => $position,
        ]);
    }

    public function destroy(Position $position): RedirectResponse
    {
        $this->positions->close($position);

        return redirect()->route('investments.positions.index')->with('success', 'Posicao fechada com sucesso.');
    }

    public function simulateStep(Request $request, Position $position): RedirectResponse
    {
        $data = $request->validate([
            'current_price' => ['required', 'numeric', 'gt:0'],
        ]);

        $this->positions->simulatePrice($position, (float) $data['current_price']);

        return back()->with('success', 'Movimento simulado com sucesso.');
    }
}
