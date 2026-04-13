@extends('layouts.app')
@include('calendar::includes.css')

@section('content')
<div class="container-fluid px-0">
    <div class="card calendar-card mb-3">
        <div class="card-body p-3 p-md-4 d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h3 class="mb-1">Calendar Events</h3>
                <div class="calendar-muted">Lista completa dos eventos do módulo.</div>
            </div>
            <div>
                <a href="{{ route('calendar.events.create') }}" class="btn btn-outline-primary"><i class="fa-solid fa-plus me-1"></i>New Event</a>
            </div>
        </div>
    </div>

    <div class="card calendar-card">
        <div class="card-body p-3 p-md-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Start</th><th>Title</th><th>Context</th><th>Category</th><th>Actions</th></tr></thead>
                    <tbody>
                        @forelse($events as $event)
                            <tr>
                                <td>{{ optional($event->start_at)->format('Y-m-d H:i') }}</td>
                                <td>{{ $event->title }}</td>
                                <td>{{ $event->context?->name }}</td>
                                <td>{{ $event->category?->name }}</td>
                                <td>
                                    <a href="{{ route('calendar.events.show', $event) }}" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-eye me-1"></i>Show</a>
                                    <form method="POST" action="{{ route('calendar.events.delete', $event) }}" class="d-inline">
                                        @csrf
                                        <button class="btn btn-outline-danger btn-sm"><i class="fa-solid fa-trash me-1"></i>Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-muted">No events found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $events->links() }}</div>
        </div>
    </div>
</div>
@endsection
