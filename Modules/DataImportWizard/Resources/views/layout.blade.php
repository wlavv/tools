@extends('layouts.app')

@push('styles')
    <style>
        .diw-shell { width: 100%; display: flex; flex-direction: column; gap: 16px; }
        .diw-nav { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 4px; }
        .diw-nav a,
        .diw-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 12px;
            border: 1px solid var(--border-soft, rgba(255, 255, 255, .12));
            background: var(--lsg-bo-btn-bg, rgba(255, 255, 255, .045));
            color: var(--text-primary, #e8edf4);
            text-decoration: none;
            font-weight: 800;
        }
        .diw-card,
        .diw-shell .card,
        .diw-shell .metric {
            background: var(--bg-card, rgba(255, 255, 255, .04));
            border: 1px solid var(--border-soft, rgba(255, 255, 255, .12));
            color: var(--text-primary, #e8edf4);
            padding: 16px;
        }
        .diw-shell .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; }
        .diw-shell .prm-dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; }
        .diw-shell .prm-dashboard-metric { position: relative; overflow: hidden; border-radius: 0; padding: 16px; min-height: 104px; border: 1px solid rgba(148, 163, 184, .25); background: linear-gradient(135deg, rgba(255, 255, 255, .96), rgba(248, 250, 252, .86)); box-shadow: 0 8px 24px rgba(15, 23, 42, .08); display: flex; justify-content: space-between; gap: 14px; align-items: center; }
        .diw-shell .prm-dashboard-metric__label { font-size: 12px; text-transform: uppercase; color: #64748b; font-weight: 800; letter-spacing: .04em; }
        .diw-shell .prm-dashboard-metric__value { font-size: 30px; line-height: 1; font-weight: 900; color: #0f172a; margin-top: 6px; }
        .diw-shell .prm-dashboard-metric__icon { width: 46px; height: 46px; border-radius: 0; display: flex; align-items: center; justify-content: center; background: color-mix(in srgb, var(--metric-color, #2563eb) 16%, transparent); color: var(--metric-color, #2563eb); font-size: 20px; border: 1px solid color-mix(in srgb, var(--metric-color, #2563eb) 28%, transparent); flex: 0 0 46px; }
        .diw-shell .prm-dashboard-metric.roles { --metric-color: #2563eb; }
        .diw-shell .prm-dashboard-metric.permissions { --metric-color: #7c3aed; }
        .diw-shell .prm-dashboard-metric.critical { --metric-color: #dc2626; }
        .diw-shell .prm-dashboard-metric.users { --metric-color: #16a34a; }
        .diw-shell table { width: 100%; border-collapse: collapse; }
        .diw-shell th,
        .diw-shell td { border-bottom: 1px solid var(--border-soft, rgba(255, 255, 255, .1)); padding: 10px; text-align: left; vertical-align: top; }
        .diw-shell th { color: var(--text-secondary, #a8b3c2); font-weight: 800; }
        .diw-shell .badge { display: inline-block; padding: 3px 8px; font-size: 12px; background: rgba(255, 255, 255, .08); }
        .diw-shell .muted { color: var(--text-secondary, #a8b3c2); }
        .diw-shell pre { white-space: pre-wrap; background: rgba(0, 0, 0, .28); color: var(--text-primary, #e8edf4); padding: 12px; overflow: auto; }
    </style>
@endpush

@section('content')
    <div class="diw-shell">
        @if (isset($errors) && $errors->any())
            <div class="diw-card border-danger">
                <strong>Erros:</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('module-content')
    </div>
@endsection
