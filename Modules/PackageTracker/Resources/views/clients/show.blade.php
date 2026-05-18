@extends(config('package_tracker.layout'))
@section('content')
@include('package-tracker::partials.flash')
@include('package-tracker::partials.module-nav')

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white"><strong>Client account</strong></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5">Name</dt><dd class="col-7">{{ $client->name }}</dd>
                    <dt class="col-5">Key</dt><dd class="col-7"><code>{{ $client->client_key }}</code></dd>
                    <dt class="col-5">Email</dt><dd class="col-7">{{ $client->contact_email ?: '-' }}</dd>
                    <dt class="col-5">Status</dt><dd class="col-7">{{ $client->is_active ? 'Active' : 'Inactive' }}</dd>
                    <dt class="col-5">Portal</dt><dd class="col-7">@if($client->publicUrl())<a href="{{ $client->publicUrl() }}" target="_blank" rel="noopener">Open</a>@else-@endif</dd>
                    <dt class="col-5">Carriers</dt><dd class="col-7">{{ $enabledCarriers ? implode(', ', $enabledCarriers) : '-' }}</dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Shipments</strong>
                <a href="{{ route('package_tracker.shipments.create', ['client_key' => $client->client_key]) }}" class="lsg-action-btn lsg-action-btn--success lsg-action-btn--compact"><i class="fa-solid fa-plus"></i><span>New shipment</span></a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Tracking</th><th>Carrier</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                    @forelse($client->shipments as $shipment)
                        <tr>
                            <td>{{ $shipment->tracking_number }}<br><small class="text-muted">{{ $shipment->order_reference }}</small></td>
                            <td>{{ $shipment->carrier?->name }}</td>
                            <td><span class="{{ $shipment->statusEnum()->badgeClass() }}">{{ $shipment->statusEnum()->label() }}</span></td>
                            <td class="text-end"><a class="lsg-action-btn lsg-action-btn--primary lsg-action-btn--compact" href="{{ route('package_tracker.shipments.show', $shipment) }}"><i class="fa-solid fa-eye"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-muted">No shipments for this client.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
