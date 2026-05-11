@extends(config('investments.layout', 'layouts.app'))

@section('content')
    @include('investments::Includes.css')
    <div class="investments-shell">
        @include('investments::Includes._components.hero', ['title' => 'Broker Accounts', 'subtitle' => 'Contas ligadas a corretoras.', 'icon' => 'fa-solid fa-building-columns'])
        @include('investments::Includes._components.flash')

        <div class="investments-card">
            <div class="investments-card__head">
                <h2 class="investments-card__title">Accounts</h2>
                <a href="{{ route('investments.broker_accounts.create') }}" class="lsg-action-btn lsg-action-btn--success lsg-action-btn--compact"><i class="fa-solid fa-plus"></i></a>
            </div>
            <div class="investments-table-wrap">
                <table class="investments-table lsg-datatable">
                    <thead><tr><th>Name</th><th>Broker</th><th>Currency</th><th>Type</th><th>Status</th><th class="text-center">Actions</th></tr></thead>
                    <tbody>
                        @forelse($accounts as $account)
                            <tr>
                                <td><strong>{{ $account->name }}</strong></td>
                                <td>{{ strtoupper($account->broker) }}</td>
                                <td>{{ $account->currency }}</td>
                                <td><span class="investments-badge {{ $account->is_demo ? '' : 'investments-badge--success' }}">{{ $account->is_demo ? 'Demo' : 'Live' }}</span></td>
                                <td>{{ $account->connection_status ?: '-' }}</td>
                                <td class="text-center">
                                    <a href="{{ route('investments.broker_accounts.edit', $account) }}" class="lsg-action-btn lsg-action-btn--warning lsg-action-btn--compact"><i class="fa-solid fa-pencil"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center investments-muted">Sem contas ainda.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $accounts->links() }}
        </div>
    </div>
    @include('investments::Includes.js')
@endsection
