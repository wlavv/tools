@extends(config('package_tracker.layout'))

@section('content')
@include('package-tracker::partials.flash')

<div class="row g-3 mb-4">
    @foreach([
        ['Total', $stats['total'], 'fa-boxes-stacked'],
        ['Active', $stats['active'], 'fa-truck-fast'],
        ['Delivered', $stats['delivered'], 'fa-circle-check'],
        ['Exceptions', $stats['exceptions'], 'fa-triangle-exclamation'],
        ['Stale', $stats['stale'], 'fa-clock'],
        ['Carriers', $stats['carriers'], 'fa-network-wired'],
    ] as [$label, $value, $icon])
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small">{{ $label }}</div>
                            <div class="h4 mb-0">{{ $value }}</div>
                        </div>
                        <i class="fa-solid {{ $icon }} fa-lg text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white"><strong>Status Distribution</strong></div>
            <div class="card-body">
                @forelse($byStatus as $row)
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span>{{ ucfirst(str_replace('_', ' ', $row->status)) }}</span>
                        <strong>{{ $row->total }}</strong>
                    </div>
                @empty
                    <p class="text-muted mb-0">No shipments yet.</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Recent Shipments</strong>
                <a href="{{ route('package_tracker.shipments.index') }}" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-eye me-1"></i>Show all</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Tracking</th><th>Carrier</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    @forelse($recentShipments as $shipment)
                        <tr>
                            <td>{{ $shipment->tracking_number }}<br><small class="text-muted">{{ $shipment->order_reference }}</small></td>
                            <td>{{ $shipment->carrier?->name }}</td>
                            <td><span class="{{ $shipment->statusEnum()->badgeClass() }}">{{ $shipment->statusEnum()->label() }}</span></td>
                            <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('package_tracker.shipments.show', $shipment) }}"><i class="fa-solid fa-eye"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-muted">No shipments found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
