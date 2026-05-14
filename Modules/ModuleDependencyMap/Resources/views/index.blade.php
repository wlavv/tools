@extends(config('module-dependency-map.layout', 'layouts.app'))

@section('content')
<?php $routeName = config('module-dependency-map.route_name', 'module-dependency-map.'); ?>

<style>
    .mdm-wrap{display:flex;flex-direction:column;gap:12px}
    .mdm-panel{border-radius:0!important;border:1px solid rgba(148,163,184,.25)!important;background:var(--bg-card,rgba(255,255,255,.04));box-shadow:0 8px 24px rgba(15,23,42,.08)}
    .mdm-panel .card-body,.mdm-panel .card-header{border-radius:0!important}
    .mdm-table th{font-size:12px;text-transform:uppercase;color:#64748b;font-weight:800;letter-spacing:.04em}
    .mdm-table td,.mdm-table th{vertical-align:middle}
    .mdm-table tbody tr.mdm-freshness-today > *{background:rgba(22,163,74,.12)!important}
    .mdm-table tbody tr.mdm-freshness-recent > *{background:rgba(245,158,11,.16)!important}
    .mdm-table tbody tr.mdm-freshness-stale > *{background:rgba(220,38,38,.12)!important}
    .mdm-table tbody tr.mdm-freshness-never > *{background:transparent!important}
    .mdm-legend{padding:12px 16px;display:flex;align-items:center;gap:16px;flex-wrap:wrap;color:#64748b;font-size:12px}
    .mdm-legend-item{display:inline-flex;align-items:center;gap:8px;white-space:nowrap}
    .mdm-legend-color{width:18px;height:10px;border:1px solid rgba(148,163,184,.28);display:inline-block}
    .mdm-legend-color.never{background:transparent}
    .mdm-legend-color.today{background:rgba(22,163,74,.12)}
    .mdm-legend-color.recent{background:rgba(245,158,11,.16)}
    .mdm-legend-color.stale{background:rgba(220,38,38,.12)}
</style>

<div class="mdm-wrap">
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mdm-panel">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0 mdm-table">
                    <thead>
                        <tr>
                            <th>Module</th>
                            <th>Last successful scan</th>
                            <th>Health</th>
                            <th>Risk</th>
                            <th>Depends on</th>
                            <th>Used by</th>
                            <th>Last scan status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (count($items) > 0)
                            @foreach ($items as $item)
                            <?php
                                $scan = $item['latest_successful_scan'];
                                $latestScan = $item['latest_scan'];
                                $freshness = $item['freshness'] ?? 'never';
                            ?>

                            <tr class="{{ $item['row_class'] }} mdm-freshness-{{ $freshness }}">
                                <td><strong>{{ $item['name'] }}</strong></td>
                                <td>
                                    @if ($scan?->finished_at)
                                        <div>{{ $scan->finished_at->format('d/m/Y H:i') }}</div>
                                    @else
                                        <div class="text-muted">Never tested</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $item['health_badge_class'] }}">
                                        {{ $scan?->health_status ?? 'unknown' }}
                                    </span>
                                </td>
                                <td>{{ $scan?->risk_score ?? '-' }}</td>
                                <td>{{ $item['outgoing_count'] }}</td>
                                <td>{{ $item['incoming_count'] }}</td>
                                <td>
                                    @if ($latestScan)
                                        <?php
                                            $statusClass = match ($latestScan->status) {
                                                'success' => 'bg-success',
                                                'failed' => 'bg-danger',
                                                default => 'bg-secondary',
                                            };
                                        ?>
                                        <span class="badge {{ $statusClass }}">{{ $latestScan->status }}</span>
                                    @else
                                        <span class="badge bg-secondary">not tested</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route($routeName . 'show', $item['name']) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>

                                    <form method="POST" action="{{ route($routeName . 'run', $item['name']) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            <i class="fa-solid fa-rotate"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    No modules found. Check config('module-dependency-map.modules_path').
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mdm-panel mdm-legend">
        <span class="mdm-legend-item"><span class="mdm-legend-color never"></span><strong>Sem background:</strong> nunca testado</span>
        <span class="mdm-legend-item"><span class="mdm-legend-color today"></span><strong>Verde:</strong> verificado hoje</span>
        <span class="mdm-legend-item"><span class="mdm-legend-color recent"></span><strong>Amarelo:</strong> verificado nos ultimos {{ $freshDays }} dias</span>
        <span class="mdm-legend-item"><span class="mdm-legend-color stale"></span><strong>Vermelho:</strong> verificado ha mais de {{ $freshDays }} dias</span>
    </div>
</div>
@endsection
