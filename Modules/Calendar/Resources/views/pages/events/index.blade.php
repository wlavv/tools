@extends('layouts.app')

@push('styles')
    @include('calendar::includes.css')
@endpush

@section('content')
<div class="lsg-content px-0 calendar-shell">
    @include('calendar::partials.nav')

    <main class="calendar-content">
        <div class="card calendar-card">
            <div class="card-body p-3 p-md-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 lsg-datatable">
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
                                        <form method="POST" action="{{ route('calendar.events.delete', $event) }}" class="d-inline" onsubmit="return confirm('Delete this event?')">
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
            </div>
        </div>
    </main>
</div>
@endsection
