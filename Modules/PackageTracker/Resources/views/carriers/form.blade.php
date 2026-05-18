@extends(config('package_tracker.layout'))
@section('content')
@php($editing = (bool) $carrier->id)
@php($credentialLabels = $carrier->code === 'ups' ? ['key' => 'UPS Client ID', 'secret' => 'UPS Client Secret'] : ['key' => 'API Key', 'secret' => 'API Secret'])
@include('package-tracker::partials.flash')
@include('package-tracker::partials.module-nav')

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form id="lsg-form" method="POST" action="{{ $editing ? route('package_tracker.carriers.update', $carrier) : route('package_tracker.carriers.store') }}" class="row g-3">
            @csrf
            @if($editing) @method('PUT') @endif
            <div class="col-md-3"><label class="form-label">Code</label><input name="code" value="{{ old('code', $carrier->code) }}" class="form-control" required></div>
            <div class="col-md-5"><label class="form-label">Name</label><input name="name" value="{{ old('name', $carrier->name) }}" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Driver Class</label><input name="driver" value="{{ old('driver', $carrier->driver ?: Modules\PackageTracker\Services\Carriers\ManualCarrierClient::class) }}" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">API Base URL</label><input name="api_base_url" value="{{ old('api_base_url', $carrier->api_base_url) }}" class="form-control"></div>
            <div class="col-md-3">
                <label class="form-label">{{ $credentialLabels['key'] }}</label>
                <input name="api_key" value="{{ old('api_key') }}" class="form-control" autocomplete="off">
                @if($editing && $carrier->api_key)
                    <small class="text-muted">Configured. Leave blank to keep current value.</small>
                @endif
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ $credentialLabels['secret'] }}</label>
                <input name="api_secret" value="{{ old('api_secret') }}" class="form-control" autocomplete="off">
                @if($editing && $carrier->api_secret)
                    <small class="text-muted">Configured. Leave blank to keep current value.</small>
                @endif
            </div>
            <div class="col-md-3 form-check ms-2"><input type="checkbox" name="is_active" value="1" class="form-check-input" {{ old('is_active', $carrier->is_active ?? true) ? 'checked' : '' }}><label class="form-check-label">Active</label></div>
            <div class="col-md-3 form-check"><input type="checkbox" name="supports_webhooks" value="1" class="form-check-input" {{ old('supports_webhooks', $carrier->supports_webhooks) ? 'checked' : '' }}><label class="form-check-label">Supports Webhooks</label></div>
        </form>
    </div>
</div>
@endsection
