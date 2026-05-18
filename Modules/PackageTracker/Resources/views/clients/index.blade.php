@extends(config('package_tracker.layout'))
@section('content')
@include('package-tracker::partials.flash')
@include('package-tracker::partials.module-nav')

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Client</th><th>Key</th><th>Email</th><th>Shipments</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @forelse($clients as $client)
                <tr>
                    <td><strong>{{ $client->name }}</strong></td>
                    <td><code>{{ $client->client_key }}</code></td>
                    <td>{{ $client->contact_email ?: '-' }}</td>
                    <td>{{ $client->shipments_count }}</td>
                    <td><span class="badge {{ $client->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $client->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td class="text-end">
                        @if($client->publicUrl())
                            <a class="lsg-action-btn lsg-action-btn--neutral lsg-action-btn--compact" href="{{ $client->publicUrl() }}" target="_blank" rel="noopener" title="Public portal"><i class="fa-solid fa-up-right-from-square"></i></a>
                        @endif
                        <a class="lsg-action-btn lsg-action-btn--primary lsg-action-btn--compact" href="{{ route('package_tracker.clients.show', $client) }}" title="Show"><i class="fa-solid fa-eye"></i></a>
                        <a class="lsg-action-btn lsg-action-btn--warning lsg-action-btn--compact" href="{{ route('package_tracker.clients.edit', $client) }}" title="Edit"><i class="fa-solid fa-pencil"></i></a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-muted">No client accounts yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $clients->links() }}</div>
</div>
@endsection
