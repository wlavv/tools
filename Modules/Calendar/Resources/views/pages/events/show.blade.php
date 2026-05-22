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
                <form method="POST" action="{{ route('calendar.events.update', $event) }}" id="lsg-form">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Context</label>
                            <select class="form-select" name="context_id">
                                <option value="">-- none --</option>
                                @foreach($contexts as $context)
                                    <option value="{{ $context->id }}" @selected($event->context_id === $context->id)>{{ $context->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category_id">
                                <option value="">-- none --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" @selected($event->category_id === $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Title</label>
                            <input class="form-control" name="title" value="{{ $event->title }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Location</label>
                            <input class="form-control" name="location" value="{{ $event->location }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Start</label>
                            <input class="form-control" name="start_at" type="datetime-local" value="{{ optional($event->start_at)->format('Y-m-d\\TH:i') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">End</label>
                            <input class="form-control" name="end_at" type="datetime-local" value="{{ optional($event->end_at)->format('Y-m-d\\TH:i') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <input class="form-control" name="status" value="{{ $event->status }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">All Day</label>
                            <select class="form-select" name="all_day"><option value="0" @selected(!$event->all_day)>No</option><option value="1" @selected($event->all_day)>Yes</option></select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Reference Type</label>
                            <input class="form-control" name="reference_type" value="{{ $event->reference_type }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Reference ID</label>
                            <input class="form-control" name="reference_id" type="number" value="{{ $event->reference_id }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="5">{{ $event->description }}</textarea>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
@endsection
