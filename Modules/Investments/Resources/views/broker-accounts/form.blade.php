@extends(config('investments.layout', 'layouts.app'))

@section('content')
    @include('investments::Includes.css')
    <div class="investments-shell">
        @include('investments::Includes._components.hero', ['title' => $account->exists ? 'Edit Broker Account' : 'New Broker Account', 'subtitle' => 'Configuracao da conta e integracao IBKR.', 'icon' => 'fa-solid fa-building-columns'])
        @include('investments::Includes._components.flash')

        <form id="lsg-form" class="investments-card investments-form" method="POST" action="{{ $action }}">
            @csrf
            @if($method !== 'POST') @method($method) @endif
            <div class="investments-field"><label>Name</label><input name="name" value="{{ old('name', $account->name) }}" required></div>
            <div class="investments-field"><label>Broker</label><input name="broker" value="{{ old('broker', $account->broker ?? 'ibkr') }}" required></div>
            <div class="investments-field"><label>Currency</label><input name="currency" value="{{ old('currency', $account->currency ?? 'EUR') }}" required></div>
            <div class="investments-field"><label>External Account ID</label><input name="external_account_id" value="{{ old('external_account_id', $account->external_account_id) }}"></div>
            <div class="investments-field"><label>Balance</label><input type="number" step="0.01" name="balance" value="{{ old('balance', $account->balance ?? 0) }}"></div>
            <div class="investments-field" style="display:flex;align-items:end"><label class="investments-check"><input type="checkbox" name="is_demo" value="1" @checked(old('is_demo', $account->is_demo ?? true))> Demo account</label></div>
            <div class="investments-actions investments-form__full">
                <a href="{{ route('investments.broker_accounts.index') }}" class="lsg-action-btn lsg-action-btn--back"><i class="fa-solid fa-angle-left"></i><span>Back</span></a>
                <button class="lsg-action-btn lsg-action-btn--success"><i class="fa-solid fa-floppy-disk"></i><span>Save</span></button>
            </div>
        </form>

        @if($account->exists && strtoupper($account->broker) === 'IBKR')
            <div class="investments-card">
                <div class="investments-card__head"><h2 class="investments-card__title">IBKR Connection</h2></div>
                <div class="investments-detail-grid">
                    <div class="investments-kv"><span>Status</span><strong>{{ $account->connection_status ?: '-' }}</strong></div>
                    <div class="investments-kv"><span>Last sync</span><strong>{{ $account->last_sync_at ? $account->last_sync_at->format('Y-m-d H:i') : '-' }}</strong></div>
                </div>
                @if($account->connection_error)
                    <div class="investments-error" style="margin-top:12px">{{ $account->connection_error }}</div>
                @endif
                <div class="investments-actions" style="margin-top:14px;justify-content:flex-start">
                    <form method="POST" action="{{ route('investments.broker_accounts.ibkr.test', $account) }}">@csrf<button class="lsg-action-btn lsg-action-btn--neutral"><i class="fa-solid fa-plug"></i><span>Test</span></button></form>
                    <form method="POST" action="{{ route('investments.broker_accounts.ibkr.sync', $account) }}">@csrf<button class="lsg-action-btn lsg-action-btn--success"><i class="fa-solid fa-rotate"></i><span>Sync</span></button></form>
                </div>

                @php($availableAccounts = data_get($account->settings, 'ibkr.available_accounts', []))
                @if(!empty($availableAccounts))
                    <form class="investments-form" style="margin-top:14px" method="POST" action="{{ route('investments.broker_accounts.ibkr.select', $account) }}">
                        @csrf
                        <div class="investments-field">
                            <label>Select IBKR Account</label>
                            <select name="external_account_id" required>
                                <option value="">-</option>
                                @foreach($availableAccounts as $availableAccount)
                                    @php($id = $availableAccount['accountId'] ?? $availableAccount['id'] ?? $availableAccount['account'] ?? null)
                                    @if($id)
                                        <option value="{{ $id }}" @selected($account->external_account_id === $id)>{{ $id }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="investments-field" style="display:flex;align-items:end">
                            <button class="lsg-action-btn lsg-action-btn--success"><i class="fa-solid fa-link"></i><span>Associate</span></button>
                        </div>
                    </form>
                @endif
            </div>
        @endif
    </div>
    @include('investments::Includes.js')
@endsection
