@extends(config('package_tracker.layout'))
@section('content')
@include('package-tracker::partials.flash')
@include('package-tracker::partials.module-nav')

<div class="card shadow-sm border-0 mb-3">
    <div class="card-body">
        <form class="row g-2">
            <div class="col-md-4"><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Search tracking, order, customer..."></div>
            <div class="col-md-3">
                <select name="client_key" class="form-select">
                    <option value="">All clients</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->client_key }}" @selected(request('client_key') === $client->client_key)>{{ $client->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3"><input name="status" value="{{ request('status') }}" class="form-control" placeholder="Status"></div>
            <div class="col-md-2"><button class="btn btn-outline-primary w-100"><i class="fa-solid fa-magnifying-glass me-1"></i>Filter</button></div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="packageTrackerShipmentsTable">
            <thead><tr><th>Tracking</th><th>Carrier</th><th>Order</th><th>Status</th><th>Last event</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @foreach($shipments as $shipment)
                <tr>
                    <td><strong>{{ $shipment->tracking_number }}</strong><br><small class="text-muted">{{ $shipment->external_reference }}</small></td>
                    <td>{{ $shipment->carrier?->name }}<br><small class="text-muted">{{ $shipment->client?->name }}</small></td>
                    <td>{{ $shipment->order_reference ?: '-' }}</td>
                    <td><span class="{{ $shipment->statusEnum()->badgeClass() }}">{{ $shipment->statusEnum()->label() }}</span></td>
                    <td>{{ optional($shipment->last_event_at)->format('Y-m-d H:i') ?: '-' }}</td>
                    <td class="text-end">
                        @if($shipment->publicUrl())
                            <a class="lsg-action-btn lsg-action-btn--neutral lsg-action-btn--compact" href="{{ $shipment->publicUrl() }}" target="_blank" rel="noopener" title="Public page"><i class="fa-solid fa-up-right-from-square"></i></a>
                        @endif
                        <form method="POST" action="{{ route('package_tracker.shipments.sync', $shipment) }}" class="d-inline">@csrf<button class="lsg-action-btn lsg-action-btn--primary lsg-action-btn--compact" title="Sync"><i class="fa-solid fa-rotate"></i></button></form>
                        <a class="lsg-action-btn lsg-action-btn--primary lsg-action-btn--compact" href="{{ route('package_tracker.shipments.show', $shipment) }}" title="Show"><i class="fa-solid fa-eye"></i></a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $shipments->links() }}</div>
</div>
@endsection
