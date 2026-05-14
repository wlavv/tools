@extends(config('integration-health.layout'))

@section('content')
@include('integration-health::partials.styles')
<div class="">
    <div class="ih-shell">
        @include('integration-health::partials.sidebar')
        <div class="ih-card">
            <form method="GET" class="d-flex gap-2 mb-3">
                <select name="status" class="form-select" style="max-width:180px"><option value="">All</option><option value="open" @selected(request('status')==='open')>Open</option><option value="resolved" @selected(request('status')==='resolved')>Resolved</option></select>
                <select name="severity" class="form-select" style="max-width:180px"><option value="">Any severity</option>@foreach(config('integration-health.severities') as $severity)<option value="{{ $severity }}" @selected(request('severity')===$severity)>{{ $severity }}</option>@endforeach</select>
                <button class="ih-btn ih-btn-primary"><i class="fa-solid fa-filter"></i> Filter</button>
            </form>
            <table class="ih-table">
                <thead><tr><th>Severity</th><th>Service</th><th>Event</th><th>Message</th><th>Date</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @foreach($events as $event)
                    <tr>
                        <td><span class="ih-badge ih-badge-{{ $event->severity_color }}">{{ strtoupper($event->severity) }}</span></td>
                        <td>{{ optional($event->service)->name ?? $event->service_slug }}</td>
                        <td><strong>{{ $event->title }}</strong><br><span class="ih-muted">{{ $event->event_type }}</span></td>
                        <td>{{ $event->message ?: '—' }}</td>
                        <td>{{ $event->created_at->format('Y-m-d H:i') }}</td>
                        <td>{{ $event->resolved_at ? 'Resolved' : 'Open' }}</td>
                        <td class="text-end">
                            @unless($event->resolved_at)
                                <form method="POST" action="{{ route('integration_health.events.resolve', $event) }}">@csrf<button class="ih-btn ih-btn-success"><i class="fa-solid fa-check"></i> Resolve</button></form>
                            @endunless
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            {{ $events->links() }}
        </div>
    </div>
</div>
@endsection
