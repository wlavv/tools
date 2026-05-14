@php
    $entries = $entries ?? [];
    $title = $title ?? 'Configurações';
    $emptyText = $emptyText ?? config('environment-manager.ui.empty_state.text', 'Sem registos.');
@endphp

<div class="environment-manager-card">
    <div class="environment-manager-section-title">
        <h3>{{ $title }}</h3>
        <span class="environment-manager-badge environment-manager-badge--muted">{{ count($entries) }} registos</span>
    </div>

    @if(count($entries) > 0)
        <div class="environment-manager-table-wrap">
            <table class="environment-manager-table lsg-datatable">
                <thead>
                    <tr>
                        <th>Chave</th>
                        <th>Valor</th>
                        <th>Tipo</th>
                        <th>Origem</th>
                        <th>Módulo</th>
                        <th>Localização</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($entries as $entry)
                        <tr>
                            <td>
                                <div class="environment-manager-key">{{ $entry['key'] ?? '—' }}</div>
                                @if(!empty($entry['sensitive']))
                                    <span class="environment-manager-badge environment-manager-badge--danger">sensível</span>
                                @endif
                                @if(!empty($entry['description']))
                                    <div class="environment-manager-muted">{{ $entry['description'] }}</div>
                                @endif
                            </td>
                            <td><span class="environment-manager-value">{{ $entry['value'] ?? '—' }}</span></td>
                            <td>
                                <span class="environment-manager-badge environment-manager-badge--muted">{{ $entry['declared_type'] ?? $entry['type'] ?? '—' }}</span>
                            </td>
                            <td>{{ $entry['source'] ?? '—' }}</td>
                            <td>{{ $entry['module'] ?? '—' }}</td>
                            <td>
                                @if(!empty($entry['location']))
                                    <span>{{ $entry['location'] }}</span>
                                    @if(!empty($entry['line']))
                                        <span class="environment-manager-muted">:{{ $entry['line'] }}</span>
                                    @endif
                                @else
                                    <span class="environment-manager-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="environment-manager-alert environment-manager-alert--warning">{{ $emptyText }}</div>
    @endif
</div>
