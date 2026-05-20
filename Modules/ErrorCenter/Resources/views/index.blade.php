@php
    $namePrefix = config('error-center.route_name_prefix', 'error-center.');
    $routes = [
        'stats' => route($namePrefix . 'api.stats'),
        'events' => route($namePrefix . 'api.events'),
    ];
@endphp
@extends('error-center::layouts.module')

@section('content')
<style>
        .wrap { color-scheme: light; --bg: #f6f7fb; --card: #fff; --border: #e5e7eb; --text: #111827; --muted: #6b7280; --danger: #b91c1c; --warn: #92400e; --ok: #166534; --brand: #1f2937; }
        .wrap { color: var(--text); font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        .wrap { width: 100%; max-width: none; margin: 0; padding: 0; }
        .header { display: flex; justify-content: space-between; gap: 16px; align-items: center; margin-bottom: 22px; }
        h1 { margin: 0; font-size: 28px; }
        .subtitle { color: var(--muted); margin-top: 4px; }
        .prm-dashboard-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-bottom: 18px; }
        .card { background: var(--card); border: 1px solid var(--border); border-radius: 0; padding: 16px; box-shadow: 0 1px 2px rgba(15, 23, 42, .04); }
        .prm-dashboard-metric { position: relative; overflow: hidden; border-radius: 0 !important; padding: 16px !important; min-height: 104px; border: 1px solid rgba(148, 163, 184, .25) !important; background: linear-gradient(135deg, rgba(255, 255, 255, .96), rgba(248, 250, 252, .86)) !important; box-shadow: 0 8px 24px rgba(15, 23, 42, .08) !important; display: flex; justify-content: space-between; gap: 14px; align-items: center; }
        .prm-dashboard-metric__label { font-size: 12px; text-transform: uppercase; color: #64748b; font-weight: 800; letter-spacing: .04em; }
        .prm-dashboard-metric__value { font-size: 30px; line-height: 1; font-weight: 900; color: #0f172a; margin-top: 6px; }
        .prm-dashboard-metric__icon { width: 46px; height: 46px; border-radius: 0; display: flex; align-items: center; justify-content: center; background: color-mix(in srgb, var(--metric-color, #2563eb) 16%, transparent); color: var(--metric-color, #2563eb); font-size: 20px; border: 1px solid color-mix(in srgb, var(--metric-color, #2563eb) 28%, transparent); flex: 0 0 46px; }
        .prm-dashboard-metric.roles { --metric-color: #2563eb; }
        .prm-dashboard-metric.permissions { --metric-color: #7c3aed; }
        .prm-dashboard-metric.critical { --metric-color: #dc2626; }
        .prm-dashboard-metric.users { --metric-color: #16a34a; }
        .panel { background: var(--card); border: 1px solid var(--border); border-radius: 0 !important; box-shadow: 0 1px 2px rgba(15, 23, 42, .04); overflow: hidden; }
        .filters { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 10px; padding: 16px; border-bottom: 1px solid var(--border); }
        input, select, button { font: inherit; }
        input, select { width: 100%; box-sizing: border-box; border: 1px solid var(--border); border-radius: 8px; padding: 9px 10px; background: #fff; }
        button { border: 1px solid var(--border); background: #fff; color: var(--text); border-radius: 8px; padding: 9px 12px; cursor: pointer; }
        button.primary { background: var(--brand); color: #fff; border-color: var(--brand); }
        button:hover { filter: brightness(.98); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 14px; border-bottom: 1px solid var(--border); text-align: left; vertical-align: top; font-size: 14px; }
        th { background: #f9fafb; color: #374151; font-size: 12px; text-transform: uppercase; letter-spacing: .04em; }
        tr:hover td { background: #fcfcfd; }
        .badge { display: inline-flex; align-items: center; border-radius: 999px; padding: 3px 8px; font-size: 12px; font-weight: 600; border: 1px solid var(--border); background: #f9fafb; }
        .critical { color: var(--danger); background: #fef2f2; border-color: #fecaca; }
        .error { color: #991b1b; background: #fff1f2; border-color: #fecdd3; }
        .warning { color: var(--warn); background: #fffbeb; border-color: #fde68a; }
        .resolved { color: var(--ok); background: #f0fdf4; border-color: #bbf7d0; }
        .muted { color: var(--muted); }
        .title { max-width: 440px; font-weight: 600; line-height: 1.35; }
        .small { font-size: 12px; color: var(--muted); margin-top: 3px; }
        .pagination { display: flex; justify-content: space-between; align-items: center; padding: 14px 16px; }
        .empty { padding: 36px; text-align: center; color: var(--muted); }
        @media (max-width: 980px) { .prm-dashboard-grid { grid-template-columns: repeat(2, 1fr); } .filters { grid-template-columns: repeat(2, 1fr); } .table-scroll { overflow-x: auto; } }
        @media (max-width: 575px) { .prm-dashboard-grid { grid-template-columns: 1fr; } }
    </style>
<div class="wrap">
    <section class="prm-dashboard-grid">
        <div class="prm-dashboard-metric roles"><div><div class="prm-dashboard-metric__label">Open errors</div><div class="prm-dashboard-metric__value" id="stat-total-open">-</div></div><div class="prm-dashboard-metric__icon"><i class="fa-solid fa-bug"></i></div></div>
        <div class="prm-dashboard-metric users"><div><div class="prm-dashboard-metric__label">New today</div><div class="prm-dashboard-metric__value" id="stat-new-today">-</div></div><div class="prm-dashboard-metric__icon"><i class="fa-solid fa-calendar-day"></i></div></div>
        <div class="prm-dashboard-metric critical"><div><div class="prm-dashboard-metric__label">Critical open</div><div class="prm-dashboard-metric__value" id="stat-critical-open">-</div></div><div class="prm-dashboard-metric__icon"><i class="fa-solid fa-triangle-exclamation"></i></div></div>
        <div class="prm-dashboard-metric permissions"><div><div class="prm-dashboard-metric__label">Resolved this week</div><div class="prm-dashboard-metric__value" id="stat-resolved-week">-</div></div><div class="prm-dashboard-metric__icon"><i class="fa-solid fa-circle-check"></i></div></div>
    </section>

    <section class="panel">
        <div class="filters">
            <select id="filter-status">
                <option value="">Status</option>
                <option value="new">new</option>
                <option value="acknowledged">acknowledged</option>
                <option value="in_progress">in_progress</option>
                <option value="resolved">resolved</option>
                <option value="ignored">ignored</option>
            </select>
            <select id="filter-severity">
                <option value="">Severidade</option>
                <option value="critical">critical</option>
                <option value="error">error</option>
                <option value="warning">warning</option>
                <option value="info">info</option>
            </select>
            <input id="filter-module" placeholder="Módulo">
            <input id="filter-environment" placeholder="Ambiente">
            <input id="filter-search" placeholder="Buscar erro, hash, mensagem">
            <input id="filter-date-from" type="date" title="Desde">
            <button class="primary" onclick="applyFilters()">Filtrar</button>
        </div>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Última ocorrência</th>
                        <th>Severidade</th>
                        <th>Status</th>
                        <th>Módulo</th>
                        <th>Erro</th>
                        <th>Ocorrências</th>
                        <th>Usuários</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="events-body"></tbody>
            </table>
            <div id="empty" class="empty" style="display:none">Nenhum erro encontrado.</div>
        </div>
        <div class="pagination">
            <div class="muted" id="page-info">-</div>
            <div>
                <button onclick="previousPage()">Anterior</button>
                <button onclick="nextPage()">Próxima</button>
            </div>
        </div>
    </section>
</div>

<script>
const endpoints = @json($routes);
let page = 1;
let lastPage = 1;
let filters = {};

function qs(params) {
    const search = new URLSearchParams();
    Object.entries(params).forEach(([key, value]) => {
        if (value !== null && value !== undefined && value !== '') search.append(key, value);
    });
    return search.toString();
}

function text(value) {
    return value === null || value === undefined || value === '' ? '-' : String(value);
}

function formatDate(value) {
    if (!value) return '-';
    const date = new Date(value);
    return isNaN(date.getTime()) ? value : date.toLocaleString();
}

function escapeHtml(value) {
    return text(value).replace(/[&<>'"]/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[char]));
}

function badge(value) {
    const css = ['critical', 'error', 'warning', 'resolved'].includes(value) ? value : '';
    return `<span class="badge ${css}">${escapeHtml(value)}</span>`;
}

async function loadStats() {
    const res = await fetch(endpoints.stats);
    const data = await res.json();
    document.getElementById('stat-total-open').textContent = data.total_open ?? '-';
    document.getElementById('stat-new-today').textContent = data.new_today ?? '-';
    document.getElementById('stat-critical-open').textContent = data.critical_open ?? '-';
    document.getElementById('stat-resolved-week').textContent = data.resolved_this_week ?? '-';
}

async function loadEvents() {
    const query = qs({...filters, page, per_page: 25});
    const res = await fetch(`${endpoints.events}?${query}`);
    const json = await res.json();
    const body = document.getElementById('events-body');
    body.innerHTML = '';

    lastPage = json.meta?.last_page || 1;
    document.getElementById('page-info').textContent = `Página ${json.meta?.page || 1} de ${lastPage} · ${json.meta?.total || 0} registos`;
    document.getElementById('empty').style.display = (json.data || []).length ? 'none' : 'block';

    (json.data || []).forEach(event => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${escapeHtml(formatDate(event.last_seen_at))}</td>
            <td>${badge(event.severity)}</td>
            <td>${badge(event.status)}</td>
            <td>${escapeHtml(event.module)}</td>
            <td><div class="title">${escapeHtml(event.title)}</div><div class="small">${escapeHtml(event.error_type)}</div></td>
            <td>${escapeHtml(event.occurrence_count)}</td>
            <td>${escapeHtml(event.affected_users_count)}</td>
            <td><a href="${event.url}">Ver</a></td>
        `;
        body.appendChild(row);
    });
}

function applyFilters() {
    filters = {
        status: document.getElementById('filter-status').value,
        severity: document.getElementById('filter-severity').value,
        module: document.getElementById('filter-module').value,
        environment: document.getElementById('filter-environment').value,
        search: document.getElementById('filter-search').value,
        date_from: document.getElementById('filter-date-from').value,
    };
    page = 1;
    loadEvents();
}

function previousPage() { if (page > 1) { page--; loadEvents(); } }
function nextPage() { if (page < lastPage) { page++; loadEvents(); } }
function loadAll() { loadStats(); loadEvents(); }
loadAll();
</script>
@endsection
