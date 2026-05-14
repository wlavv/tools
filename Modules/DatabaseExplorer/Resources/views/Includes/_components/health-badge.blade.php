@php
    $statusValue = $status ?? 'healthy';
    $scoreValue = isset($score) ? (int) $score : null;
@endphp

<span class="dbx-badge dbx-badge--{{ $statusValue }}">
    {{ config('database-explorer.ui.statuses.' . $statusValue, ucfirst($statusValue)) }}
    @if($scoreValue !== null)
        · {{ $scoreValue }}
    @endif
</span>
