@extends('site-manager::layouts.module')

@section('module-content')
    <div class="sm-grid mb-3">
        @foreach([
            'sites' => ['label' => 'Sites', 'icon' => 'fa-solid fa-globe'],
            'stores' => ['label' => 'Lojas', 'icon' => 'fa-solid fa-store'],
            'services' => ['label' => 'Servicos', 'icon' => 'fa-solid fa-briefcase'],
            'presentation' => ['label' => 'Apresentacao', 'icon' => 'fa-solid fa-display'],
        ] as $key => $item)
            <div class="sm-card sm-stat">
                <span class="sm-stat__icon"><i class="{{ $item['icon'] }}"></i></span>
                <div>
                    <small>{{ $item['label'] }}</small>
                    <strong>{{ $stats[$key] ?? 0 }}</strong>
                </div>
            </div>
        @endforeach
    </div>

    <div class="sm-card">
        <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap mb-3">
            <h5 class="mb-0">Relatorio diario PageSpeed</h5>
            <a class="sm-btn" href="{{ route('lsg.site_manager.sites.index') }}"><i class="fa-solid fa-globe"></i> Ver sites</a>
        </div>
        <div class="sm-table-wrap">
            <table class="sm-table">
                <thead><tr><th>Site</th><th>Tipo</th><th>Mobile</th><th>Desktop</th><th>Estado</th><th></th></tr></thead>
                <tbody>
                @forelse($sites as $site)
                    @php
                        $mobile = $pageSpeedMetricsByStrategy['mobile']->get($site->id);
                        $desktop = $pageSpeedMetricsByStrategy['desktop']->get($site->id);
                        $scoreColor = fn ($score) => $score === null ? '#94a3b8' : ($score >= 90 ? '#22c55e' : ($score >= 50 ? '#f59e0b' : '#ef4444'));
                    @endphp
                    <tr>
                        <td><div class="sm-site-title"><strong>{{ $site->name }}</strong><span>{{ $site->domain ?: $site->public_url ?: '-' }}</span></div></td>
                        <td><span class="sm-badge">{{ config('site-manager.site_types.' . $site->site_type, $site->site_type) }}</span></td>
                        <td><span class="sm-score" style="--score-color:{{ $scoreColor($mobile?->performance_score) }}">{{ $mobile?->performance_score ?? '-' }}</span></td>
                        <td><span class="sm-score" style="--score-color:{{ $scoreColor($desktop?->performance_score) }}">{{ $desktop?->performance_score ?? '-' }}</span></td>
                        <td><span class="sm-badge">{{ $site->status }}</span></td>
                        <td><a class="sm-btn" href="{{ route('lsg.site_manager.sites.show', $site) }}"><i class="fa-solid fa-eye"></i></a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Sem sites registados.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
