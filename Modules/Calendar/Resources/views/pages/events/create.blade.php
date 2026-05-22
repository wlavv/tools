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
                <form method="POST" action="{{ route('calendar.events.store') }}" id="lsg-form">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Context</label>
                            <select class="form-select" name="context_id">
                                <option value="">-- none --</option>
                                @foreach($contexts as $context)
                                    <option value="{{ $context->id }}" @selected((string) old('context_id') === (string) $context->id)>{{ $context->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category_id">
                                <option value="">-- none --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Title</label>
                            <input class="form-control" name="title" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Location</label>
                            <input class="form-control" name="location">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Start</label>
                            <input class="form-control" name="start_at" type="datetime-local" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">End</label>
                            <input class="form-control" name="end_at" type="datetime-local">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <input class="form-control" name="status" value="active">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">All Day</label>
                            <select class="form-select" name="all_day"><option value="0">No</option><option value="1">Yes</option></select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Reference Type</label>
                            <input class="form-control" name="reference_type" value="{{ old('reference_type') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Reference ID</label>
                            <input class="form-control" name="reference_id" type="number" value="{{ old('reference_id') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="5"></textarea>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
@endsection
