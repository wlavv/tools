<?php

namespace Modules\Investments\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Investments\Models\BrokerAccount;
use Modules\Investments\Services\IbkrClient;

class BrokerAccountController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = $this->resolvePageTitle();
    }

    public function index(): View
    {
        return $this->view('investments::broker-accounts.index', [
            'accounts' => BrokerAccount::where('user_id', auth()->id())
                ->latest()
                ->paginate(config('investments.pagination', 25)),
        ]);
    }

    public function create(): View
    {
        return $this->view('investments::broker-accounts.form', [
            'account' => new BrokerAccount(['broker' => 'ibkr', 'currency' => 'EUR', 'is_demo' => true]),
            'action' => route('investments.broker_accounts.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        BrokerAccount::create([
            ...$this->validatedAccountData($request),
            'user_id' => $request->user()->id,
        ]);

        return redirect()->route('investments.broker_accounts.index')->with('success', 'Conta criada com sucesso.');
    }

    public function edit(BrokerAccount $brokerAccount): View
    {
        $this->authorizeAccount($brokerAccount);

        return $this->view('investments::broker-accounts.form', [
            'account' => $brokerAccount,
            'action' => route('investments.broker_accounts.update', $brokerAccount),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, BrokerAccount $brokerAccount): RedirectResponse
    {
        $this->authorizeAccount($brokerAccount);
        $brokerAccount->update($this->validatedAccountData($request));

        return redirect()->route('investments.broker_accounts.index')->with('success', 'Conta atualizada com sucesso.');
    }

    public function testIbkr(BrokerAccount $brokerAccount, IbkrClient $client): RedirectResponse
    {
        $this->authorizeAccount($brokerAccount);

        try {
            $status = $client->withAccount($brokerAccount)->authStatus();
            $brokerAccount->update([
                'connection_status' => data_get($status, 'authenticated') ? 'authenticated' : 'reachable',
                'connection_error' => null,
                'last_sync_at' => now(),
            ]);

            return back()->with('success', 'Ligacao IBKR testada com sucesso.');
        } catch (\Throwable $e) {
            $brokerAccount->update([
                'connection_status' => 'error',
                'connection_error' => $e->getMessage(),
                'last_sync_at' => now(),
            ]);

            return back()->withErrors(['ibkr' => $e->getMessage()]);
        }
    }

    public function syncIbkr(BrokerAccount $brokerAccount, IbkrClient $client): RedirectResponse
    {
        $this->authorizeAccount($brokerAccount);

        try {
            $accounts = $client->withAccount($brokerAccount)->portfolioAccounts();
            $settings = $brokerAccount->settings ?? [];
            data_set($settings, 'ibkr.available_accounts', $accounts);

            $brokerAccount->update([
                'settings' => $settings,
                'connection_status' => 'synced',
                'connection_error' => null,
                'last_sync_at' => now(),
            ]);

            return back()->with('success', 'Contas IBKR sincronizadas com sucesso.');
        } catch (\Throwable $e) {
            $brokerAccount->update([
                'connection_status' => 'error',
                'connection_error' => $e->getMessage(),
                'last_sync_at' => now(),
            ]);

            return back()->withErrors(['ibkr' => $e->getMessage()]);
        }
    }

    public function selectIbkrAccount(Request $request, BrokerAccount $brokerAccount): RedirectResponse
    {
        $this->authorizeAccount($brokerAccount);

        $data = $request->validate([
            'external_account_id' => ['required', 'string', 'max:120'],
        ]);

        $brokerAccount->update($data);

        return back()->with('success', 'Conta IBKR associada com sucesso.');
    }

    protected function validatedAccountData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'broker' => ['required', 'string', 'max:40'],
            'external_account_id' => ['nullable', 'string', 'max:120'],
            'currency' => ['required', 'string', 'max:10'],
            'is_demo' => ['nullable', 'boolean'],
            'balance' => ['nullable', 'numeric'],
        ]);

        $data['is_demo'] = $request->boolean('is_demo');
        $data['balance'] = $data['balance'] ?? 0;

        return $data;
    }

    protected function authorizeAccount(BrokerAccount $account): void
    {
        abort_unless((int) $account->user_id === (int) auth()->id(), 403);
    }
}
