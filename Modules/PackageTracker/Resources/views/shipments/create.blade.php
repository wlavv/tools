@extends(config('package_tracker.layout'))
@section('content')
@include('package-tracker::partials.flash')
@include('package-tracker::partials.module-nav')

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form id="lsg-form" method="POST" action="{{ route('package_tracker.shipments.store') }}" class="row g-3">
            @csrf
            <div class="col-md-4"><label class="form-label">Client</label><select name="client_key" class="form-select"><option value="">No client</option>@foreach($clients as $client)<option value="{{ $client->client_key }}" @selected(old('client_key', request('client_key')) === $client->client_key)>{{ $client->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label">Carrier</label><select name="carrier_id" class="form-select" required>@foreach($carriers as $carrier)<option value="{{ $carrier->id }}">{{ $carrier->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label">Tracking Number</label><input name="tracking_number" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Order Reference</label><input name="order_reference" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">External Reference</label><input name="external_reference" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Store Code</label><input name="store_code" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Customer Email</label><input name="customer_email" type="email" class="form-control"></div>
            <div class="col-md-3"><label class="form-label">Destination Country</label><input name="destination_country" maxlength="2" class="form-control" placeholder="PT"></div>
            <div class="col-md-3"><label class="form-label">SLA Due At</label><input name="sla_due_at" type="datetime-local" class="form-control"></div>
        </form>
    </div>
</div>
@endsection
