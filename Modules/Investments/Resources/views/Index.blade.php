@extends(config('investments.layout', 'layouts.app'))

@section('content')
    @include('investments::Includes.css')
    <div class="investments-shell">
        @include('investments::Includes._components.hero', [
            'title' => 'Investments',
            'subtitle' => 'Contas, ativos e posicoes com gestao de stops.',
        ])
        @include('investments::Includes._components.flash')

        <div class="investments-grid">
            <div class="investments-card investments-stat"><span>Accounts</span><strong>{{ $stats['accounts'] }}</strong></div>
            <div class="investments-card investments-stat"><span>Assets</span><strong>{{ $stats['assets'] }}</strong></div>
            <div class="investments-card investments-stat"><span>Open</span><strong>{{ $stats['open_positions'] }}</strong></div>
            <div class="investments-card investments-stat"><span>Closed</span><strong>{{ $stats['closed_positions'] }}</strong></div>
        </div>

        <div class="investments-card">
            <div class="investments-card__head">
                <h2 class="investments-card__title">Recent Positions</h2>
                <a href="{{ route('investments.positions.index') }}" class="lsg-action-btn lsg-action-btn--neutral lsg-action-btn--compact">
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
            @include('investments::positions._table', ['positions' => $positions])
        </div>
    </div>
    @include('investments::Includes.js')
@endsection
