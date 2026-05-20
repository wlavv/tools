@php
    $namePrefix = config('error-center.route_name_prefix', 'error-center.');
    $routes = [
        'index' => route($namePrefix . 'index'),
        'detail' => route($namePrefix . 'api.events.show', ['errorEvent' => $errorEvent->id]),
        'occurrences' => route($namePrefix . 'api.events.occurrences', ['errorEvent' => $errorEvent->id]),
        'status' => route($namePrefix . 'api.events.status', ['errorEvent' => $errorEvent->id]),
        'resolve' => route($namePrefix . 'api.events.resolve', ['errorEvent' => $errorEvent->id]),
        'ignore' => route($namePrefix . 'api.events.ignore', ['errorEvent' => $errorEvent->id]),
    ];
@endphp
@extends('error-center::layouts.module')

@section('content')
<style>
        .wrap { color-scheme: light; --bg: #f6f7fb; --card: #fff; --border: #e5e7eb; --text: #111827; --muted: #6b7280; --danger: #b91c1c; --warn: #92400e; --ok: #166534; --brand: #1f2937; }
        .wrap { color: var(--text); font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        .wrap { width: 100%; max-width: none; margin: 0; padding: 0; }
        .top { display: flex; justify-content: space-between; gap: 16px; align-items: flex-start; margin-bottom: 18px; }
        a { color: #2563eb; text-decoration: none; }
        h1 { margin: 8px 0 6px; font-size: 26px; line-height: 1.25; }
        .muted { color: var(--muted); }
        .grid { display: grid; grid-template-columns: 1fr 360px; gap: 16px; }
        .card { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 16px; box-shadow: 0 1px 2px rgba(15, 23, 42, .04); margin-bottom: 16px; }
        .card h2 { margin: 0 0 12px; font-size: 16px; }
        .meta { display: grid; grid-template-columns: 180px 1fr; gap: 8px 12px; font-size: 14px; }
        .meta .key { color: var(--muted); }
        .badge { display: inline-flex; align-items: center; border-radius: 999px; padding: 3px 8px; font-size: 12px; font-weight: 600; border: 1px solid var(--border); background: #f9fafb; }
        .critical { color: var(--danger); background: #fef2f2; border-color: #fecaca; }
        .error { color: #991b1b; background: #fff1f2; border-color: #fecdd3; }
        .warning { color: var(--warn); background: #fffbeb; border-color: #fde68a; }
        .resolved { color: var(--ok); background: #f0fdf4; border-color: #bbf7d0; }
        button { font: inherit; border: 1px solid var(--border); background: #fff; color: var(--text); border-radius: 8px; padding: 9px 12px; cursor: pointer; margin: 0 6px 8px 0; }
        button.primary { background: var(--brand); color: #fff; border-color: var(--brand); }
        button.danger { background: #b91c1c; color: #fff; border-color: #b91c1c; }
        pre { white-space: pre-wrap; word-break: break-word; background: #0f172a; color: #e5e7eb; border-radius: 10px; padding: 14px; overflow: auto; max-height: 520px; font-size: 13px; line-height: 1.5; }
        code { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px 12px; border-bottom: 1px solid var(--border); text-align: left; vertical-align: top; font-size: 14px; }
        th { background: #f9fafb; color: #374151; font-size: 12px; text-transform: uppercase; letter-spacing: .04em; }
        .copy { float: right; font-size: 12px; padding: 5px 8px; }
        @media (max-width: 980px) { .grid { grid-template-columns: 1fr; } .meta { grid-template-columns: 1fr; } }
    </style>
<div class="wrap">
    <div class="top">
        <div>
            <a href="{{ $routes['index'] }}">← Voltar</a>
            <h1 id="title">Error #{{ $errorEvent->id }}</h1>
            <div class="muted" id="subtitle">A carregar detalhe técnico...</div>
        </div>
        <div>
            <button onclick="loadDetail()">Atualizar</button>
        </div>
    </div>

    <div class="grid">
        <main>
            <section class="card">
                <h2>Resumo</h2>
                <div class="meta" id="summary"></div>
            </section>

            <section class="card">
                <h2>Stack trace <button class="copy" onclick="copyText('stack')">Copiar</button></h2>
                <pre><code id="stack">-</code></pre>
            </section>

            <section class="card">
                <h2>Payload sanitizado <button class="copy" onclick="copyText('payload')">Copiar</button></h2>
                <pre><code id="payload">-</code></pre>
            </section>

            <section class="card">
                <h2>Contexto <button class="copy" onclick="copyText('context')">Copiar</button></h2>
                <pre><code id="context">-</code></pre>
            </section>
        </main>

        <aside>
            <section class="card">
                <h2>Ações</h2>
                <button onclick="setStatus('acknowledged')">Marcar visto</button>
                <button onclick="setStatus('in_progress')">Em análise</button>
                <button class="primary" onclick="resolveEvent()">Resolver</button>
                <button class="danger" onclick="ignoreEvent()">Ignorar</button>
                <div class="muted" id="action-result"></div>
            </section>

            <section class="card">
                <h2>Última ocorrência</h2>
                <div class="meta" id="latest"></div>
            </section>

            <section class="card">
                <h2>Ocorrências recentes</h2>
                <table>
                    <thead>
                        <tr><th>Data</th><th>Usuário</th><th>Status</th></tr>
                    </thead>
                    <tbody id="occurrences"></tbody>
                </table>
            </section>
        </aside>
    </div>
</div>

<script>
const routes = @json($routes);
const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
let current = null;

function text(value) { return value === null || value === undefined || value === '' ? '-' : String(value); }
function formatDate(value) { if (!value) return '-'; const date = new Date(value); return isNaN(date.getTime()) ? value : date.toLocaleString(); }
function escapeHtml(value) { return text(value).replace(/[&<>'"]/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[char])); }
function pretty(value) { return JSON.stringify(value ?? null, null, 2); }
function badge(value) { const css = ['critical', 'error', 'warning', 'resolved'].includes(value) ? value : ''; return `<span class="badge ${css}">${escapeHtml(value)}</span>`; }
function row(key, value, raw = false) { return `<div class="key">${escapeHtml(key)}</div><div>${raw ? value : escapeHtml(value)}</div>`; }

async function loadDetail() {
    const res = await fetch(routes.detail);
    const json = await res.json();
    current = json.data;
    render();
}

function render() {
    if (!current) return;

    document.getElementById('title').textContent = current.title;
    document.getElementById('subtitle').textContent = `${current.error_type || '-'} · #${current.id} · ${current.hash || '-'}`;

    document.getElementById('summary').innerHTML = [
        row('Severidade', badge(current.severity), true),
        row('Status', badge(current.status), true),
        row('Módulo', current.module),
        row('Fonte', current.source),
        row('Ambiente', current.environment),
        row('Primeira ocorrência', formatDate(current.first_seen_at)),
        row('Última ocorrência', formatDate(current.last_seen_at)),
        row('Ocorrências', current.occurrence_count),
        row('Usuários afetados', current.affected_users_count),
        row('Notificações', current.notification_count),
        row('Último alerta', current.last_notification_event),
    ].join('');

    const latest = current.latest_occurrence || {};
    document.getElementById('latest').innerHTML = [
        row('Data', formatDate(latest.occurred_at)),
        row('Endpoint', latest.endpoint),
        row('Método', latest.http_method),
        row('Status code', latest.status_code),
        row('User ID', latest.user_id),
        row('Tenant ID', latest.tenant_id),
        row('Request ID', latest.request_id),
        row('Correlation ID', latest.correlation_id),
        row('IP', latest.ip_address),
        row('User Agent', latest.user_agent),
    ].join('');

    document.getElementById('stack').textContent = latest.stack_trace || '-';
    document.getElementById('payload').textContent = pretty(latest.payload_snapshot);
    document.getElementById('context').textContent = pretty(latest.context_json);

    const tbody = document.getElementById('occurrences');
    tbody.innerHTML = '';
    (current.recent_occurrences || []).forEach(occurrence => {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${escapeHtml(formatDate(occurrence.occurred_at))}</td><td>${escapeHtml(occurrence.user_id)}</td><td>${escapeHtml(occurrence.status_code)}</td>`;
        tbody.appendChild(tr);
    });
}

async function post(url, body = {}) {
    const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify(body),
    });

    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return res.json();
}

async function setStatus(status) {
    await post(routes.status, {status});
    document.getElementById('action-result').textContent = `Status atualizado para ${status}.`;
    await loadDetail();
}

async function resolveEvent() {
    await post(routes.resolve);
    document.getElementById('action-result').textContent = 'Erro marcado como resolvido.';
    await loadDetail();
}

async function ignoreEvent() {
    await post(routes.ignore);
    document.getElementById('action-result').textContent = 'Erro ignorado.';
    await loadDetail();
}

async function copyText(id) {
    const value = document.getElementById(id).textContent;
    await navigator.clipboard.writeText(value);
}

loadDetail();
</script>
@endsection
