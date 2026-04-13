@extends('layouts.app')
@include('calendar::includes.css')

@section('content')
<div class="container-fluid px-0">
    <div class="card calendar-card">
        <div class="card-body p-3 p-md-4">
            <h3 class="mb-1">Create Event</h3>
            <div class="calendar-muted mb-3">Novo evento do calendário.</div>

            <form method="POST" action="{{ route('calendar.events.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Context</label>
                        <select class="form-select" name="context_id">
                            <option value="">-- none --</option>
                            @foreach($contexts as $context)
                                <option value="{{ $context->id }}">{{ $context->name }}</option>
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
                        <input class="form-control" name="reference_type">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Reference ID</label>
                        <input class="form-control" name="reference_id" type="number">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="5"></textarea>
                    </div>
                </div>

                <div class="mt-3">
                    <button class="btn btn-outline-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
