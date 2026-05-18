@extends(config('package_tracker.layout'))
@section('content')
@include('package-tracker::partials.flash')
@include('package-tracker::partials.module-nav')
@php($editing = (bool) $client->id)

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form id="lsg-form" method="POST" action="{{ $editing ? route('package_tracker.clients.update', $client) : route('package_tracker.clients.store') }}" class="row g-3">
            @csrf
            @if($editing) @method('PUT') @endif

            <div class="col-md-5"><label class="form-label">Name</label><input name="name" value="{{ old('name', $client->name) }}" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Client key</label><input name="client_key" value="{{ old('client_key', $client->client_key) }}" class="form-control" placeholder="auto-generated"></div>
            <div class="col-md-3"><label class="form-label">Contact email</label><input name="contact_email" type="email" value="{{ old('contact_email', $client->contact_email) }}" class="form-control"></div>
            <div class="col-12 form-check ms-2"><input type="checkbox" name="is_active" value="1" class="form-check-input" {{ old('is_active', $client->is_active ?? true) ? 'checked' : '' }}><label class="form-check-label">Active</label></div>

            <div class="col-12"><hr></div>
            <div class="col-md-4"><label class="form-label">Brand name</label><input name="theme[brand_name]" value="{{ old('theme.brand_name', $client->theme['brand_name'] ?? '') }}" class="form-control"></div>
            <div class="col-md-8"><label class="form-label">Logo URL</label><input name="theme[logo_url]" value="{{ old('theme.logo_url', $client->theme['logo_url'] ?? '') }}" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Primary color</label><input name="theme[primary_color]" value="{{ old('theme.primary_color', $client->theme['primary_color'] ?? '#0f766e') }}" class="form-control" placeholder="#0f766e"></div>
            <div class="col-md-4"><label class="form-label">Accent color</label><input name="theme[accent_color]" value="{{ old('theme.accent_color', $client->theme['accent_color'] ?? '#2563eb') }}" class="form-control" placeholder="#2563eb"></div>
            <div class="col-md-4"><label class="form-label">Background color</label><input name="theme[background_color]" value="{{ old('theme.background_color', $client->theme['background_color'] ?? '#f8fafc') }}" class="form-control" placeholder="#f8fafc"></div>

            <div class="col-12"><hr></div>
            <div class="col-12">
                <label class="form-label">Enabled carriers</label>
                <div class="row g-2">
                    @foreach($carriers as $carrier)
                        <div class="col-md-3">
                            <label class="form-check">
                                <input class="form-check-input" type="checkbox" name="carrier_codes[]" value="{{ $carrier->code }}" {{ in_array($carrier->code, old('carrier_codes', $enabledCarrierCodes), true) ? 'checked' : '' }}>
                                <span class="form-check-label">{{ $carrier->name }}</span>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
