@extends('layouts.app')
@include('calendar::includes.css')
@include('calendar::includes.js')

@section('content')
<div class="container-fluid px-0">
    <div class="card calendar-card mb-3">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                <div>
                    <h3 class="mb-1">Calendar</h3>
                    <div class="calendar-muted">Calendário central por contexto.</div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('calendar.contexts.index') }}" class="btn btn-outline-primary"><i class="fa-solid fa-layer-group me-1"></i>Contexts</a>
                    <a href="{{ route('calendar.categories.index') }}" class="btn btn-outline-primary"><i class="fa-solid fa-tags me-1"></i>Categories</a>
                    <a href="{{ route('calendar.events.create') }}" class="btn btn-outline-primary"><i class="fa-solid fa-plus me-1"></i>New Event</a>
                </div>
            </div>

            <form method="GET" action="{{ route('calendar.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Context</label>
                        <select class="form-select" name="context">
                            <option value="">All contexts</option>
                            @foreach($contexts as $context)
                                <option value="{{ $context->slug }}" @selected($selectedContext === $context->slug)>{{ $context->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-auto">
                        <button class="btn btn-outline-primary" type="submit"><i class="fa-solid fa-filter me-1"></i>Filter</button>
                    </div>
                    <div class="col-md-auto">
                        <a href="{{ route('calendar.tablet', ['context' => $selectedContext ?: 'family']) }}" class="btn btn-outline-primary"><i class="fa-solid fa-tablet-screen-button me-1"></i>Tablet</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card calendar-card">
        <div class="card-body p-3 p-md-4">
            <div class="table-responsive">
                <table class="table table-hover calendar-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Start</th>
                            <th>Title</th>
                            <th>Context</th>
                            <th>Category</th>
                            <th>Location</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events as $event)
                            <tr>
                                <td>{{ optional($event->start_at)->format('Y-m-d H:i') }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $event->title }}</div>
                                    @if($event->description)
                                        <div class="calendar-muted">{{ $event->description }}</div>
                                    @endif
                                </td>
                                <td>{{ $event->context?->name }}</td>
                                <td>{{ $event->category?->name }}</td>
                                <td>{{ $event->location }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-muted">No events found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $events->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
