@extends(config('integration-health.layout'))

@section('content')
@include('integration-health::partials.styles')

<div class="">
    <div class="ih-shell">
        @include('integration-health::partials.sidebar')
        <div>
            <div class="ih-grid mb-3">
                <div class="ih-card ih-kpi prm-dashboard-metric roles"><div><div class="ih-kpi-label">System Score</div><div class="ih-kpi-value">{{ $summary['system_score'] }}%</div><div class="ih-score"><span style="width:{{ $summary['system_score'] }}%"></span></div></div><div class="prm-dashboard-metric__icon"><i class="fa-solid fa-gauge-high"></i></div></div>
                <div class="ih-card ih-kpi prm-dashboard-metric permissions"><div><div class="ih-kpi-label">Services</div><div class="ih-kpi-value">{{ $summary['services_total'] }}</div><div class="ih-kpi-sub">enabled integrations</div></div><div class="prm-dashboard-metric__icon"><i class="fa-solid fa-plug-circle-bolt"></i></div></div>
                <div class="ih-card ih-kpi prm-dashboard-metric users"><div><div class="ih-kpi-label">Online</div><div class="ih-kpi-value">{{ $summary['online'] }}</div><div class="ih-kpi-sub">healthy services</div></div><div class="prm-dashboard-metric__icon"><i class="fa-solid fa-circle-check"></i></div></div>
                <div class="ih-card ih-kpi prm-dashboard-metric roles"><div><div class="ih-kpi-label">Degraded</div><div class="ih-kpi-value">{{ $summary['degraded'] }}</div><div class="ih-kpi-sub">needs attention</div></div><div class="prm-dashboard-metric__icon"><i class="fa-solid fa-circle-half-stroke"></i></div></div>
                <div class="ih-card ih-kpi prm-dashboard-metric critical"><div><div class="ih-kpi-label">Offline</div><div class="ih-kpi-value">{{ $summary['offline'] }}</div><div class="ih-kpi-sub">critical impact</div></div><div class="prm-dashboard-metric__icon"><i class="fa-solid fa-triangle-exclamation"></i></div></div>
                <div class="ih-card ih-kpi prm-dashboard-metric critical"><div><div class="ih-kpi-label">Open Events</div><div class="ih-kpi-value">{{ $summary['open_events'] }}</div><div class="ih-kpi-sub">{{ $summary['critical_events'] }} critical/fatal</div></div><div class="prm-dashboard-metric__icon"><i class="fa-solid fa-bell"></i></div></div>
            </div>

            <div class="row g-3">
                <div class="col-lg-8">
                    <div class="ih-card">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h4 class="mb-0">Service Health</h4>
                            <a href="{{ route('integration_health.integrations.index') }}" class="ih-btn ih-btn-primary"><i class="fa-solid fa-eye"></i> View all</a>
                        </div>
                        <table class="ih-table">
                            <thead><tr><th>Status</th><th>Service</th><th>Type</th><th>Score</th><th>Latency</th><th>Last Seen</th><th>Open</th></tr></thead>
                            <tbody>
                            @forelse($services as $service)
                                <tr>
                                    <td><span class="ih-status-dot ih-dot-{{ $service->status }}"></span></td>
                                    <td><strong>{{ $service->name }}</strong><br><span class="ih-muted">{{ $service->slug }}</span></td>
                                    <td>{{ strtoupper($service->type) }}</td>
                                    <td><strong>{{ $service->health_score }}%</strong><div class="ih-score"><span style="width:{{ $service->health_score }}%"></span></div></td>
                                    <td>{{ $service->avg_response_time_ms ? $service->avg_response_time_ms . 'ms' : '—' }}</td>
                                    <td>{{ $service->last_seen_at ? $service->last_seen_at->diffForHumans() : 'never' }}</td>
                                    <td><span class="ih-badge ih-badge-{{ $service->critical_events_count ? 'danger' : ($service->open_events_count ? 'warning' : 'success') }}">{{ $service->open_events_count }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="7">No services configured.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="ih-card">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h4 class="mb-0">Incident Timeline</h4>
                            <a href="{{ route('integration_health.events.index', ['status' => 'open']) }}" class="ih-btn ih-btn-warning"><i class="fa-solid fa-triangle-exclamation"></i> Events</a>
                        </div>
                        <div class="ih-timeline">
                            @forelse($openEvents as $event)
                                <div class="ih-event {{ $event->severity }}">
                                    <div class="ih-event-title">{{ $event->title }}</div>
                                    <div class="ih-muted">{{ strtoupper($event->severity) }} · {{ optional($event->service)->name ?? $event->service_slug }} · {{ $event->created_at->diffForHumans() }}</div>
                                    @if($event->message)<div class="mt-1">{{ $event->message }}</div>@endif
                                </div>
                            @empty
                                <div class="ih-muted">No open incidents.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
