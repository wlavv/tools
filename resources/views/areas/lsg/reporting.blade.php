@extends('layouts.app')

@push('styles')
    @include('areas.lsg.partials.section-cards-css')
@endpush

@section('content')
    <div class="lsg-section-panel">
        <div class="lsg-section-head">
            <div>
                <span>LSG</span>
                <h3>Reporting</h3>
            </div>
        </div>

        <div class="lsg-section-grid">
            <section class="lsg-section-card">
                <div class="lsg-section-card__head"><i class="fa-solid fa-gauge-high"></i><strong>PageSpeed</strong></div>
                <span>Relatórios diários de performance dos sites do grupo.</span>
                <div class="lsg-section-actions">
                    @if(Route::has('lsg.site_manager.dashboard'))<a class="btn btn-sm btn-outline-primary" href="{{ route('lsg.site_manager.dashboard') }}">Abrir</a>@endif
                </div>
            </section>

            <section class="lsg-section-card">
                <div class="lsg-section-card__head"><i class="fa-solid fa-plug-circle-check"></i><strong>Integration Health</strong></div>
                <span>Estado e disponibilidade de integrações críticas.</span>
                <div class="lsg-section-actions">
                    @if(Route::has('integration_health.dashboard'))<a class="btn btn-sm btn-outline-primary" href="{{ route('integration_health.dashboard') }}">Abrir</a>@endif
                </div>
            </section>

            <section class="lsg-section-card">
                <div class="lsg-section-card__head"><i class="fa-solid fa-clipboard-list"></i><strong>Audit Log</strong></div>
                <span>Rastreabilidade operacional e histórico de ações.</span>
                <div class="lsg-section-actions">
                    @if(Route::has('audit_log_central.dashboard'))<a class="btn btn-sm btn-outline-primary" href="{{ route('audit_log_central.dashboard') }}">Abrir</a>@endif
                </div>
            </section>
        </div>
    </div>
@endsection
