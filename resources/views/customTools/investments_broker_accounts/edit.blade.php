@extends('layouts.app')

@section('content')

    <div class="col-lg-12">    
        <div class="navbar navbar-light customPanel" style="padding: 0;">
            <div class="m-4">
                <form method="POST" action="{{ route('broker-accounts.update', $account->id) }}">
                    @csrf
                    @method('PUT')

                    <input type="text" name="name" value="{{ old('name', $account->name) }}">

                    <input type="text" name="broker" value="{{ old('broker', $account->broker) }}">

                    <input type="text" name="currency" value="{{ old('currency', $account->currency) }}">

                    <label>
                        <input type="checkbox" name="is_demo" value="1" @checked(old('is_demo', $account->is_demo))>
                        Demo
                    </label>

                    <button type="submit">Guardar</button>
                </form>

                @if(strtoupper($account->broker) === 'IBKR')
                    <hr>

                    <h4>IBKR – Ligação</h4>

                    <p>
                        <strong>Estado:</strong>
                        {{ $account->connection_status ?? '—' }}<br>

                        <strong>Última sincronização:</strong>
                        {{ $account->last_sync_at ? $account->last_sync_at->format('Y-m-d H:i') : '—' }}
                    </p>

                    @if($account->connection_error)
                        <div style="color:red; margin-bottom:10px;">
                            <strong>Erro:</strong> {{ $account->connection_error }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('broker-accounts.ibkr.test', $account->id) }}" style="margin-bottom:10px;">
                        @csrf
                        <button type="submit">Testar ligação IBKR</button>
                    </form>

                    <form method="POST" action="{{ route('broker-accounts.ibkr.sync', $account->id) }}" style="margin-bottom:10px;">
                        @csrf
                        <button type="submit">Sincronizar contas IBKR</button>
                    </form>

                    @php
                        $availableAccounts = data_get($account->settings, 'ibkr.available_accounts', []);
                    @endphp

                    @if(!empty($availableAccounts))
                        <form method="POST" action="{{ route('broker-accounts.ibkr.select', $account->id) }}">
                            @csrf

                            <label>Selecionar conta IBKR</label>
                            <select name="external_account_id" required>
                                <option value="">—</option>

                                @foreach($availableAccounts as $a)
                                    @php
                                        $id = $a['accountId']
                                            ?? $a['id']
                                            ?? $a['account']
                                            ?? null;
                                    @endphp

                                    @if($id)
                                        <option value="{{ $id }}"
                                            @selected($account->external_account_id === $id)>
                                            {{ $id }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>

                            <button type="submit">Associar conta</button>
                        </form>
                    @endif
                @endif


            </div>
        </div>
    </div>
@endsection
