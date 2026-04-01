@extends('roadmap-manager::layouts.page')

@section('roadmap-content')
<h1 class="h3 mb-3">{{ $group->exists ? 'Edit Group' : 'New Group' }}</h1>
<form method="POST" action="{{ $group->exists ? route('roadmap.groups.update', $group->id) : route('roadmap.groups.store') }}">
    @csrf
    @if($group->exists) @method('PUT') @endif
    <div class="card shadow-sm">
        <div class="card-body row g-3">
            <div class="col-md-6">
                <label class="form-label">Name</label>
                <input name="name" class="form-control" value="{{ old('name', $group->name) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Slug</label>
                <input name="slug" class="form-control" value="{{ old('slug', $group->slug) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Color</label>
                <input name="color" class="form-control" value="{{ old('color', $group->color ?: '#6366f1') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Icon</label>
                <input name="icon" class="form-control" value="{{ old('icon', $group->icon) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    @foreach(['active','archived','planning'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $group->status ?: 'active') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Sort Order</label>
                <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $group->sort_order ?: 0) }}">
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4">{{ old('description', $group->description) }}</textarea>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('roadmap.groups.index') }}" class="btn btn-outline-primary"><i class="fa-solid fa-angle-left"></i> Back</a>
            <button class="btn btn-outline-primary"><i class="fa-solid fa-floppy-disk"></i> Save</button>
        </div>
    </div>
</form>
@endsection
