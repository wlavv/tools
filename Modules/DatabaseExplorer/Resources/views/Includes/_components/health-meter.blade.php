@php
    $scoreValue = max(0, min(100, (int) ($score ?? 0)));
    $statusValue = $status ?? 'healthy';
@endphp

<div class="dbx-health-meter">
    <div class="dbx-health-meter__bar">
        <div class="dbx-health-meter__fill dbx-health-meter__fill--{{ $statusValue }}" style="width: {{ $scoreValue }}%"></div>
    </div>
    <div class="dbx-health-meter__label">{{ $scoreValue }}/100 · {{ ucfirst($statusValue) }}</div>
</div>
