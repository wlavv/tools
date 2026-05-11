<div class="investments-table-wrap">
    <table class="investments-table {{ $positions instanceof \Illuminate\Pagination\AbstractPaginator ? 'lsg-datatable' : '' }}">
        <thead>
            <tr>
                <th>Asset</th><th>Account</th><th>Side</th><th>Qty</th><th>Entry</th><th>SL</th><th>SE</th><th>Status</th><th class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($positions as $position)
                <tr>
                    <td><strong>{{ $position->asset?->symbol }}</strong></td>
                    <td>{{ $position->brokerAccount?->name }}</td>
                    <td>{{ ucfirst($position->side) }}</td>
                    <td>{{ $position->quantity }}</td>
                    <td>{{ number_format((float) $position->entry_price, 4) }}</td>
                    <td>{{ number_format((float) $position->current_stop_loss, 4) }}</td>
                    <td>{{ number_format((float) $position->current_stop_earn, 4) }}</td>
                    <td><span class="investments-badge {{ $position->isOpen() ? 'investments-badge--success' : 'investments-badge--muted' }}">{{ $position->status }}</span></td>
                    <td class="text-center">
                        <div class="investments-actions">
                            <a href="{{ route('investments.positions.show', $position) }}" class="lsg-action-btn lsg-action-btn--neutral lsg-action-btn--compact"><i class="fa-solid fa-eye"></i></a>
                            @if($position->isOpen())
                                <form method="POST" action="{{ route('investments.positions.destroy', $position) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="lsg-action-btn lsg-action-btn--danger lsg-action-btn--compact" data-investments-confirm="Fechar posicao?"><i class="fa-solid fa-lock"></i></button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center investments-muted">Sem posicoes ainda.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
