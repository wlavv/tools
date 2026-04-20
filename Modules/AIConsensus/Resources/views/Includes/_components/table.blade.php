<div class="ai-card" style="margin-top: 15px;">
    <div class="table-responsive">
        <table class="table align-middle text-center" style="text-align: center !important">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Template</th>
                    <th>Estado</th>
                    <th>Tokens</th>
                    <th>Custo</th>
                    <th>Ficheiros</th>
                    <th>Respostas</th>
                    <th style="width: 180px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($runs as $run)
                    <tr>
                        <td>{{ $run->title ?: 'Sem título' }}</td>
                        <td>{{ $run->template_key }}</td>
                        <td>{{ $run->status }}</td>
                        <td>{{ (int) $run->total_tokens_in }}/{{ (int) $run->total_tokens_out }}</td>
                        <td>${{ number_format((float) $run->total_cost_estimate_usd, 4) }}</td>
                        <td>{{ $run->files_count }}</td>
                        <td>{{ $run->responses_count }}</td>
                        <td>
                            <div class="ai-table-actions">
                                <a href="{{ route('ai_consensus.show', $run->id) }}" class="lsg-action-btn lsg-action-btn--primary lsg-action-btn--compact">
                                    <span class="lsg-action-btn__icon"><i class="fa-solid fa-eye"></i></span>
                                </a>

                                <a href="{{ route('ai_consensus.edit', $run->id) }}" class="lsg-action-btn lsg-action-btn--warning lsg-action-btn--compact">
                                    <span class="lsg-action-btn__icon"><i class="fa-solid fa-pencil"></i></span>
                                </a>

                                <form method="POST" action="{{ route('ai_consensus.destroy', $run->id) }}" class="lsg-action-form" data-ai-loading-form onsubmit="return confirm('{{ __('ai-consensus::actions.confirm_delete') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="lsg-action-btn lsg-action-btn--danger lsg-action-btn--compact">
                                        <span class="lsg-action-btn__icon"><i class="fa-solid fa-trash"></i></span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center py-4">Sem pedidos registados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $runs->links() }}
    </div>
</div>
