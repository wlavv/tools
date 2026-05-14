@php
    $typeLabels = config('streamdeck-access.types', []);
    $taskLabels = config('streamdeck-access.task_labels', []);
    $emptyState = config('streamdeck-access.ui.empty_state', []);
@endphp

<div class="streamdeck-access-card sda-table-card">
    <div class="streamdeck-access-table-wrap">
        <table class="streamdeck-access-table streamdeck-access-table--lean lsg-datatable">
            <thead>
                <tr>
                    <th>Access point</th>
                    <th>Tipo</th>
                    <th>Destino / tarefa</th>
                    <th>Token</th>
                    <th>Utilização</th>
                    <th>Última execução</th>
                    <th class="text-center" style="width: 150px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($accessPoints as $accessPoint)
                    <tr>
                        <td>
                            <div class="sda-table-title">
                                <strong>{{ $accessPoint->name }}</strong>
                                <span>{{ $accessPoint->slug }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="sda-badge sda-badge--{{ $accessPoint->type }}">{{ $typeLabels[$accessPoint->type] ?? $accessPoint->type }}</span>
                            <span class="sda-status {{ $accessPoint->enabled ? 'sda-status--enabled' : 'sda-status--disabled' }}">
                                {{ $accessPoint->enabled ? 'Ativo' : 'Inativo' }}
                            </span>
                        </td>
                        <td class="sda-table-target">
                            @if($accessPoint->type === 'redirect')
                                <span title="{{ $accessPoint->target_url }}">{{ $accessPoint->target_url ?: '—' }}</span>
                            @else
                                <strong>{{ $taskLabels[$accessPoint->task_key] ?? $accessPoint->task_key }}</strong>
                                @if($accessPoint->queue)
                                    <small>queue: {{ $accessPoint->queue }}</small>
                                @endif
                            @endif
                        </td>
                        <td>
                            <span class="sda-token-hint">••••••{{ $accessPoint->token_hint ?: '—' }}</span>
                        </td>
                        <td>
                            <span>{{ $accessPoint->use_count }}</span>
                            @if($accessPoint->max_uses)
                                <small>/ {{ $accessPoint->max_uses }}</small>
                            @endif
                            @if($accessPoint->cooldown_seconds)
                                <small>{{ $accessPoint->cooldown_seconds }}s cooldown</small>
                            @endif
                        </td>
                        <td>{{ $accessPoint->last_used_at ? $accessPoint->last_used_at->format('d/m/Y H:i') : '—' }}</td>
                        <td>
                            <div class="streamdeck-access-actions streamdeck-access-actions--center">
                                <a href="{{ route('streamdeck_access.show', $accessPoint) }}" class="lsg-action-btn lsg-action-btn--primary lsg-action-btn--compact" title="Ver">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="{{ route('streamdeck_access.edit', $accessPoint) }}" class="lsg-action-btn lsg-action-btn--warning lsg-action-btn--compact" title="Editar">
                                    <i class="fa-solid fa-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('streamdeck_access.rotate-token', $accessPoint) }}" class="lsg-action-form" onsubmit="return confirm('Rodar token? O link atual deixará de funcionar.');">
                                    @csrf
                                    <button type="submit" class="lsg-action-btn lsg-action-btn--secondary lsg-action-btn--compact" title="Rodar token">
                                        <i class="fa-solid fa-arrows-rotate"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('streamdeck_access.destroy', $accessPoint) }}" class="lsg-action-form" onsubmit="return confirm('Remover este access point?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="lsg-action-btn lsg-action-btn--danger lsg-action-btn--compact" title="Apagar">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="sda-empty-state">
                                <strong>{{ $emptyState['title'] ?? 'Nenhum access point criado' }}</strong>
                                <span>{{ $emptyState['text'] ?? 'Cria o primeiro endpoint externo.' }}</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
