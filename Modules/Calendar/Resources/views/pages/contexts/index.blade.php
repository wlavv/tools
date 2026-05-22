@extends('layouts.app')

@push('styles')
    @include('calendar::includes.css')
@endpush

@push('scripts')
    @include('calendar::includes.js')
@endpush

@section('content')
<div class="lsg-content px-0 calendar-shell">
    @include('calendar::partials.nav')

    <main class="calendar-content">
        <div class="card calendar-card">
            <div class="card-body p-3 p-md-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 lsg-datatable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Color</th>
                                <th>Icon</th>
                                <th>Events</th>
                                <th>Categories</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($contexts as $context)
                                <tr>
                                    <td>{{ $context->name }}</td>
                                    <td>{{ $context->slug }}</td>
                                    <td><span class="calendar-context-dot" style="background-color: {{ $context->color ?: '#0d6efd' }}"></span> {{ $context->color }}</td>
                                    <td><i class="{{ $context->icon ?: 'fa-solid fa-calendar-days' }}"></i></td>
                                    <td>{{ $context->events_count }}</td>
                                    <td>{{ $context->categories_count }}</td>
                                    <td><span class="badge {{ $context->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $context->is_active ? 'Active' : 'Off' }}</span></td>
                                    <td class="text-end">
                                        <div class="calendar-context-actions">
                                            <button type="button" class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#calendarContextEditModal{{ $context->id }}" title="Edit context">
                                                <i class="fa-solid fa-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#calendarContextDeleteModal{{ $context->id }}" title="Delete context">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-muted">No contexts found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

@include('calendar::pages.contexts.modals', [
    'contexts' => $contexts,
    'moveTargets' => $moveTargets,
])
@endsection
