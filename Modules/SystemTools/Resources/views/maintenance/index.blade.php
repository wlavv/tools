@extends('layouts.app')

@section('content')
<style>
    .system-tools-page {
        --st-radius: 5px;
        --st-border: var(--border-soft, rgba(21, 32, 51, .14));
        --st-text: var(--text-primary, #18212b);
        --st-muted: var(--text-muted, #6c757d);
        --st-panel: rgba(255,255,255,.78);
        --st-panel-strong: rgba(255,255,255,.94);
        --st-shadow: var(--shadow-soft, 0 14px 38px rgba(15, 23, 42, .08));
    }

    .system-tools-hero,
    .system-tools-panel,
    .system-tools-console {
        border: 1px solid var(--st-border);
        border-radius: var(--st-radius);
        background: linear-gradient(180deg, var(--st-panel-strong), var(--st-panel));
        box-shadow: var(--st-shadow);
    }

    .system-tools-hero {
        padding: 14px 16px;
        margin-bottom: 14px;
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
    }

    .system-tools-hero-title {
        margin: 0;
        font-weight: 700;
        color: var(--st-text);
        letter-spacing: -.02em;
        font-size: 1.08rem;
    }

    .system-tools-hero-subtitle {
        color: var(--st-muted);
        font-size: .82rem;
        margin-top: 3px;
    }

    .system-tools-main-grid {
        display: grid;
        grid-template-columns: 2fr 3fr; /* 60% / 40% */
        gap: 14px;
        align-items: start;
    }

    .system-tools-panel {
        overflow: hidden;
    }

    .system-tools-accordion-item + .system-tools-accordion-item {
        border-top: 1px solid var(--st-border);
    }

    .system-tools-accordion-button {
        width: 100%;
        border: 0;
        background: transparent;
        padding: 13px 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        color: var(--st-text);
        text-align: left;
    }

    .system-tools-accordion-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 700;
    }

    .system-tools-accordion-title i {
        width: 28px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: var(--st-radius);
        border: 1px solid var(--st-border);
        background: rgba(255,255,255,.65);
    }

    .system-tools-accordion-meta {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--st-muted);
        font-size: .78rem;
        white-space: nowrap;
    }

    .system-tools-accordion-body {
        padding: 0 14px 14px 14px;
    }

    .system-tool-list {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }

    .system-tool-row {
        border: 1px solid var(--st-border);
        border-radius: var(--st-radius);
        background: rgba(255,255,255,.58);
        padding: 8px 9px;
        display: grid;
        grid-template-columns: 32px minmax(0, 1fr) auto auto;
        gap: 10px;
        align-items: center;
        transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
        min-height: 48px;
    }

    .system-tool-row:hover {
        border-color: rgba(13, 110, 253, .32);
        box-shadow: 0 8px 18px rgba(15, 23, 42, .08);
        background: rgba(255,255,255,.75);
    }

    .system-tool-icon {
        width: 30px;
        height: 30px;
        border-radius: var(--st-radius);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--st-border);
        background: rgba(255,255,255,.78);
    }

    .system-tool-title {
        margin: 0;
        font-weight: 700;
        color: var(--st-text);
        font-size: .9rem;
        line-height: 1.1;
    }

    .system-tool-description {
        color: var(--st-muted);
        font-size: .74rem;
        line-height: 1.2;
        margin-top: 2px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .system-tool-badges {
        display: flex;
        gap: 5px;
        justify-content: flex-end;
        flex-wrap: wrap;
    }

    .system-risk-badge,
    .system-external-badge {
        border-radius: 999px;
        padding: 3px 7px;
        font-size: .68rem;
        font-weight: 700;
        border: 1px solid transparent;
        line-height: 1.15;
        white-space: nowrap;
    }

    .system-risk-safe {
        color: #0f5132;
        background: rgba(25, 135, 84, .12);
        border-color: rgba(25, 135, 84, .24);
    }

    .system-risk-medium {
        color: #664d03;
        background: rgba(255, 193, 7, .16);
        border-color: rgba(255, 193, 7, .32);
    }

    .system-risk-danger {
        color: #842029;
        background: rgba(220, 53, 69, .12);
        border-color: rgba(220, 53, 69, .26);
    }

    .system-external-badge {
        color: #084298;
        background: rgba(13, 110, 253, .10);
        border-color: rgba(13, 110, 253, .20);
    }

    .system-tool-run {
        border-radius: var(--st-radius);
        font-weight: 700;
        white-space: nowrap;
        padding: 5px 10px;
        font-size: .78rem;
    }

    .system-tools-console {
        overflow: hidden;
        position: sticky;
        top: 12px;
    }

    .system-tools-console-header {
        padding: 11px 12px;
        border-bottom: 1px solid var(--st-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
    }

    .system-tools-console-title {
        margin: 0;
        font-weight: 700;
        color: var(--st-text);
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: .98rem;
    }

    .system-tools-console-body {
        margin: 0;
        height: calc(100vh - 285px);
        min-height: 360px;
        max-height: 720px;
        overflow: auto;
        padding: 13px;
        background: #0f172a;
        color: #d1e7dd;
        font-size: .78rem;
        line-height: 1.42;
        white-space: pre-wrap;
        border-radius: 0 0 var(--st-radius) var(--st-radius);
    }

    .system-tools-console-body .failed { color: #fecaca; }
    .system-tools-console-body .success { color: #bbf7d0; }
    .system-tools-loading { opacity: .65; pointer-events: none; }

    @media (max-width: 991.98px) {
        .system-tools-main-grid {
            grid-template-columns: 1fr;
        }

        .system-tools-console {
            position: static;
        }

        .system-tools-console-body {
            height: 320px;
            min-height: 320px;
        }
    }

    @media (max-width: 767.98px) {
        .system-tools-hero {
            flex-direction: column;
            align-items: stretch;
        }

        .system-tool-row {
            grid-template-columns: 30px minmax(0, 1fr) auto;
        }

        .system-tool-badges {
            grid-column: 2 / 4;
            justify-content: flex-start;
        }
    }
</style>

<div class="system-tools-page">
    <div class="system-tools-hero">
        <div>
            <h4 class="system-tools-hero-title">Manutenção do Sistema</h4>
            <div class="system-tools-hero-subtitle">
                Ferramentas controladas para manutenção, cache, migrations, queues e diagnóstico.
            </div>
        </div>
        <div class="text-muted small">
            <i class="fa-solid fa-shield-halved me-1"></i>
            Apenas ações em whitelist podem ser executadas.
        </div>
    </div>

    @if(empty($tools))
        <div class="alert alert-warning">
            Nenhuma tool encontrada. Verifica se <code>Config/tools.php</code> está carregado em <code>system-tools.tools</code>.
        </div>
    @endif

    <div class="system-tools-main-grid">
        <div class="system-tools-panel">
            @foreach(collect($tools)->groupBy('section', preserveKeys: true) as $sectionKey => $items)
                @php
                    $section = $sections[$sectionKey] ?? [
                        'label' => ucfirst((string) $sectionKey),
                        'description' => '',
                        'icon' => 'fa-solid fa-toolbox',
                    ];

                    $collapseId = 'system-tools-section-' . preg_replace('/[^a-zA-Z0-9_-]/', '-', (string) $sectionKey);
                    $isFirst = $loop->first;
                @endphp

                <div class="system-tools-accordion-item">
                    <button
                        class="system-tools-accordion-button"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#{{ $collapseId }}"
                        aria-expanded="{{ $isFirst ? 'true' : 'false' }}"
                        aria-controls="{{ $collapseId }}"
                    >
                        <span>
                            <span class="system-tools-accordion-title">
                                <i class="{{ $section['icon'] ?? 'fa-solid fa-toolbox' }}"></i>
                                {{ $section['label'] ?? ucfirst((string) $sectionKey) }}
                            </span>
                            @if(!empty($section['description']))
                                <span class="d-block system-tools-hero-subtitle ms-5">
                                    {{ $section['description'] }}
                                </span>
                            @endif
                        </span>

                        <span class="system-tools-accordion-meta">
                            {{ count($items) }} ações
                            <i class="fa-solid fa-chevron-down"></i>
                        </span>
                    </button>

                    <div id="{{ $collapseId }}" class="collapse {{ $isFirst ? 'show' : '' }}">
                        <div class="system-tools-accordion-body">
                            <div class="system-tool-list">
                                @foreach($items as $key => $tool)
                                    @php
                                        $risk = $tool['risk'] ?? 'safe';
                                        $requiresConfirmation = !empty($tool['requires_confirmation']);
                                    @endphp

                                    <div class="system-tool-row" data-tool-card>
                                        <div class="system-tool-icon">
                                            <i class="{{ $tool['icon'] ?? 'fa-solid fa-terminal' }}"></i>
                                        </div>

                                        <div class="min-w-0">
                                            <h6 class="system-tool-title">{{ $tool['label'] ?? $key }}</h6>
                                            <div class="system-tool-description">
                                                {{ $tool['description'] ?? 'Sem descrição.' }}
                                            </div>
                                        </div>

                                        <div class="system-tool-badges">
                                            <span class="system-risk-badge system-risk-{{ $risk }}">
                                                {{ $riskLabels[$risk] ?? ucfirst($risk) }}
                                            </span>

                                            @if(!empty($tool['external']))
                                                <span class="system-external-badge">Stream Deck</span>
                                            @endif
                                        </div>

                                        <button
                                            type="button"
                                            class="btn btn-outline-primary system-tool-run"
                                            data-run-tool
                                            data-action="{{ $key }}"
                                            data-label="{{ $tool['label'] ?? $key }}"
                                            data-confirm="{{ $requiresConfirmation ? '1' : '0' }}"
                                            data-risk="{{ $risk }}"
                                            data-url="{{ route('system-tools.run', ['action' => $key]) }}"
                                        >
                                            <i class="fa-solid fa-play me-1"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="system-tools-console">
            <div class="system-tools-console-header">
                <h5 class="system-tools-console-title">
                    <i class="fa-solid fa-terminal"></i>
                    Console
                </h5>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="systemToolsClearConsole">
                    <i class="fa-solid fa-eraser me-1"></i>
                    Limpar
                </button>
            </div>
            <pre class="system-tools-console-body" id="systemToolsConsole">Console pronto.</pre>
        </div>
    </div>
</div>

<script>
(function () {
    const consoleEl = document.getElementById('systemToolsConsole');
    const clearBtn = document.getElementById('systemToolsClearConsole');

    function now() {
        return new Date().toLocaleString();
    }

    function appendConsole(message, type) {
        const prefix = '[' + now() + '] ';
        const classOpen = type ? '<span class="' + type + '">' : '';
        const classClose = type ? '</span>' : '';

        consoleEl.innerHTML += "\n" + classOpen + escapeHtml(prefix + message) + classClose;
        consoleEl.scrollTop = consoleEl.scrollHeight;
    }

    function setConsole(message) {
        consoleEl.textContent = message;
    }

    function escapeHtml(str) {
        return String(str).replace(/[&<>"']/g, function (m) {
            return ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            })[m];
        });
    }

    function csrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '{{ csrf_token() }}';
    }

    document.querySelectorAll('[data-run-tool]').forEach(function (button) {
        button.addEventListener('click', async function () {
            const action = button.dataset.action;
            const label = button.dataset.label || action;
            const url = button.dataset.url;
            const requiresConfirm = button.dataset.confirm === '1';
            const risk = button.dataset.risk || 'safe';
            const card = button.closest('[data-tool-card]');

            if (requiresConfirm) {
                const ok = window.confirm(
                    'Confirmas a execução de "' + label + '"?\n\n' +
                    'Risco: ' + risk.toUpperCase() + '\n' +
                    'Esta ação pode afetar cache, rotas, base de dados ou storage.'
                );

                if (!ok) {
                    appendConsole('CANCELLED: ' + label, '');
                    return;
                }
            }

            button.disabled = true;
            card.classList.add('system-tools-loading');

            appendConsole('RUNNING: ' + label + ' [' + action + ']', '');

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken(),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json().catch(function () {
                    return {
                        success: false,
                        message: 'Invalid JSON response.'
                    };
                });

                if (!response.ok || !data.success) {
                    appendConsole('FAILED: ' + label + "\n" + (data.message || 'Unknown error.'), 'failed');

                    if (data.debug && data.debug.requested_action) {
                        appendConsole('DEBUG requested action: ' + data.debug.requested_action, '');
                    }

                    if (data.debug && data.debug.available_actions) {
                        appendConsole('DEBUG available actions: ' + data.debug.available_actions.join(', '), '');
                    }

                    return;
                }

                appendConsole(
                    'SUCCESS: ' + label +
                    (data.duration_ms !== undefined ? ' (' + data.duration_ms + 'ms)' : '') +
                    "\n" + (data.output || data.message || 'Done.'),
                    'success'
                );
            } catch (error) {
                appendConsole('FAILED: ' + label + "\n" + error.message, 'failed');
            } finally {
                button.disabled = false;
                card.classList.remove('system-tools-loading');
            }
        });
    });

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            setConsole('Console limpo.');
        });
    }
})();
</script>
@endsection
