@extends(config('streamdeck-access.layout', 'layouts.app'))

@section('content')
    @include('streamdeck-access::Includes.css')

    @php
        $tokenData = session('streamdeck_access_token');
        $typeLabels = config('streamdeck-access.types', []);
        $taskLabels = config('streamdeck-access.task_labels', []);
        $triggerBaseUrl = route('streamdeck_access.external.trigger', ['identifier' => $accessPoint->public_id]);
    @endphp

    <div class="streamdeck-access-shell">
        @if(session('success'))
            <div class="streamdeck-access-alert">{{ session('success') }}</div>
        @endif

        @if($tokenData)
            <div class="streamdeck-access-alert streamdeck-access-alert--token">
                <strong>Token disponível apenas agora.</strong>
                <span>{{ $tokenData['warning'] ?? 'Guarda o link antes de sair desta página.' }}</span>

                <div class="sda-copy-row">
                    <input id="streamdeck-url" type="text" class="streamdeck-access-input" value="{{ $tokenData['streamdeck_url'] }}" readonly>
                    <button type="button" class="lsg-action-btn lsg-action-btn--success" data-copy-target="streamdeck-url" data-copy-title="Copiar URL" data-copied-title="URL copiado">
                        <i class="fa-solid fa-copy"></i>
                        <span>Copiar URL</span>
                    </button>
                </div>

                <div class="sda-copy-row">
                    <input id="streamdeck-token" type="text" class="streamdeck-access-input" value="{{ $tokenData['plain_token'] }}" readonly>
                    <button type="button" class="lsg-action-btn lsg-action-btn--secondary" data-copy-target="streamdeck-token" data-copy-title="Copiar token" data-copied-title="Token copiado">
                        <i class="fa-solid fa-copy"></i>
                        <span>Copiar token</span>
                    </button>
                </div>
            </div>
        @endif

        <div class="streamdeck-access-card sda-show-card">
            <div class="sda-show-header">
                <div>
                    <h1 class="sda-show-title">{{ $accessPoint->name }}</h1>
                    <div class="sda-show-subtitle">{{ $accessPoint->slug }} · {{ $typeLabels[$accessPoint->type] ?? $accessPoint->type }}</div>
                </div>

                <div class="streamdeck-access-actions">
                    <a href="{{ route('streamdeck_access.edit', $accessPoint) }}" class="lsg-action-btn lsg-action-btn--warning">
                        <i class="fa-solid fa-pencil"></i>
                        <span>Editar</span>
                    </a>
                    <form method="POST" action="{{ route('streamdeck_access.rotate-token', $accessPoint) }}" class="lsg-action-form" onsubmit="return confirm('Rodar token? O link atual deixará de funcionar.');">
                        @csrf
                        <button type="submit" class="lsg-action-btn lsg-action-btn--secondary">
                            <i class="fa-solid fa-arrows-rotate"></i>
                            <span>Rodar token</span>
                        </button>
                    </form>
                </div>
            </div>

            <div class="streamdeck-access-grid sda-show-grid">
                <div class="streamdeck-access-meta">
                    <strong>Estado</strong>
                    <div>{{ $accessPoint->enabled ? 'Ativo' : 'Inativo' }}</div>
                </div>
                <div class="streamdeck-access-meta">
                    <strong>Public ID</strong>
                    <div>{{ $accessPoint->public_id }}</div>
                </div>
                <div class="streamdeck-access-meta">
                    <strong>Token hint</strong>
                    <div>••••••{{ $accessPoint->token_hint ?: '—' }}</div>
                </div>
                <div class="streamdeck-access-meta">
                    <strong>Utilizações</strong>
                    <div>{{ $accessPoint->use_count }}{{ $accessPoint->max_uses ? ' / ' . $accessPoint->max_uses : '' }}</div>
                </div>
                <div class="streamdeck-access-meta">
                    <strong>Última execução</strong>
                    <div>{{ $accessPoint->last_used_at ? $accessPoint->last_used_at->format('d/m/Y H:i') : '—' }}</div>
                </div>
                <div class="streamdeck-access-meta">
                    <strong>Expiração</strong>
                    <div>{{ $accessPoint->expires_at ? $accessPoint->expires_at->format('d/m/Y H:i') : '—' }}</div>
                </div>
                <div class="streamdeck-access-meta streamdeck-access-grid-1">
                    <strong>Trigger externo sem token</strong>
                    <div class="sda-copy-row sda-copy-row--compact">
                        <input id="streamdeck-base-url" type="text" class="streamdeck-access-input" value="{{ $triggerBaseUrl }}" readonly>
                        <button type="button" class="lsg-action-btn lsg-action-btn--secondary lsg-action-btn--compact" data-copy-target="streamdeck-base-url" title="Copiar">
                            <i class="fa-solid fa-copy"></i>
                        </button>
                    </div>
                </div>

                @if($accessPoint->type === 'redirect')
                    <div class="streamdeck-access-meta streamdeck-access-grid-1">
                        <strong>Destino</strong>
                        <div>{{ $accessPoint->target_url ?: '—' }}</div>
                    </div>
                @else
                    <div class="streamdeck-access-meta">
                        <strong>Tarefa</strong>
                        <div>{{ $taskLabels[$accessPoint->task_key] ?? $accessPoint->task_key }}</div>
                    </div>
                    <div class="streamdeck-access-meta">
                        <strong>Queue</strong>
                        <div>{{ $accessPoint->queue ?: config('streamdeck-access.default_queue', 'default') }}</div>
                    </div>
                    <div class="streamdeck-access-meta streamdeck-access-grid-1">
                        <strong>Payload</strong>
                        <pre>{{ json_encode($accessPoint->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                @endif

                @if($accessPoint->description)
                    <div class="streamdeck-access-meta streamdeck-access-grid-1">
                        <strong>Descrição</strong>
                        <div>{{ $accessPoint->description }}</div>
                    </div>
                @endif
            </div>
        </div>

        @include('streamdeck-access::Includes._components.logs', ['recentLogs' => $recentLogs])
    </div>

    @include('streamdeck-access::Includes.js')
@endsection
