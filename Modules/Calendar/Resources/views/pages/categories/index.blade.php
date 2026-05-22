@extends('layouts.app')

@push('styles')
    @include('calendar::includes.css')
@endpush

@section('content')
<div class="lsg-content px-0 calendar-shell">
    @include('calendar::partials.nav')

    <main class="calendar-content">
        <div class="card calendar-card mb-3">
            <div class="card-body p-3 p-md-4">
                <form method="POST" action="{{ route('calendar.categories.store') }}">
                    @csrf
                    <div class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label">Context</label>
                            <select class="form-select" name="context_id">
                                <option value="">-- none --</option>
                                @foreach($contexts as $context)
                                    <option value="{{ $context->id }}">{{ $context->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2"><label class="form-label">Name</label><input class="form-control" name="name" required></div>
                        <div class="col-md-2"><label class="form-label">Slug</label><input class="form-control" name="slug" required></div>
                        <div class="col-md-2"><label class="form-label">Color</label><input class="form-control calendar-color-field" name="color" type="color" value="#0d6efd"></div>
                        <div class="col-md-2"><label class="form-label">Icon</label><input class="form-control" name="icon"></div>
                        <div class="col-md-1"><label class="form-label">Order</label><input class="form-control" name="sort_order" type="number" value="0"></div>
                        <div class="col-md-1"><label class="form-label">Active</label><select class="form-select" name="is_active"><option value="1">Yes</option><option value="0">No</option></select></div>
                        <div class="col-md-12"><button class="btn btn-outline-primary"><i class="fa-solid fa-plus me-1"></i>Add category</button></div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card calendar-card">
            <div class="card-body p-3 p-md-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 lsg-datatable">
                        <thead><tr><th>Name</th><th>Context</th><th>Slug</th><th>Actions</th></tr></thead>
                        <tbody>
                            @forelse($categories as $category)
                                <tr>
                                    <td>{{ $category->name }}</td>
                                    <td>{{ $category->context?->name }}</td>
                                    <td><span class="calendar-context-dot" style="background-color: {{ $category->color ?: '#0d6efd' }}"></span> {{ $category->slug }}</td>
                                    <td style="min-width:360px;">
                                        <form method="POST" action="{{ route('calendar.categories.update', $category) }}" class="calendar-form-compact">
                                            @csrf
                                            <select class="form-select" name="context_id">
                                                <option value="">-- none --</option>
                                                @foreach($contexts as $context)
                                                    <option value="{{ $context->id }}" @selected($category->context_id === $context->id)>{{ $context->name }}</option>
                                                @endforeach
                                            </select>
                                            <input class="form-control" name="name" value="{{ $category->name }}" required>
                                            <input class="form-control" name="slug" value="{{ $category->slug }}" required>
                                            <input class="form-control calendar-color-field" name="color" type="color" value="{{ $category->color ?: '#0d6efd' }}">
                                            <input class="form-control" name="icon" value="{{ $category->icon }}">
                                            <div class="row g-2">
                                                <div class="col"><input class="form-control" name="sort_order" type="number" value="{{ $category->sort_order }}"></div>
                                                <div class="col"><select class="form-select" name="is_active"><option value="1" @selected($category->is_active)>Yes</option><option value="0" @selected(!$category->is_active)>No</option></select></div>
                                            </div>
                                            <button class="btn btn-outline-primary btn-sm mt-2"><i class="fa-solid fa-floppy-disk me-1"></i>Save</button>
                                        </form>
                                        <form method="POST" action="{{ route('calendar.categories.delete', $category) }}" class="mt-2" onsubmit="return confirm('Delete this category?')">
                                            @csrf
                                            <button class="btn btn-outline-danger btn-sm"><i class="fa-solid fa-trash me-1"></i>Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-muted">No categories found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection
