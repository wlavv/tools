@php
    $context = $context ?? null;
    $selectedIcon = old('icon', $context?->icon ?: 'fa-solid fa-building');
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Name</label>
        <input class="form-control" name="name" value="{{ old('name', $context?->name) }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Slug</label>
        <input class="form-control" name="slug" value="{{ old('slug', $context?->slug) }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Color</label>
        <input class="form-control calendar-color-field" name="color" type="color" value="{{ old('color', $context?->color ?: '#0d6efd') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Order</label>
        <input class="form-control" name="sort_order" type="number" value="{{ old('sort_order', $context?->sort_order ?? 0) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Active</label>
        <select class="form-select" name="is_active">
            <option value="1" @selected((bool) old('is_active', $context?->is_active ?? true))>Yes</option>
            <option value="0" @selected(! (bool) old('is_active', $context?->is_active ?? true))>No</option>
        </select>
    </div>
    <div class="col-12">
        <label class="form-label">Icon</label>
        <div class="calendar-icon-choice">
            @foreach($iconChoices as $icon => $label)
                <label>
                    <input type="radio" name="icon" value="{{ $icon }}" @checked($selectedIcon === $icon)>
                    <i class="{{ $icon }}"></i>
                    <span>{{ $label }}</span>
                </label>
            @endforeach
        </div>
    </div>
</div>
