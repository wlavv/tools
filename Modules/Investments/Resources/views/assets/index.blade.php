@extends(config('investments.layout', 'layouts.app'))

@section('content')
    @include('investments::Includes.css')
    <div class="investments-shell">
        @include('investments::Includes._components.hero', [
            'title' => 'Assets',
            'subtitle' => 'Instrumentos disponiveis para abertura de posicoes.',
            'icon' => 'fa-solid fa-layer-group',
        ])
        @include('investments::Includes._components.flash')

        <div class="investments-card">
            <div class="investments-card__head">
                <h2 class="investments-card__title">Assets</h2>
                <a href="{{ route('investments.assets.create') }}" class="lsg-action-btn lsg-action-btn--success lsg-action-btn--compact"><i class="fa-solid fa-plus"></i></a>
            </div>
            <div class="investments-table-wrap">
                <table class="investments-table lsg-datatable">
                    <thead><tr><th>Symbol</th><th>Name</th><th>Broker</th><th>Type</th><th>Exchange</th></tr></thead>
                    <tbody>
                        @forelse($assets as $asset)
                            <tr>
                                <td><strong>{{ $asset->symbol }}</strong></td>
                                <td>{{ $asset->name }}</td>
                                <td>{{ strtoupper($asset->broker) }}</td>
                                <td><span class="investments-badge">{{ $asset->type }}</span></td>
                                <td>{{ $asset->exchange ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center investments-muted">Sem ativos ainda.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $assets->links() }}
        </div>
    </div>
    @include('investments::Includes.js')
@endsection
