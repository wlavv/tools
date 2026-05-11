@extends(config('investments.layout', 'layouts.app'))

@section('content')
    @include('investments::Includes.css')
    <div class="investments-shell">
        @include('investments::Includes._components.hero', ['title' => 'Positions', 'subtitle' => 'Gestao de posicoes e stops.', 'icon' => 'fa-solid fa-arrow-trend-up'])
        @include('investments::Includes._components.flash')
        <div class="investments-card">
            <div class="investments-card__head">
                <h2 class="investments-card__title">Positions</h2>
                <a href="{{ route('investments.positions.create') }}" class="lsg-action-btn lsg-action-btn--success lsg-action-btn--compact"><i class="fa-solid fa-plus"></i></a>
            </div>
            @include('investments::positions._table', ['positions' => $positions])
            {{ $positions->links() }}
        </div>
    </div>
    @include('investments::Includes.js')
@endsection
