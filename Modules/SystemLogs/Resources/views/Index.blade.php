@extends('layouts.app')

@push('styles')
    <style>
        .system-logs-lsg .sl-card {
            border: 1px solid var(--border-soft, rgba(148, 163, 184, 0.20));
            border-radius: 5px;
            background: var(--card-bg, rgba(255, 255, 255, 0.96));
            box-shadow: var(--shadow-soft, 0 8px 24px rgba(15, 23, 42, 0.06));
            overflow: hidden;
        }
        body.theme-dark .system-logs-lsg .sl-card,
        body[data-theme="dark"] .system-logs-lsg .sl-card {
            background: linear-gradient(180deg, rgba(37, 47, 59, 0.94) 0%, rgba(32, 40, 51, 0.96) 100%);
        }
        .system-logs-lsg .sl-stat { padding: 1rem 1.1rem; height: 100%; }
        .system-logs-lsg .sl-stat-label { font-size: .78rem; text-transform: uppercase; letter-spacing: .08em; opacity: .7; margin-bottom: .4rem; }
        .system-logs-lsg .sl-stat-value { font-size: 1.55rem; font-weight: 700; line-height: 1; }
        .system-logs-lsg .sl-section-title { font-size: .95rem; font-weight: 700; margin: 0; }
        .system-logs-lsg .sl-muted { opacity: .72; }
        .system-logs-lsg .sl-form-wrap, .system-logs-lsg .sl-table-wrap { padding: 1.15rem 1.25rem; }
        .system-logs-lsg .sl-form-card { border-top: 1px solid var(--border-soft, rgba(148, 163, 184, 0.20)); }
        .system-logs-lsg .sl-filter-grid {
            display: grid;
            grid-template-columns: 1.4fr .8fr .8fr .85fr .85fr .6fr auto;
            gap: .75rem;
            align-items: end;
        }
        .system-logs-lsg .sl-filter-box {
            padding: 1rem;
            border: 1px solid var(--border-soft, rgba(148, 163, 184, 0.18));
            border-radius: 5px;
            background: rgba(148, 163, 184, 0.05);
        }
        .system-logs-lsg .table > :not(caption) > * > * {
            padding-top: .85rem;
            padding-bottom: .85rem;
            vertical-align: middle;
            border-color: var(--border-soft, rgba(148, 163, 184, 0.20));
        }
        .system-logs-lsg .table thead th {
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            opacity: .72;
            white-space: nowrap;
        }
        .system-logs-lsg .sl-id { font-weight: 700; white-space: nowrap; }
        .system-logs-lsg .sl-message { min-width: 280px; }
        .system-logs-lsg .sl-context { max-width: 320px; }
        .system-logs-lsg .sl-context pre {
            margin: 0;
            white-space: pre-wrap;
            word-break: break-word;
            font-size: .80rem;
            line-height: 1.45;
            padding: .65rem .75rem;
            border-radius: 5px;
            background: rgba(148, 163, 184, 0.10);
            border: 1px solid rgba(148, 163, 184, 0.14);
            color: inherit;
            max-height: 120px;
            overflow: auto;
        }
        .system-logs-lsg .sl-empty { padding: 2.25rem 1rem; text-align: center; opacity: .75; }
        .system-logs-lsg .sl-toolbar-meta {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }
        .system-logs-lsg .sl-pagination { display: flex; gap: .35rem; flex-wrap: wrap; justify-content: flex-end; }
        .system-logs-lsg .sl-pagination .btn.active { pointer-events: none; }
        .system-logs-lsg .sl-alert-panel { border-left: 0; border-color: rgba(220, 38, 38, 0.24); }
        .system-logs-lsg .sl-alert-list { display: grid; gap: .65rem; }
        .system-logs-lsg .sl-alert-item {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: .75rem;
            align-items: center;
            padding: .75rem .85rem;
            border: 1px solid rgba(220, 38, 38, 0.18);
            background: rgba(220, 38, 38, 0.06);
        }
        .system-logs-lsg .sl-alert-message { font-weight: 700; word-break: break-word; }
        .system-logs-lsg .sl-row-acknowledged { opacity: .62; }
        .system-logs-lsg .sl-row-actions { width: 1%; white-space: nowrap; }
        @media (max-width: 1400px) {
            .system-logs-lsg .sl-filter-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        }
        @media (max-width: 991.98px) {
            .system-logs-lsg .sl-filter-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 575.98px) {
            .system-logs-lsg .sl-filter-grid { grid-template-columns: 1fr; }
        }
    </style>
@endpush

