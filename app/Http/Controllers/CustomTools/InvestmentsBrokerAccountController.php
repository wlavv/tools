<?php

namespace App\Http\Controllers\CustomTools;

use App\Models\webToolsManager\wt_investments_brokerAccount;
use Illuminate\Http\Request;

use App\Services\Brokers\IbkrClient;

class InvestmentsBrokerAccountController extends customToolsController
{
    public $actions;
    public $breadcrumbs;

    public function __construct()
    {
        $this->middleware('auth');
        $this->actions[] = [];
    }

    public function index()
    {
        $data = [
            'breadcrumbs' => $this->breadcrumbs,
            'accounts' => wt_investments_brokerAccount::where('user_id', auth()->id())
                ->orderBy('name')
                ->paginate(20),
        ];

        $this->setViewData($data);
        return view('customTools/investments_broker_accounts/index')->with($this->viewData);
    }

    public function create()
    {
        $data = [
            'breadcrumbs' => $this->breadcrumbs,
        ];

        $this->setViewData($data);
        return view('customTools.investments_broker_accounts.create')->with($this->viewData);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'broker' => 'required|string|max:50',
            'currency' => 'required|string|max:10',
            'is_demo' => 'nullable|boolean',
        ]);

        $data['user_id'] = auth()->id();
        $data['is_demo'] = $request->boolean('is_demo', true);

        wt_investments_brokerAccount::create($data);

        return redirect()->route('broker-accounts.index')->with('success', 'Conta criada com sucesso.');
    }

    public function edit($id)
    {
        $account = wt_investments_brokerAccount::where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        $data = [
            'breadcrumbs' => $this->breadcrumbs,
            'account' => $account,
        ];

        $this->setViewData($data);
        return view('customTools.investments_broker_accounts.edit')->with($this->viewData);
    }

    public function update(Request $request, $id)
    {
        $account = wt_investments_brokerAccount::where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'broker' => 'required|string|max:50',
            'currency' => 'required|string|max:10',
            'is_demo' => 'nullable|boolean',
        ]);

        $data['is_demo'] = $request->boolean('is_demo', true);

        $account->update($data);

        return redirect()->route('broker-accounts.index')->with('success', 'Conta atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $account = wt_investments_brokerAccount::where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        $account->delete();

        return redirect()->route('broker-accounts.index')->with('success', 'Conta removida com sucesso.');
    }

    private function getAccountOrFail(int $id): wt_investments_brokerAccount{
        return wt_investments_brokerAccount::where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();
    }

    public function ibkrTest(int $id, IbkrClient $ibkr){
        $account = $this->getAccountOrFail($id);

        if (strtoupper($account->broker) !== 'IBKR') {
            return back()->with('error', 'Este registo não é do broker IBKR.');
        }

        try {
            $ibkr->withAccount($account)->authStatus();

            $account->update([
                'connection_status' => 'connected',
                'last_sync_at' => now(),
                'connection_error' => null,
            ]);

            return back()->with('success', 'Ligação IBKR OK.');
        } catch (\Throwable $e) {
            $account->update([
                'connection_status' => 'error',
                'connection_error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Falha ao ligar à IBKR (ver erro na conta).');
        }
    }

    public function ibkrSyncAccounts(int $id, IbkrClient $ibkr){
        $account = $this->getAccountOrFail($id);

        if (strtoupper($account->broker) !== 'IBKR') {
            return back()->with('error', 'Este registo não é do broker IBKR.');
        }

        try {
            $accounts = $ibkr->withAccount($account)->portfolioAccounts();

            $settings = $account->settings ?? [];
            $settings['ibkr'] = $settings['ibkr'] ?? [];
            $settings['ibkr']['available_accounts'] = $accounts;

            $account->update([
                'settings' => $settings,
                'connection_status' => 'connected',
                'last_sync_at' => now(),
                'connection_error' => null,
            ]);

            return back()->with('success', 'Contas IBKR sincronizadas. Selecione a conta para associar.');
        } catch (\Throwable $e) {
            $account->update([
                'connection_status' => 'error',
                'connection_error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Falha ao sincronizar contas IBKR.');
        }
    }

    public function ibkrSelectAccount(Request $request, int $id){
        $account = $this->getAccountOrFail($id);

        if (strtoupper($account->broker) !== 'IBKR') {
            return back()->with('error', 'Este registo não é do broker IBKR.');
        }

        $data = $request->validate([
            'external_account_id' => ['required', 'string', 'max:64'],
        ]);

        $account->update([
            'external_account_id' => $data['external_account_id'],
        ]);

        return back()->with('success', 'Conta IBKR associada com sucesso.');
    }

}
