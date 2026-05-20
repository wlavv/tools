@php
    $status = (string) ($status ?? 'pending');
    $class = match ($status) {
        'approved', 'passed', 'available', 'completed' => 'success',
        'approved_with_warnings', 'warning', 'changes_required', 'manual_review_required' => 'warning',
        'rejected', 'failed', 'error', 'unavailable' => 'danger',
        'processing' => 'primary',
        default => 'secondary',
    };
@endphp
<span class="badge bg-{{ $class }}">{{ __('module-compliance-center::module-compliance-center.statuses.' . $status) }}</span>
