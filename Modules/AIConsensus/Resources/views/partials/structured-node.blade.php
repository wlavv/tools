@php
    $nodeValue = $value ?? null;
    $nodeKey = $nodeKey ?? null;
    $depth = (int) ($depth ?? 0);
    $isList = is_array($nodeValue) && array_is_list($nodeValue);
    $isAssoc = is_array($nodeValue) && !array_is_list($nodeValue);
    $label = $nodeKey !== null ? str($nodeKey)->replace(['_', '-'], ' ')->headline() : null;
@endphp

@if($isAssoc)
    <div class="ai-structured-section {{ $depth > 0 ? 'ai-structured-section--nested' : '' }}">
        @if($label)
            <h6>{{ $label }}</h6>
        @endif
        <div class="ai-structured-grid">
            @foreach($nodeValue as $key => $item)
                @if(is_array($item))
                    <div class="ai-structured-block">
                        @include('ai-consensus::partials.structured-node', ['nodeKey' => $key, 'value' => $item, 'depth' => $depth + 1])
                    </div>
                @else
                    <div class="ai-structured-field">
                        <span>{{ str($key)->replace(['_', '-'], ' ')->headline() }}</span>
                        <strong>{!! nl2br(e((string) $item)) !!}</strong>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
@elseif($isList)
    @php
        $scalarItems = collect($nodeValue)->every(fn ($item) => !is_array($item));
    @endphp
    <div class="ai-structured-section {{ $depth > 0 ? 'ai-structured-section--nested' : '' }}">
        @if($label)
            <h6>{{ $label }}</h6>
        @endif
        @if($scalarItems)
            <ul class="ai-structured-list">
                @foreach($nodeValue as $item)
                    <li>{!! nl2br(e((string) $item)) !!}</li>
                @endforeach
            </ul>
        @else
            <div class="ai-structured-list-cards">
                @foreach($nodeValue as $index => $item)
                    <div class="ai-structured-list-card">
                        @include('ai-consensus::partials.structured-node', ['nodeKey' => is_array($item) ? ($item['title'] ?? $item['name'] ?? '#' . ($index + 1)) : '#' . ($index + 1), 'value' => $item, 'depth' => $depth + 1])
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@else
    <div class="ai-structured-text">
        @if($label)
            <span>{{ $label }}</span>
        @endif
        <p>{!! nl2br(e((string) $nodeValue)) !!}</p>
    </div>
@endif
