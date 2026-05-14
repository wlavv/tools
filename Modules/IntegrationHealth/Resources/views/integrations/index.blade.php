@extends(config('integration-health.layout'))

@section('content')
@include('integration-health::partials.styles')
<div class="">
    <div class="ih-shell">
        @include('integration-health::partials.sidebar')
        <div class="ih-card">
            <table class="ih-table">
                <thead><tr><th>Status</th><th>Name</th><th>Type</th><th>Score</th><th>Last Error</th><th>Enabled</th><th></th></tr></thead>
                <tbody>
                    @foreach($services as $service)
                        <tr>
                            <td><span class="ih-status-dot ih-dot-{{ $service->status }}"></span> {{ $service->status }}</td>
                            <td><strong>{{ $service->name }}</strong><br><span class="ih-muted">{{ $service->slug }}</span></td>
                            <td>{{ $service->type }}</td>
                            <td>{{ $service->health_score }}%</td>
                            <td>{{ $service->last_error_message ?: '—' }}</td>
                            <td>{{ $service->is_enabled ? 'Yes' : 'No' }}</td>
                            <td class="text-end"><a class="ih-btn ih-btn-warning" href="{{ route('integration_health.integrations.edit', $service) }}"><i class="fa-solid fa-pencil"></i> Edit</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $services->links() }}
        </div>
    </div>
</div>
@endsection
