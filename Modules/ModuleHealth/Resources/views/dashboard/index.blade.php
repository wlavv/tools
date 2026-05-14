@extends('module-health::layouts.module')

@section('content')
@include('module-health::partials.styles')

<div class="mh-shell">
    @if(session('success'))
        <div class="alert alert-success mb-0">{{ session('success') }}</div>
    @endif

    <div class="mh-grid">
        <div class="mh-card mh-kpi prm-dashboard-metric roles">
            <div class="mh-kpi-top">
                <div class="label">Modules</div>
                <div class="prm-dashboard-metric__icon"><i class="fa-solid fa-cubes"></i></div>
            </div>
            <div>
                <div class="value">{{ $scan?->modules_total ?? 0 }}</div>
                <div class="hint">Registados no scan atual</div>
            </div>
        </div>
        <div class="mh-card mh-kpi prm-dashboard-metric critical">
            <div class="mh-kpi-top">
                <div class="label">Broken</div>
                <div class="prm-dashboard-metric__icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
            </div>
            <div>
                <div class="value">{{ $scan?->broken_total ?? 0 }}</div>
                <div class="hint">Componentes obrigatórios em falta</div>
            </div>
        </div>
        <div class="mh-card mh-kpi prm-dashboard-metric permissions">
            <div class="mh-kpi-top">
                <div class="label">Incomplete</div>
                <div class="prm-dashboard-metric__icon"><i class="fa-solid fa-circle-half-stroke"></i></div>
            </div>
            <div>
                <div class="value">{{ $scan?->incomplete_total ?? 0 }}</div>
                <div class="hint">Prontos para completar</div>
            </div>
        </div>
        <div class="mh-card mh-kpi prm-dashboard-metric users">
            <div class="mh-kpi-top">
                <div class="label">Functional + Enhanced</div>
                <div class="prm-dashboard-metric__icon"><i class="fa-solid fa-circle-check"></i></div>
            </div>
            <div>
                <div class="value">{{ ($scan?->functional_total ?? 0) + ($scan?->enhanced_total ?? 0) }}</div>
                <div class="hint">Operacionais</div>
            </div>
        </div>
    </div>

    <div class="mh-card mh-panel">
        <div class="mh-card-head">
            <div>
                <h5 class="mh-title">Module Health Overview</h5>
                <div class="mh-subtitle">Latest scan: {{ $scan?->finished_at?->format('Y-m-d H:i') ?? 'No scan yet' }}</div>
            </div>
            <form method="POST" action="{{ route('module_health.scan.run') }}">
                @csrf
                <button class="btn btn-outline-primary lsg-action-btn lsg-action-btn--primary">
                    <span class="lsg-action-btn__glow"></span>
                    <span class="lsg-action-btn__icon"><i class="fa-solid fa-rotate"></i></span>
                    <span class="lsg-action-btn__label">Run Scan</span>
                </button>
            </form>
        </div>

        @include('module-health::partials.module-table', ['items' => $items])
    </div>
</div>
@endsection
