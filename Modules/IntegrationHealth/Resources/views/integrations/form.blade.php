@extends(config('integration-health.layout'))

@section('content')
@include('integration-health::partials.styles')
@php($isEdit = $service->exists)
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div><h1 class="mb-1">{{ $isEdit ? 'Edit Integration' : 'New Integration' }}</h1><div class="text-muted">Configuração base do serviço monitorizado.</div></div>
        <a class="ih-btn ih-btn-primary" href="{{ route('integration_health.integrations.index') }}"><i class="fa-solid fa-angle-left"></i> Back</a>
    </div>
    <div class="ih-shell">
        @include('integration-health::partials.sidebar')
        <div class="ih-card">
            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ $isEdit ? route('integration_health.integrations.update', $service) : route('integration_health.integrations.store') }}">
                @csrf
                @if($isEdit) @method('PUT') @endif
                <div class="ih-form-grid">
                    <div class="ih-field"><label>Slug</label><input name="slug" value="{{ old('slug', $service->slug) }}" required></div>
                    <div class="ih-field"><label>Name</label><input name="name" value="{{ old('name', $service->name) }}" required></div>
                    <div class="ih-field"><label>Type</label><input name="type" value="{{ old('type', $service->type ?: 'api') }}" required></div>
                    <div class="ih-field"><label>Status</label><select name="status">@foreach(config('integration-health.statuses') as $status)<option value="{{ $status }}" @selected(old('status', $service->status ?: 'unknown') === $status)>{{ $status }}</option>@endforeach</select></div>
                    <div class="ih-field"><label>Health Score</label><input type="number" min="0" max="100" name="health_score" value="{{ old('health_score', $service->health_score ?? 100) }}" required></div>
                    <div class="ih-field"><label>Enabled</label><select name="is_enabled"><option value="1" @selected(old('is_enabled', $service->is_enabled ?? true))>Yes</option><option value="0" @selected(!old('is_enabled', $service->is_enabled ?? true))>No</option></select></div>
                </div>
                <div class="mt-3 ih-actions"><button class="ih-btn ih-btn-primary" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save</button></div>
            </form>
        </div>
    </div>
</div>
@endsection
