@php($embeddedResources = !empty($embeddedResources))

<section @class(['wc-product-resources-block', 'wc-card wc-panel' => !$embeddedResources, 'wc-product-resources-block-embedded' => $embeddedResources]) id="resources">
    @unless($embeddedResources)
        <div class="wc-eyebrow">Recursos</div>
        <h2 class="wc-section-title">Resources</h2>
    @endunless

    <div class="wc-product-resource-summary">
        <a class="@if($images->count()) is-on @endif" href="#galleryMain">
            <i class="fa-solid fa-image"></i>
            <span><strong>{{ $images->count() }}</strong> Imagens</span>
        </a>
        @if($model3d || !empty($card3d))
            <a class="is-on" href="#viewer">
                <i class="fa-solid fa-cube"></i>
                <span><strong>1</strong> 3D</span>
            </a>
        @else
            <span>
                <i class="fa-solid fa-cube"></i>
                <span><strong>0</strong> 3D</span>
            </span>
        @endif
        <span class="@if($videos->count()) is-on @endif">
            <i class="fa-solid fa-video"></i>
            <span><strong>{{ $videos->count() }}</strong> Video</span>
        </span>
        <span class="@if($documents->count()) is-on @endif">
            <i class="fa-solid fa-file-lines"></i>
            <span><strong>{{ $documents->count() }}</strong> Docs</span>
        </span>
    </div>

    @if($documents->count())
        <div class="wc-resource-list">
            @foreach($documents as $resource)
            <a class="wc-resource-item" href="{{ $resource->resolved_url }}" target="_blank" rel="noopener">
                <span><strong>{{ $resource->title ?: ucfirst(str_replace('_', ' ', $resource->resource_type)) }}</strong><br><small>{{ strtoupper($resource->extension ?? '') }} {{ $resource->file_size ? ' - '.number_format($resource->file_size / 1024, 0).' KB' : '' }}</small></span>
                <span><i class="fa-solid fa-up-right-from-square"></i></span>
            </a>
            @endforeach
        </div>
    @endif

    @if($videos->count())
        <h3 class="wc-section-subtitle">Videos</h3>
        <div class="wc-resource-list">
            @foreach($videos as $video)
                <a class="wc-resource-item" href="{{ $video->resolved_url }}" target="_blank" rel="noopener"><strong>{{ $video->title ?: 'Video' }}</strong><span><i class="fa-solid fa-play"></i></span></a>
            @endforeach
        </div>
    @endif

    @if($audio->count())
        <h3 class="wc-section-subtitle">Audio</h3>
        <div class="wc-resource-list">
            @foreach($audio as $track)
                <div class="wc-resource-item"><strong>{{ $track->title ?: 'Audio' }}</strong>@if($track->resolved_url)<audio controls preload="none" src="{{ $track->resolved_url }}"></audio>@endif</div>
            @endforeach
        </div>
    @endif
</section>
