@php
    $class = [
        'broken' => 'mh-broken',
        'incomplete' => 'mh-incomplete',
        'functional' => 'mh-functional',
        'enhanced' => 'mh-enhanced',
    ][$status] ?? 'mh-functional';
@endphp
<span class="mh-badge {{ $class }}">{{ __('module-health::messages.status.' . $status) }}</span>
