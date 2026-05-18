@extends(config('package_tracker.layout'))
@section('content')
@php($publicUrl = $shipment->publicUrl())
@include('package-tracker::partials.flash')
@include('package-tracker::partials.module-nav')

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white"><strong>Shipment</strong></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5">Carrier</dt><dd class="col-7">{{ $shipment->carrier?->name }}</dd>
                    <dt class="col-5">Client</dt><dd class="col-7">{{ $shipment->client?->name ?: '-' }}</dd>
                    <dt class="col-5">Status</dt><dd class="col-7"><span class="{{ $shipment->statusEnum()->badgeClass() }}">{{ $shipment->statusEnum()->label() }}</span></dd>
                    <dt class="col-5">Order</dt><dd class="col-7">{{ $shipment->order_reference ?: '-' }}</dd>
                    <dt class="col-5">Store</dt><dd class="col-7">{{ $shipment->store_code ?: '-' }}</dd>
                    <dt class="col-5">Customer</dt><dd class="col-7">{{ $shipment->customer_email ?: '-' }}</dd>
                    <dt class="col-5">Country</dt><dd class="col-7">{{ $shipment->destination_country ?: '-' }}</dd>
                    <dt class="col-5">Postal code</dt><dd class="col-7">{{ data_get($shipment->metadata, 'destination_postal_code') ?: '-' }}</dd>
                    <dt class="col-5">Location</dt><dd class="col-7">{{ $shipment->last_location ?: '-' }}</dd>
                    <dt class="col-5">ETA</dt><dd class="col-7">{{ optional($shipment->estimated_delivery_at)->format('Y-m-d H:i') ?: '-' }}</dd>
                    <dt class="col-5">SLA</dt><dd class="col-7">{{ optional($shipment->sla_due_at)->format('Y-m-d H:i') ?: '-' }}</dd>
                    <dt class="col-5">Public link</dt><dd class="col-7">@if($publicUrl)<a href="{{ $publicUrl }}" target="_blank" rel="noopener">Open</a>@else-@endif</dd>
                    <dt class="col-5">Last poll</dt><dd class="col-7">{{ optional($shipment->last_polled_at)->format('Y-m-d H:i') ?: '-' }}</dd>
                    <dt class="col-5">Poll attempts</dt><dd class="col-7">{{ $shipment->poll_attempts }}</dd>
                    @if(data_get($shipment->metadata, 'last_poll_error'))
                        <dt class="col-5">Last error</dt><dd class="col-7 text-danger small">{{ data_get($shipment->metadata, 'last_poll_error') }}</dd>
                    @endif
                </dl>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white"><strong>Timeline</strong></div>
            <div class="card-body">
                @forelse($shipment->events as $event)
                    <div class="border-start ps-3 pb-3 position-relative">
                        <div><strong>{{ ucfirst(str_replace('_', ' ', $event->normalized_status)) }}</strong> <small class="text-muted">{{ optional($event->event_at)->format('Y-m-d H:i') }}</small></div>
                        <div>{{ $event->description }}</div>
                        <small class="text-muted">{{ $event->location }}</small>
                    </div>
                @empty
                    <p class="text-muted mb-0">No tracking events yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
