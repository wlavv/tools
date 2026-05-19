@php
    $statuses = config('idealab.statuses');
    $priorities = config('idealab.priorities');
    $sources = config('idealab.sources');
    $tagValue = old('tags', $idea->exists ? $idea->tags->pluck('name')->implode(', ') : '');
@endphp

<div class="row g-3">
    <div class="col-md-8">
        <div class="card idealab-card">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $idea->title) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Raw description</label>
                    <textarea name="description_raw" class="form-control" rows="8">{{ old('description_raw', $idea->description_raw) }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tags</label>
                    <input type="text" name="tags" class="form-control" value="{{ $tagValue }}" placeholder="AI, Laravel, PrestaShop, SaaS">
                    <div class="form-text">Comma-separated tags.</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card idealab-card idealab-soft">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">No category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $idea->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}" @selected(old('status', $idea->status ?: 'draft') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-select">
                        @foreach($priorities as $key => $label)
                            <option value="{{ $key }}" @selected(old('priority', $idea->priority ?: 'medium') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Source</label>
                    <select name="source" class="form-select">
                        @foreach($sources as $key => $label)
                            <option value="{{ $key }}" @selected(old('source', $idea->source ?: 'manual') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>
