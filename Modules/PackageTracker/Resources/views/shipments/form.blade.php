@extends(config('package_tracker.layout'))
@section('content')
@php($editing = (bool) $shipment->id)
@php($postalCode = old('destination_postal_code', data_get($shipment->metadata, 'destination_postal_code')))
@include('package-tracker::partials.flash')
@include('package-tracker::partials.module-nav')

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form id="lsg-form" method="POST" action="{{ $editing ? route('package_tracker.shipments.update', $shipment) : route('package_tracker.shipments.store') }}" class="row g-3">
            @csrf
            @if($editing) @method('PUT') @endif
            <div class="col-md-4">
                <label class="form-label">Client</label>
                <select name="client_key" class="form-select">
                    <option value="">No client</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->client_key }}" @selected(old('client_key', $shipment->client_key ?: request('client_key')) === $client->client_key)>{{ $client->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Carrier</label>
                <select name="carrier_id" class="form-select" required>
                    @foreach($carriers as $carrier)
                        <option value="{{ $carrier->id }}" @selected((int) old('carrier_id', $shipment->carrier_id) === $carrier->id)>{{ $carrier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4"><label class="form-label">Tracking Number</label><input name="tracking_number" value="{{ old('tracking_number', $shipment->tracking_number) }}" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Order Reference</label><input name="order_reference" value="{{ old('order_reference', $shipment->order_reference) }}" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">External Reference</label><input name="external_reference" value="{{ old('external_reference', $shipment->external_reference) }}" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Store Code</label><input name="store_code" value="{{ old('store_code', $shipment->store_code) }}" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Customer Email</label><input name="customer_email" type="email" value="{{ old('customer_email', $shipment->customer_email) }}" class="form-control"></div>
            <div class="col-md-2"><label class="form-label">Destination Country</label><input name="destination_country" maxlength="2" value="{{ old('destination_country', $shipment->destination_country ?: config('package_tracker.default_destination_country', 'PT')) }}" class="form-control" placeholder="PT"></div>
            <div class="col-md-3"><label class="form-label">Postal Code</label><input name="destination_postal_code" value="{{ $postalCode }}" class="form-control" placeholder="1000000"></div>
            <div class="col-md-3"><label class="form-label">SLA Due At</label><input name="sla_due_at" type="datetime-local" value="{{ old('sla_due_at', optional($shipment->sla_due_at)->format('Y-m-d\\TH:i')) }}" class="form-control"></div>
        </form>
    </div>
</div>
@endsection
