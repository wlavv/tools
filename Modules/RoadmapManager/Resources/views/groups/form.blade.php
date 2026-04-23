@extends('layouts.app')

@section('content')
@include('roadmap-manager::partials.styles')
@include('roadmap-manager::partials.alerts')
<form method="POST" action="{{ $group->exists ? route('roadmap_manager.groups.update', $group->id) : route('roadmap_manager.groups.store') }}">
    @csrf
    @if($group->exists) @method('PUT') @endif
    <div class="rm-form-card">
        <div class="rm-form-grid">
            <div><label class="rm-label">Name</label><input name="name" class="rm-input" value="{{ old('name', $group->name) }}" required></div>
            <div><label class="rm-label">Slug</label><input name="slug" class="rm-input" value="{{ old('slug', $group->slug) }}"></div>
            <div><label class="rm-label">Color</label><input name="color" class="rm-input" value="{{ old('color', $group->color ?: '#6366f1') }}"></div>
            <div><label class="rm-label">Icon</label><input name="icon" class="rm-input" value="{{ old('icon', $group->icon) }}"></div>
            <div><label class="rm-label">Status</label><select name="status" class="rm-select">@foreach(['active','archived','planning'] as $status)<option value="{{ $status }}" @selected(old('status', $group->status ?: 'active') === $status)>{{ $status }}</option>@endforeach</select></div>
            <div><label class="rm-label">Sort Order</label><input type="number" name="sort_order" class="rm-input" value="{{ old('sort_order', $group->sort_order ?: 0) }}"></div>
            <div class="rm-form-grid__full"><label class="rm-label">Description</label><textarea name="description" class="rm-textarea" rows="4">{{ old('description', $group->description) }}</textarea></div>
        </div>
    </div>
</form>
@endsection
