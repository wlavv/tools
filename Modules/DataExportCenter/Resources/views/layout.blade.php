@extends('layouts.app')

@push('styles')
    <style>
        .dec-shell { width: 100%; display: flex; flex-direction: column; gap: 16px; }
        .dec-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 16px;
            border: 1px solid var(--border-soft, rgba(255, 255, 255, .12));
            background: var(--bg-card, rgba(255, 255, 255, .04));
        }
        .dec-title { margin: 0; font-size: 18px; font-weight: 900; }
        .dec-subtitle { color: var(--text-secondary, #a8b3c2); font-size: 13px; }
        .dec-nav { display: flex; gap: 10px; flex-wrap: wrap; }
        .dec-nav a,
        .dec-actions a {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 12px;
            border: 1px solid var(--lsg-bo-btn-primary-border, rgba(96, 165, 250, .76));
            background: var(--lsg-bo-btn-primary-bg, rgba(37, 99, 235, .24));
            color: var(--lsg-bo-btn-primary-text, #eff6ff);
            text-decoration: none;
            font-weight: 800;
            box-shadow: var(--lsg-bo-btn-shadow, 0 8px 18px rgba(0, 0, 0, .12));
        }
        .dec-nav a:hover,
        .dec-actions a:hover {
            background: var(--lsg-bo-btn-primary-bg-hover, rgba(37, 99, 235, .34));
            color: var(--lsg-bo-btn-primary-text, #eff6ff);
        }
        .dec-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin: 0;
        }
        .dec-shell .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; }
        .dec-shell .prm-dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; }
        .dec-shell .prm-dashboard-metric { position: relative; overflow: hidden; border-radius: 0; padding: 16px; min-height: 104px; border: 1px solid rgba(148, 163, 184, .25); background: linear-gradient(135deg, rgba(255, 255, 255, .96), rgba(248, 250, 252, .86)); box-shadow: 0 8px 24px rgba(15, 23, 42, .08); display: flex; justify-content: space-between; gap: 14px; align-items: center; }
        .dec-shell .prm-dashboard-metric__label { font-size: 12px; text-transform: uppercase; color: #64748b; font-weight: 800; letter-spacing: .04em; }
        .dec-shell .prm-dashboard-metric__value { font-size: 30px; line-height: 1; font-weight: 900; color: #0f172a; margin-top: 6px; }
        .dec-shell .prm-dashboard-metric__icon { width: 46px; height: 46px; border-radius: 0; display: flex; align-items: center; justify-content: center; background: color-mix(in srgb, var(--metric-color, #2563eb) 16%, transparent); color: var(--metric-color, #2563eb); font-size: 20px; border: 1px solid color-mix(in srgb, var(--metric-color, #2563eb) 28%, transparent); flex: 0 0 46px; }
        .dec-shell .prm-dashboard-metric.roles { --metric-color: #2563eb; }
        .dec-shell .prm-dashboard-metric.permissions { --metric-color: #7c3aed; }
        .dec-shell .prm-dashboard-metric.critical { --metric-color: #dc2626; }
        .dec-shell .prm-dashboard-metric.users { --metric-color: #16a34a; }
        .dec-shell .card,
        .dec-shell .panel {
            background: var(--bg-card, rgba(255, 255, 255, .04));
            border: 1px solid var(--border-soft, rgba(255, 255, 255, .12));
            color: var(--text-primary, #e8edf4);
            padding: 16px;
        }
        .dec-shell table { width: 100%; border-collapse: collapse; }
        .dec-shell th,
        .dec-shell td { padding: 10px 12px; border-bottom: 1px solid var(--border-soft, rgba(255, 255, 255, .1)); text-align: left; vertical-align: top; }
        .dec-shell th { color: var(--text-secondary, #a8b3c2); font-weight: 800; }
        .dec-shell .muted { color: var(--text-secondary, #a8b3c2); }
        .dec-shell .badge { display: inline-block; padding: 3px 8px; background: rgba(255, 255, 255, .08); font-size: 12px; }
        .dec-shell button,
        .dec-shell select,
        .dec-shell input,
        .dec-shell textarea { border: 1px solid var(--border-soft, rgba(255, 255, 255, .12)); padding: 8px 10px; }
        .dec-shell textarea { width: 100%; min-height: 90px; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; }
    </style>
@endpush

@section('content')
    <div class="dec-shell">
        @yield('module-content')
    </div>
@endsection
