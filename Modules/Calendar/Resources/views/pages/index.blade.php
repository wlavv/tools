@extends('layouts.app')
@include('calendar::includes.css')
@include('calendar::includes.js')

@section('content')
<div class="lsg-content px-0">
    @include('calendar::partials.nav')

    <div class="card calendar-card mb-3">
        <div class="card-body p-3 p-md-4">
            <div class="calendar-context-filter">
                <a href="{{ route('calendar.index') }}" class="{{ empty($selectedContext) ? 'is-active' : '' }}">
                    <i class="fa-solid fa-border-all"></i>All contexts
                </a>
                @foreach($contexts as $context)
                    <a href="{{ route('calendar.index', ['context' => $context->slug]) }}" class="{{ $selectedContext === $context->slug ? 'is-active' : '' }}">
                        <span class="calendar-context-dot" style="background-color: {{ $context->color ?: '#0d6efd' }}"></span>{{ $context->name }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card calendar-card">
        <div class="card-body p-3 p-md-4">
            <div class="table-responsive">
                <table class="table table-hover calendar-table align-middle mb-0 lsg-datatable">
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
        </div>
    </div>
</div>
@endsection
