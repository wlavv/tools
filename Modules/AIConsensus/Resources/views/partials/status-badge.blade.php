@php
    $statusClass = [
        'pending' => 'secondary',
        'processing' => 'info',
        'waiting_user_input' => 'warning',
        'completed' => 'success',
        'failed' => 'danger',
        'cancelled' => 'dark',
        'archived' => 'light',
    ][$status ?? 'pending'] ?? 'secondary';
@endphp
<span class="badge bg-{{ $statusClass }}">{{ $status }}</span>
