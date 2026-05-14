@extends(config('module-dependency-map.layout', 'layouts.app'))

@section('content')
<?php $routeName = config('module-dependency-map.route_name', 'module-dependency-map.'); ?>

<style>
    .mdm-wrap{display:flex;flex-direction:column;gap:12px}
    .mdm-panel{border-radius:0!important;border:1px solid rgba(148,163,184,.25)!important;background:var(--bg-card,rgba(255,255,255,.04));box-shadow:0 8px 24px rgba(15,23,42,.08)}
    .mdm-panel .card-body,.mdm-panel .card-header{border-radius:0!important}
    .mdm-table th{font-size:12px;text-transform:uppercase;color:#64748b;font-weight:800;letter-spacing:.04em}
    .mdm-table td,.mdm-table th{vertical-align:middle}
    .prm-dashboard-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px}
    .prm-dashboard-metric{position:relative;overflow:hidden;border-radius:0;padding:16px;min-height:104px;border:1px solid rgba(148,163,184,.25);background:linear-gradient(135deg,rgba(255,255,255,.96),rgba(248,250,252,.86));box-shadow:0 8px 24px rgba(15,23,42,.08);display:flex;justify-content:space-between;gap:14px;align-items:center}
    .prm-dashboard-metric__label{font-size:12px;text-transform:uppercase;color:#64748b;font-weight:800;letter-spacing:.04em}
    .prm-dashboard-metric__value{font-size:30px;line-height:1;font-weight:900;color:#0f172a;margin-top:6px}
    .prm-dashboard-metric__icon{width:46px;height:46px;border-radius:0;display:flex;align-items:center;justify-content:center;background:color-mix(in srgb,var(--metric-color,#2563eb) 16%,transparent);color:var(--metric-color,#2563eb);font-size:20px;border:1px solid color-mix(in srgb,var(--metric-color,#2563eb) 28%,transparent);flex:0 0 46px}
    .prm-dashboard-metric.roles{--metric-color:#2563eb}
    .prm-dashboard-metric.permissions{--metric-color:#7c3aed}
    .prm-dashboard-metric.critical{--metric-color:#dc2626}
    .prm-dashboard-metric.users{--metric-color:#16a34a}
</style>

<div class="mdm-wrap">
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if ($latestSuccessfulScan)
        <div class="prm-dashboard-grid">
            <div class="prm-dashboard-metric users">
                <div><div class="prm-dashboard-metric__label">Health</div><div class="prm-dashboard-metric__value">{{ $latestSuccessfulScan->health_status }}</div></div>
                <div class="prm-dashboard-metric__icon"><i class="fa-solid fa-heart-pulse"></i></div>
            </div>
            <div class="prm-dashboard-metric critical">
                <div><div class="prm-dashboard-metric__label">Risk score</div><div class="prm-dashboard-metric__value">{{ $latestSuccessfulScan->risk_score }}</div></div>
                <div class="prm-dashboard-metric__icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
            </div>
            <div class="prm-dashboard-metric roles">
                <div><div class="prm-dashboard-metric__label">Direct dependencies</div><div class="prm-dashboard-metric__value">{{ $latestSuccessfulScan->direct_dependencies_count }}</div></div>
                <div class="prm-dashboard-metric__icon"><i class="fa-solid fa-arrow-right"></i></div>
            </div>
            <div class="prm-dashboard-metric permissions">
                <div><div class="prm-dashboard-metric__label">Used by</div><div class="prm-dashboard-metric__value">{{ $latestSuccessfulScan->dependents_count }}</div></div>
                <div class="prm-dashboard-metric__icon"><i class="fa-solid fa-arrow-left"></i></div>
            </div>
            <div class="prm-dashboard-metric critical">
                <div><div class="prm-dashboard-metric__label">Circular</div><div class="prm-dashboard-metric__value">{{ $latestSuccessfulScan->circular_dependencies_count }}</div></div>
                <div class="prm-dashboard-metric__icon"><i class="fa-solid fa-rotate"></i></div>
            </div>
            <div class="prm-dashboard-metric roles">
                <div><div class="prm-dashboard-metric__label">Stale deps</div><div class="prm-dashboard-metric__value">{{ $latestSuccessfulScan->stale_dependencies_count }}</div></div>
                <div class="prm-dashboard-metric__icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
            </div>
        </div>
    @endif

    @if ($latestScan && $latestScan->status === 'failed')
        <div class="alert alert-danger">
            <strong>Last scan failed.</strong>
            {{ $latestScan->error_message }}
        </div>
    @endif

    <div class="card mdm-panel">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Dependencies used by {{ $module }}</strong>
            <span class="badge bg-secondary">{{ $dependencies->count() }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle mb-0 mdm-table">
                    <thead>
                        <tr>
                            <th>Depends on</th>
                            <th>Type</th>
                            <th>File</th>
                            <th>Line</th>
                            <th>Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($dependencies->count() > 0)
                            @foreach ($dependencies as $dependency)
                            <tr>
                                <td>
                                    <a href="{{ route($routeName . 'show', $dependency->target_module) }}">
                                        {{ $dependency->target_module }}
                                    </a>
                                </td>
                                <td>{{ $dependency->dependency_type }}</td>
                                <td><code>{{ $dependency->file_path }}</code></td>
                                <td>{{ $dependency->line_number }}</td>
                                <td><code>{{ $dependency->reference }}</code></td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="text-muted text-center py-4">
                                    No active dependencies found.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mdm-panel">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Modules impacted by changes in {{ $module }}</strong>
            <span class="badge bg-secondary">{{ $dependents->count() }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle mb-0 mdm-table">
                    <thead>
                        <tr>
                            <th>Dependent module</th>
                            <th>Type</th>
                            <th>File</th>
                            <th>Line</th>
                            <th>Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($dependents->count() > 0)
                            @foreach ($dependents as $dependent)
                            <tr>
                                <td>
                                    <a href="{{ route($routeName . 'show', $dependent->source_module) }}">
                                        {{ $dependent->source_module }}
                                    </a>
                                </td>
                                <td>{{ $dependent->dependency_type }}</td>
                                <td><code>{{ $dependent->file_path }}</code></td>
                                <td>{{ $dependent->line_number }}</td>
                                <td><code>{{ $dependent->reference }}</code></td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="text-muted text-center py-4">
                                    No modules depend on this module.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
