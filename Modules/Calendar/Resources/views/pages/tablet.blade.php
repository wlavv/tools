@extends('layouts.app')
@include('calendar::includes.css')

@section('content')
<div class="lsg-content px-0">
    @include('calendar::partials.nav')

    <div class="card calendar-card mb-3">
        <div class="card-body p-3 p-md-4 d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h3 class="mb-1">Tablet Calendar</h3>
                <div class="calendar-muted">Context: {{ $context?->name ?? $contextSlug }}</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('calendar.feed', ['context' => $contextSlug]) }}" class="btn btn-outline-primary"><i class="fa-solid fa-code me-1"></i>Feed</a>
                <a href="{{ route('calendar.index', ['context' => $contextSlug]) }}" class="btn btn-outline-primary"><i class="fa-solid fa-angle-left me-1"></i>Back</a>
            </div>
        </div>
    </div>

    <div class="card calendar-card">
        <div class="card-body p-3 p-md-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 lsg-datatable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Event</th>
                            <th>Category</th>
                            <th>Location</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events as $event)
                            <tr>
                                <td>{{ optional($event->start_at)->format('d/m H:i') }}</td>
                                <td>{{ $event->title }}</td>
                                <td>{{ $event->category?->name }}</td>
                                <td>{{ $event->location }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-muted">No events found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

