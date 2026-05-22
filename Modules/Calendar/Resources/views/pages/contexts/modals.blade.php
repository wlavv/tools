@php
    $iconChoices = [
        'fa-solid fa-building' => 'Company',
        'fa-solid fa-users' => 'Team',
        'fa-solid fa-house-user' => 'Family',
        'fa-solid fa-umbrella-beach' => 'Vacation',
        'fa-solid fa-user-clock' => 'Absence',
        'fa-solid fa-car' => 'Travel',
    ];
@endphp

<div class="modal fade" id="calendarContextCreateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content calendar-modal-content">
            <form method="POST" action="{{ route('calendar.contexts.store') }}" class="calendar-modal-form">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">New Context</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @include('calendar::pages.contexts.form', [
                        'context' => null,
                        'iconChoices' => $iconChoices,
                    ])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-outline-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($contexts as $context)
    <div class="modal fade" id="calendarContextEditModal{{ $context->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content calendar-modal-content">
                <form method="POST" action="{{ route('calendar.contexts.update', $context) }}" class="calendar-modal-form">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Context</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @include('calendar::pages.contexts.form', [
                            'context' => $context,
                            'iconChoices' => $iconChoices,
                        ])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-outline-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="calendarContextDeleteModal{{ $context->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content calendar-modal-content">
                <form method="POST" action="{{ route('calendar.contexts.delete', $context) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Delete Context</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">This context has {{ $context->events_count }} events. Choose where to move them before deleting.</p>
                        <label class="form-label">Move events to</label>
                        <select class="form-select" name="move_context_id">
                            <option value="">No context</option>
                            @foreach($moveTargets->where('id', '!=', $context->id) as $target)
                                <option value="{{ $target->id }}">{{ $target->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-outline-danger"><i class="fa-solid fa-trash me-1"></i>Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
