@php
    $map = [
        'success' => 'success', 'ok' => 'success',
        'failed' => 'danger', 'critical' => 'danger', 'danger' => 'danger',
        'processing' => 'info', 'pending' => 'warning', 'retrying' => 'warning', 'warning' => 'warning',
    ];
    $variant = $map[$status ?? 'info'] ?? 'info';
@endphp
<span class="jqm-badge jqm-badge-{{ $variant }}">{{ strtoupper($status ?? 'info') }}</span>