@section('content')
<div class="system-logs-lsg">
    @php
        $stats = $stats ?? [];
        $acknowledgedLogIds = $acknowledgedLogIds ?? [];
        $unacknowledgedErrorLogs = $unacknowledgedErrorLogs ?? collect();

        $levelBadgeClass = function ($level) {
            return match (strtolower((string) $level)) {
                'error', 'critical', 'alert', 'emergency' => 'danger',
                'warning' => 'warning',
                'success' => 'success',
                'debug' => 'secondary',
                default => 'primary',
            };
        };
    @endphp

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm">
            <div class="fw-semibold mb-1">Please review the form:</div>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="sl-card sl-alert-panel mb-3">
        <div class="sl-table-wrap">
            <div class="d-flex justify-content-between align-items-center mb-3 gap-2 flex-wrap">
                <div>
                    <h2 class="sl-section-title">{{ __('system-logs::messages.unacknowledged_errors') }}</h2>
                    <div class="sl-muted small">{{ __('system-logs::messages.unacknowledged_errors_note') }}</div>
                </div>
                @if($unacknowledgedErrorLogs->isNotEmpty())
                    <form method="POST" action="{{ route('system_logs.acknowledge_errors') }}">
                        @csrf
                        @foreach($unacknowledgedErrorLogs as $errorLog)
                            <input type="hidden" name="ids[]" value="{{ $errorLog->id }}">
                        @endforeach
                        <button class="btn btn-sm btn-outline-success" type="submit" title="{{ __('system-logs::messages.acknowledge_all') }}">
                            <i class="fa-solid fa-check-double"></i>
                        </button>
                    </form>
                @endif
            </div>

            @if($unacknowledgedErrorLogs->isEmpty())
                <div class="sl-muted small">{{ __('system-logs::messages.no_unacknowledged_errors') }}</div>
            @else
                <div class="sl-alert-list">
                    @foreach($unacknowledgedErrorLogs as $errorLog)
                        <div class="sl-alert-item">
                            <div>
                                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                    <span class="badge text-bg-{{ $levelBadgeClass($errorLog->level) }}">{{ strtoupper($errorLog->level) }}</span>
                                    <span class="sl-id">#{{ $errorLog->id }}</span>
                                    <span class="sl-muted small">{{ optional($errorLog->created_at)->format('Y-m-d H:i:s') }}</span>
                                </div>
                                <div class="sl-alert-message">{{ $errorLog->message }}</div>
                            </div>
                            <form method="POST" action="{{ route('system_logs.acknowledge', $errorLog) }}">
                                @csrf
                                <button class="btn btn-sm btn-outline-success" type="submit" title="{{ __('system-logs::messages.acknowledge') }}">
                                    <i class="fa-solid fa-check"></i>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="collapse mb-3 {{ $errors->any() ? 'show' : '' }}" id="createSystemLogCard">
        <div class="sl-card sl-form-card">
            <div class="sl-form-wrap">
                <div class="d-flex justify-content-between align-items-center mb-3 gap-2 flex-wrap">
                    <h2 class="sl-section-title">{{ __('system-logs::messages.create_manual_log') }}</h2>
                    <span class="sl-muted small">{{ __('system-logs::messages.diagnostics_note') }}</span>
                </div>

                <form method="POST" action="{{ route('system_logs.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-lg-2">
                            <label class="form-label">{{ __('system-logs::messages.level') }}</label>
                            <select name="level" class="form-select">
                                @foreach(['info', 'warning', 'error', 'success', 'debug'] as $level)
                                    <option value="{{ $level }}" @selected(old('level', 'info') === $level)>{{ ucfirst($level) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label">{{ __('system-logs::messages.message') }}</label>
                            <input name="message" class="form-control" value="{{ old('message') }}" placeholder="Describe the event that should be recorded." required>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label">{{ __('system-logs::messages.context_json') }}</label>
                            <input name="context" class="form-control" value="{{ old('context') }}" placeholder='{"source":"backoffice"}'>
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button class="btn btn-outline-primary" type="submit"><i class="fa-solid fa-floppy-disk me-1"></i> {{ __('system-logs::messages.save_log') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="sl-card">
        <div class="sl-table-wrap">
            <div class="d-flex justify-content-between align-items-center mb-3 gap-2 flex-wrap">
                <h2 class="sl-section-title">{{ __('system-logs::messages.latest_entries') }}</h2>
                <span class="sl-muted small">{{ __('system-logs::messages.filtering_note') }}</span>
            </div>

            <div class="sl-filter-box mb-3">
                <div class="sl-filter-grid">
                    <div>
                        <label class="form-label">{{ __('system-logs::messages.search') }}</label>
                        <input type="text" class="form-control" id="slSearch" placeholder="ID, message, context, user...">
                    </div>
                    <div>
                        <label class="form-label">{{ __('system-logs::messages.level') }}</label>
                        <select class="form-select" id="slLevelFilter">
                            <option value="">{{ __('system-logs::messages.all') }}</option>
                            <option value="info">Info</option>
                            <option value="warning">Warning</option>
                            <option value="error">Error</option>
                            <option value="success">Success</option>
                            <option value="debug">Debug</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">{{ __('system-logs::messages.user') }}</label>
                        <input type="text" class="form-control" id="slUserFilter" placeholder="User ID">
                    </div>
                    <div>
                        <label class="form-label">{{ __('system-logs::messages.date_from') }}</label>
                        <input type="date" class="form-control" id="slDateFrom">
                    </div>
                    <div>
                        <label class="form-label">{{ __('system-logs::messages.date_to') }}</label>
                        <input type="date" class="form-control" id="slDateTo">
                    </div>
                    <div>
                        <label class="form-label">{{ __('system-logs::messages.rows') }}</label>
                        <select class="form-select" id="slPageSize">
                            <option value="10">10</option>
                            <option value="25" selected>25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="999999">All</option>
                        </select>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-secondary w-100" type="button" id="slResetFilters"><i class="fa-solid fa-filter-circle-xmark me-1"></i> {{ __('system-logs::messages.reset') }}</button>
                    </div>
                </div>
            </div>

            <div class="sl-toolbar-meta mb-3">
                <div class="small sl-muted">{{ __('system-logs::messages.entries_loaded') }}: <span id="slVisibleCount">0</span> / {{ $logs->count() }}</div>
                <div class="small sl-muted" id="slPagingInfo"></div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="systemLogsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>{{ __('system-logs::messages.level') }}</th>
                            <th>{{ __('system-logs::messages.message') }}</th>
                            <th>{{ __('system-logs::messages.context_json') }}</th>
                            <th>{{ __('system-logs::messages.user') }}</th>
                            <th>Date</th>
                            <th class="sl-row-actions"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            @php
                                $isActionableLog = in_array(strtolower((string) $log->level), ['error', 'critical', 'alert', 'emergency'], true);
                                $isAcknowledged = in_array((string) $log->id, $acknowledgedLogIds, true);
                            @endphp
                            <tr class="{{ $isAcknowledged ? 'sl-row-acknowledged' : '' }}" data-level="{{ strtolower((string) $log->level) }}" data-user="{{ (string) ($log->user_id ?? '') }}" data-date="{{ optional($log->created_at)->format('Y-m-d') }}" data-search="{{ strtolower(trim(implode(' ', [$log->id, $log->level, $log->message, $log->context, $log->user_id, optional($log->created_at)->format('Y-m-d H:i:s')]))) }}">
                                <td class="sl-id">#{{ $log->id }}</td>
                                <td><span class="badge text-bg-{{ $levelBadgeClass($log->level) }}">{{ strtoupper($log->level) }}</span></td>
                                <td class="sl-message">{{ $log->message }}</td>
                                <td class="sl-context">@if(!empty($log->context))<pre>{{ $log->context }}</pre>@else<span class="sl-muted">—</span>@endif</td>
                                <td>{{ $log->user_id ?: '—' }}</td>
                                <td>{{ optional($log->created_at)->format('Y-m-d H:i:s') }}</td>
                                <td class="sl-row-actions">
                                    @if($isActionableLog && !$isAcknowledged)
                                        <form method="POST" action="{{ route('system_logs.acknowledge', $log) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success" type="submit" title="{{ __('system-logs::messages.acknowledge') }}">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        </form>
                                    @elseif($isAcknowledged)
                                        <span class="badge text-bg-success" title="{{ __('system-logs::messages.acknowledged') }}"><i class="fa-solid fa-check"></i></span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7"><div class="sl-empty"><i class="fa-solid fa-rectangle-list fa-2x mb-3"></i><div class="fw-semibold mb-1">{{ __('system-logs::messages.no_logs_available') }}</div><div>{{ __('system-logs::messages.no_logs_help') }}</div></div></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="sl-pagination mt-3" id="slPagination"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@php
    $slNoMatchingRecordsText = __('system-logs::messages.no_matching_records');
    $slShowingAllMatchingText = trans('system-logs::messages.showing_all_matching', ['total' => '__TOTAL__']);
    $slShowingRangeMatchingText = trans('system-logs::messages.showing_range_matching', ['start' => '__START__', 'end' => '__END__', 'total' => '__TOTAL__']);
@endphp
<script>
document.addEventListener('DOMContentLoaded', function () {
    const table = document.getElementById('systemLogsTable');
    if (!table) return;

    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr[data-search]'));
    if (!rows.length) return;

    const searchInput = document.getElementById('slSearch');
    const levelFilter = document.getElementById('slLevelFilter');
    const userFilter = document.getElementById('slUserFilter');
    const dateFrom = document.getElementById('slDateFrom');
    const dateTo = document.getElementById('slDateTo');
    const pageSize = document.getElementById('slPageSize');
    const resetButton = document.getElementById('slResetFilters');
    const visibleCount = document.getElementById('slVisibleCount');
    const pagingInfo = document.getElementById('slPagingInfo');
    const pagination = document.getElementById('slPagination');
    const noMatchingRecordsText = @json($slNoMatchingRecordsText);
    const showingAllMatchingText = @json($slShowingAllMatchingText);
    const showingRangeMatchingText = @json($slShowingRangeMatchingText);
    let currentPage = 1;

    function normalize(value) { return String(value || '').toLowerCase().trim(); }

    function getFilteredRows() {
        const search = normalize(searchInput.value);
        const level = normalize(levelFilter.value);
        const user = normalize(userFilter.value);
        const from = dateFrom.value || '';
        const to = dateTo.value || '';

        return rows.filter((row) => {
            const rowSearch = normalize(row.dataset.search);
            const rowLevel = normalize(row.dataset.level);
            const rowUser = normalize(row.dataset.user);
            const rowDate = row.dataset.date || '';
            if (search && !rowSearch.includes(search)) return false;
            if (level && rowLevel !== level) return false;
            if (user && !rowUser.includes(user)) return false;
            if (from && rowDate && rowDate < from) return false;
            if (to && rowDate && rowDate > to) return false;
            return true;
        });
    }

    function renderPagination(totalPages) {
        pagination.innerHTML = '';
        if (totalPages <= 1) return;

        const addButton = (label, page, disabled = false, active = false) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = `btn btn-sm ${active ? 'btn-primary active' : 'btn-outline-primary'}`;
            button.textContent = label;
            button.disabled = disabled;
            button.addEventListener('click', function () {
                currentPage = page;
                applyFilters();
            });
            pagination.appendChild(button);
        };

        addButton('Prev', Math.max(1, currentPage - 1), currentPage === 1);
        const maxButtons = 5;
        let start = Math.max(1, currentPage - 2);
        let end = Math.min(totalPages, start + maxButtons - 1);
        start = Math.max(1, end - maxButtons + 1);
        for (let page = start; page <= end; page += 1) addButton(String(page), page, false, currentPage === page);
        addButton('Next', Math.min(totalPages, currentPage + 1), currentPage === totalPages);
    }

    function applyFilters() {
        const filtered = getFilteredRows();
        const size = parseInt(pageSize.value, 10) || 25;
        const totalRows = filtered.length;
        const totalPages = size >= 999999 ? 1 : Math.max(1, Math.ceil(totalRows / size));
        if (currentPage > totalPages) currentPage = totalPages;

        const startIndex = size >= 999999 ? 0 : (currentPage - 1) * size;
        const endIndex = size >= 999999 ? totalRows : Math.min(startIndex + size, totalRows);
        rows.forEach((row) => row.style.display = 'none');
        filtered.slice(startIndex, endIndex).forEach((row) => row.style.display = '');
        visibleCount.textContent = String(totalRows);

        if (!totalRows) {
            pagingInfo.textContent = noMatchingRecordsText;
        } else if (size >= 999999) {
            pagingInfo.textContent = showingAllMatchingText.replace('__TOTAL__', totalRows);
        } else {
            pagingInfo.textContent = showingRangeMatchingText
                .replace('__START__', startIndex + 1)
                .replace('__END__', endIndex)
                .replace('__TOTAL__', totalRows);
        }

        renderPagination(totalPages);
    }

    [searchInput, levelFilter, userFilter, dateFrom, dateTo].forEach((element) => {
        element.addEventListener('input', function () { currentPage = 1; applyFilters(); });
        element.addEventListener('change', function () { currentPage = 1; applyFilters(); });
    });

    pageSize.addEventListener('change', function () { currentPage = 1; applyFilters(); });
    resetButton.addEventListener('click', function () {
        searchInput.value = '';
        levelFilter.value = '';
        userFilter.value = '';
        dateFrom.value = '';
        dateTo.value = '';
        pageSize.value = '25';
        currentPage = 1;
        applyFilters();
    });

    applyFilters();
});
</script>
@endpush
