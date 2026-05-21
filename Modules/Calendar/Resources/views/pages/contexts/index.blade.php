@extends('layouts.app')
@include('calendar::includes.css')

@section('content')
<div class="lsg-content px-0">
    @include('calendar::partials.nav')

    <div class="card calendar-card mb-3">
        <div class="card-body p-3 p-md-4">
            <form method="POST" action="{{ route('calendar.contexts.store') }}">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-md-3"><label class="form-label">Name</label><input class="form-control" name="name" required></div>
                    <div class="col-md-2"><label class="form-label">Slug</label><input class="form-control" name="slug" required></div>
                    <div class="col-md-2"><label class="form-label">Color</label><input class="form-control calendar-color-field" name="color" type="color" value="#0d6efd"></div>
                    <div class="col-md-2"><label class="form-label">Icon</label><input class="form-control" name="icon"></div>
                    <div class="col-md-1"><label class="form-label">Order</label><input class="form-control" name="sort_order" type="number" value="0"></div>
                    <div class="col-md-1"><label class="form-label">Active</label><select class="form-select" name="is_active"><option value="1">Yes</option><option value="0">No</option></select></div>
                    <div class="col-md-1"><button class="btn btn-outline-primary w-100"><i class="fa-solid fa-plus"></i></button></div>
                </div>
            </form>
        </div>
    </div>

    <div class="card calendar-card">
        <div class="card-body p-3 p-md-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 lsg-datatable">
                    <thead><tr><th>Name</th><th>Slug</th><th>Color</th><th>Actions</th></tr></thead>
                    <tbody>
                        @forelse($contexts as $context)
                            <tr>
                                <td>{{ $context->name }}</td>
                                <td>{{ $context->slug }}</td>
                                <td><span class="calendar-context-dot" style="background-color: {{ $context->color ?: '#0d6efd' }}"></span> {{ $context->color }}</td>
                                <td style="min-width:340px;">
                                    <form method="POST" action="{{ route('calendar.contexts.update', $context) }}" class="calendar-form-compact">
                                        @csrf
                                        <input class="form-control" name="name" value="{{ $context->name }}" required>
                                        <input class="form-control" name="slug" value="{{ $context->slug }}" required>
                                        <input class="form-control calendar-color-field" name="color" type="color" value="{{ $context->color ?: '#0d6efd' }}">
                                        <input class="form-control" name="icon" value="{{ $context->icon }}">
                                        <div class="row g-2">
                                            <div class="col"><input class="form-control" name="sort_order" type="number" value="{{ $context->sort_order }}"></div>
                                            <div class="col"><select class="form-select" name="is_active"><option value="1" @selected($context->is_active)>Yes</option><option value="0" @selected(!$context->is_active)>No</option></select></div>
                                        </div>
                                        <button class="btn btn-outline-primary btn-sm mt-2"><i class="fa-solid fa-floppy-disk me-1"></i>Save</button>
                                    </form>
                                    <form method="POST" action="{{ route('calendar.contexts.delete', $context) }}" class="mt-2" onsubmit="return confirm('Delete this context?')">
                                        @csrf
                                        <button class="btn btn-outline-danger btn-sm"><i class="fa-solid fa-trash me-1"></i>Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-muted">No contexts found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
