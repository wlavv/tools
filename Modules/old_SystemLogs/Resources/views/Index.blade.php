@extends('layouts.app')

@section('content')
<div class="container-fluid py-3 system-logs-lsg">
    @php
        $pageTitle = $pageTitle ?? 'System Logs';
        $pageSubtitle = $pageSubtitle ?? 'Monitor recent platform events and create manual log entries when needed.';
        $stats = $stats ?? [];

        $levelBadgeClass = function ($level) {
            return match (strtolower((string) $level)) {
                'error', 'critical' => 'danger',
                'warning' => 'warning',
                'success' => 'success',
                'debug' => 'secondary',
                default => 'primary',
            };
        };
    @endphp

    <style>
        .system-logs-lsg .sl-card {
            border: 1px solid var(--border-soft, rgba(148, 163, 184, 0.20));
            border-radius: 8px;
            background: var(--card-bg, rgba(255, 255, 255, 0.96));
            box-shadow: var(--shadow-soft, 0 8px 24px rgba(15, 23, 42, 0.06));
            overflow: hidden;
        }

        body.theme-dark .system-logs-lsg .sl-card,
        body[data-theme="dark"] .system-logs-lsg .sl-card {
            background: linear-gradient(180deg, rgba(37, 47, 59, 0.94) 0%, rgba(32, 40, 51, 0.96) 100%);
        }

        .system-logs-lsg .sl-header {
            padding: 1.15rem 1.25rem;
        }

        .system-logs-lsg .sl-title {
            font-size: 1.35rem;
            font-weight: 700;
            margin: 0;
        }

        .system-logs-lsg .sl-subtitle {
            margin: .35rem 0 0;
            opacity: .78;
        }

        .system-logs-lsg .sl-actions {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .system-logs-lsg .sl-stat {
            padding: 1rem 1.1rem;
            height: 100%;
        }

        .system-logs-lsg .sl-stat-label {
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            opacity: .7;
            margin-bottom: .4rem;
        }

        .system-logs-lsg .sl-stat-value {
            font-size: 1.55rem;
            font-weight: 700;
            line-height: 1;
        }

        .system-logs-lsg .sl-section-title {
            font-size: .95rem;
            font-weight: 700;
            margin: 0;
        }

        .system-logs-lsg .sl-muted {
            opacity: .72;
        }

        .system-logs-lsg .sl-form-wrap,
        .system-logs-lsg .sl-table-wrap {
            padding: 1.15rem 1.25rem;
        }

        .system-logs-lsg .sl-form-card {
            border-top: 1px solid var(--border-soft, rgba(148, 163, 184, 0.20));
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

        .system-logs-lsg .sl-id {
            font-weight: 700;
            white-space: nowrap;
        }

        .system-logs-lsg .sl-message {
            min-width: 280px;
        }

        .system-logs-lsg .sl-context {
            max-width: 320px;
        }

        .system-logs-lsg .sl-context pre {
            margin: 0;
            white-space: pre-wrap;
            word-break: break-word;
            font-size: .80rem;
            line-height: 1.45;
            padding: .65rem .75rem;
            border-radius: 6px;
            background: rgba(148, 163, 184, 0.10);
            border: 1px solid rgba(148, 163, 184, 0.14);
            color: inherit;
        }

        .system-logs-lsg .sl-empty {
            padding: 2.25rem 1rem;
            text-align: center;
            opacity: .75;
        }

        .system-logs-lsg .btn-outline-primary,
        .system-logs-lsg .btn-outline-success,
        .system-logs-lsg .btn-outline-secondary {
            box-shadow: none;
        }
    </style>

    <div class="sl-card mb-3">
        <div class="sl-header">
            <div class="row g-3 align-items-center">
                <div class="col-lg-8">
                    <h1 class="sl-title">{{ $pageTitle }}</h1>
                    <p class="sl-subtitle">{{ $pageSubtitle }}</p>
                </div>
                <div class="col-lg-4">
                    <div class="sl-actions">
                        <a href="{{ route('system_logs.index') }}" class="btn btn-outline-primary">
                            <i class="fa-solid fa-rotate-right me-1"></i> Refresh
                        </a>
                        <button class="btn btn-outline-success" type="button" data-bs-toggle="collapse" data-bs-target="#createSystemLogCard" aria-expanded="false" aria-controls="createSystemLogCard">
                            <i class="fa-solid fa-plus me-1"></i> New log
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

    <div class="row g-3 mb-3">
        <div class="col-6 col-xl-3">
            <div class="sl-card sl-stat">
                <div class="sl-stat-label">Entries loaded</div>
                <div class="sl-stat-value">{{ $stats['total'] ?? 0 }}</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="sl-card sl-stat">
                <div class="sl-stat-label">Errors</div>
                <div class="sl-stat-value">{{ $stats['error'] ?? 0 }}</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="sl-card sl-stat">
                <div class="sl-stat-label">Warnings</div>
                <div class="sl-stat-value">{{ $stats['warning'] ?? 0 }}</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="sl-card sl-stat">
                <div class="sl-stat-label">Info</div>
                <div class="sl-stat-value">{{ $stats['info'] ?? 0 }}</div>
            </div>
        </div>
    </div>

    <div class="collapse mb-3" id="createSystemLogCard">
        <div class="sl-card sl-form-card">
            <div class="sl-form-wrap">
                <div class="d-flex justify-content-between align-items-center mb-3 gap-2 flex-wrap">
                    <h2 class="sl-section-title">Create manual log entry</h2>
                    <span class="sl-muted small">Use for internal diagnostics or quick operational notes.</span>
                </div>

                <form method="POST" action="{{ route('system_logs.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-lg-2">
                            <label class="form-label">Level</label>
                            <select name="level" class="form-select">
                                @foreach(['info', 'warning', 'error', 'success', 'debug'] as $level)
                                    <option value="{{ $level }}" @selected(old('level', 'info') === $level)>{{ ucfirst($level) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label">Message</label>
                            <input name="message" class="form-control" value="{{ old('message') }}" placeholder="Describe the event that should be recorded." required>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label">Context JSON</label>
                            <input name="context" class="form-control" value="{{ old('context') }}" placeholder='{"source":"backoffice"}'>
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button class="btn btn-outline-primary" type="submit">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Save log
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="sl-card">
        <div class="sl-table-wrap">
            <div class="d-flex justify-content-between align-items-center mb-3 gap-2 flex-wrap">
                <h2 class="sl-section-title">Latest entries</h2>
                <span class="sl-muted small">Showing the most recent {{ $logs->count() }} records.</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Level</th>
                            <th>Message</th>
                            <th>Context</th>
                            <th>User</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td class="sl-id">#{{ $log->id }}</td>
                                <td>
                                    <span class="badge text-bg-{{ $levelBadgeClass($log->level) }}">
                                        {{ strtoupper($log->level) }}
                                    </span>
                                </td>
                                <td class="sl-message">{{ $log->message }}</td>
                                <td class="sl-context">
                                    @if(!empty($log->context))
                                        <pre>{{ $log->context }}</pre>
                                    @else
                                        <span class="sl-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $log->user_id ?: '—' }}</td>
                                <td>{{ optional($log->created_at)->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="sl-empty">
                                        <i class="fa-solid fa-rectangle-list fa-2x mb-3"></i>
                                        <div class="fw-semibold mb-1">No logs available yet</div>
                                        <div>Create the first manual entry or wait for the module to receive events.</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
