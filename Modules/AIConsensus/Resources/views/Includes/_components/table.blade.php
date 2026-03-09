<div class="ai-card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Template</th>
                    <th>Estado</th>
                    <th>Tokens</th>
                    <th>Custo</th>
                    <th>Ficheiros</th>
                    <th>Respostas</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($runs as $run)
                    <tr>
                        <td>#{{ $run->id }}</td>
                        <td>{{ $run->title ?: 'Sem título' }}</td>
                        <td>{{ $run->template_key }}</td>
                        <td>{{ $run->status }}</td>
                        <td>{{ (int) $run->total_tokens_in }}/{{ (int) $run->total_tokens_out }}</td>
                        <td>${{ number_format((float) $run->total_cost_estimate_usd, 4) }}</td>
                        <td>{{ $run->files_count }}</td>
                        <td>{{ $run->responses_count }}</td>
                        <td class="text-end">
                            <div class="ai-actions justify-content-end">
                                <a href="{{ route('ai_consensus.show', $run->id) }}" class="btn btn-sm btn-outline-primary">Ver</a>
                                <a href="{{ route('ai_consensus.edit', $run->id) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                                <form method="POST" action="{{ route('ai_consensus.destroy', $run->id) }}" onsubmit="return confirm('Remover este run?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Apagar</button>
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
