@if(isset($actions) && is_array($actions) && count($actions) > 0)
    <div class="topbar-actions">
        @foreach ($actions as $key => $action)
            @php
                $btnClass = $action['class'] ?? 'btn-outline-light';
                $btnLabel = $action['label'] ?? $action['name'] ?? null;
            @endphp

            @if(isset($action['onclick']))
                <button type="button" class="btn {{ $btnClass }} btn-dimensions topbar-action-btn" onclick="{{ $action['onclick'] }}">
                    @if(isset($action['icon'])){!! $action['icon'] !!}@endif
                    @if($btnLabel)<span class="topbar-action-label">{{ $btnLabel }}</span>@endif
                </button>
            @else
                <a class="btn {{ $btnClass }} btn-dimensions topbar-action-btn" href="{{ $action['url'] ?? '#' }}">
                    @if(isset($action['icon'])){!! $action['icon'] !!}@endif
                    @if($btnLabel)<span class="topbar-action-label">{{ $btnLabel }}</span>@endif
                </a>
            @endif
        @endforeach
    </div>
@endif
