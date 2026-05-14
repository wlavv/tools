<div class="streamdeck-access-card sda-logs-card">
    <div class="sda-card-header">
        <strong>Últimas execuções</strong>
        <span>{{ $recentLogs->count() }} registos</span>
    </div>

    <div class="streamdeck-access-table-wrap">
        <table class="streamdeck-access-table streamdeck-access-table--logs">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Status</th>
                    <th>HTTP</th>
                    <th>IP</th>
                    <th>Duração</th>
                    <th>Erro / resultado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentLogs as $log)
                    <tr>
                        <td>{{ $log->created_at ? $log->created_at->format('d/m/Y H:i:s') : '—' }}</td>
                        <td><span class="sda-log-status sda-log-status--{{ $log->status }}">{{ $log->status }}</span></td>
                        <td>{{ $log->http_status ?: '—' }}</td>
                        <td>{{ $log->ip ?: '—' }}</td>
                        <td>{{ $log->response_ms !== null ? $log->response_ms . ' ms' : '—' }}</td>
                        <td class="sda-log-summary">
                            @if($log->error)
                                {{ $log->error }}
                            @elseif(data_get($log->response, 'task_result'))
                                <pre>{{ json_encode(data_get($log->response, 'task_result'), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                            @else
                                <pre>{{ json_encode($log->response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="sda-empty-state">
                                <strong>Sem execuções registadas</strong>
                                <span>Os pedidos externos aparecerão aqui.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
