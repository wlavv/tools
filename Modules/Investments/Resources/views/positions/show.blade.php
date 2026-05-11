@extends(config('investments.layout', 'layouts.app'))

@section('content')
    @include('investments::Includes.css')
    <div class="investments-shell">
        @include('investments::Includes._components.hero', ['title' => ($position->asset?->symbol ?? 'Position') . ' #' . $position->id, 'subtitle' => 'Detalhe da posicao, patamares e eventos.', 'icon' => 'fa-solid fa-arrow-trend-up'])
        @include('investments::Includes._components.flash')

        <div class="investments-detail-grid">
            <div class="investments-card">
                <h2 class="investments-card__title">Position</h2>
                <div class="investments-kv"><span>Account</span><strong>{{ $position->brokerAccount?->name }}</strong></div>
                <div class="investments-kv"><span>Side</span><strong>{{ ucfirst($position->side) }}</strong></div>
                <div class="investments-kv"><span>Quantity</span><strong>{{ $position->quantity }}</strong></div>
                <div class="investments-kv"><span>Entry</span><strong>{{ number_format((float) $position->entry_price, 4) }}</strong></div>
                <div class="investments-kv"><span>Status</span><strong>{{ $position->status }}</strong></div>
            </div>
            <div class="investments-card">
                <h2 class="investments-card__title">Stops</h2>
                <div class="investments-kv"><span>Stop Loss</span><strong>{{ number_format((float) $position->current_stop_loss, 4) }}</strong></div>
                <div class="investments-kv"><span>Stop Earn</span><strong>{{ number_format((float) $position->current_stop_earn, 4) }}</strong></div>
                <div class="investments-kv"><span>Step</span><strong>{{ number_format((float) $position->step_value, 4) }}</strong></div>
                <div class="investments-kv"><span>Auto</span><strong>{{ $position->auto_manage ? 'Yes' : 'No' }}</strong></div>
                @if($position->pnl !== null)<div class="investments-kv"><span>PnL</span><strong>{{ number_format((float) $position->pnl, 2) }}</strong></div>@endif
            </div>
        </div>

        @if($position->isOpen())
            <form class="investments-card investments-form" method="POST" action="{{ route('investments.positions.simulate_step', $position) }}">
                @csrf
                <div class="investments-field"><label>Current price simulation</label><input type="number" step="0.0001" name="current_price" required></div>
                <div class="investments-field" style="display:flex;align-items:end"><button class="lsg-action-btn lsg-action-btn--success"><i class="fa-solid fa-play"></i><span>Simulate</span></button></div>
            </form>
        @endif

        <div class="investments-card">
            <h2 class="investments-card__title">Stop Levels</h2>
            <div class="investments-table-wrap"><table class="investments-table"><thead><tr><th>Step</th><th>SL</th><th>SE</th><th>Activated</th></tr></thead><tbody>
                @forelse($position->stopLevels as $level)
                    <tr><td>{{ $level->step_index }}</td><td>{{ number_format((float) $level->stop_loss, 4) }}</td><td>{{ number_format((float) $level->stop_earn, 4) }}</td><td>{{ $level->activated_at }}</td></tr>
                @empty
                    <tr><td colspan="4" class="text-center investments-muted">Sem patamares.</td></tr>
                @endforelse
            </tbody></table></div>
        </div>

        <div class="investments-card">
            <h2 class="investments-card__title">Events</h2>
            <div class="investments-table-wrap"><table class="investments-table"><thead><tr><th>Date</th><th>Type</th><th>Price</th><th>Data</th></tr></thead><tbody>
                @forelse($position->events as $event)
                    <tr><td>{{ $event->event_time }}</td><td>{{ $event->type }}</td><td>{{ $event->price ? number_format((float) $event->price, 4) : '-' }}</td><td><pre class="mb-0">{{ json_encode($event->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></td></tr>
                @empty
                    <tr><td colspan="4" class="text-center investments-muted">Sem eventos.</td></tr>
                @endforelse
            </tbody></table></div>
        </div>
    </div>
    @include('investments::Includes.js')
@endsection
