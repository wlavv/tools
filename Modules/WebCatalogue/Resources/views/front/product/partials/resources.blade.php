<section class="wc-card wc-panel wc-product-resources-block" id="resources">
    <div class="wc-eyebrow">Extra resources</div>
    <h2 class="wc-section-title">Resources</h2>
    <div class="wc-resource-list">
        @forelse($documents as $resource)
            <a class="wc-resource-item" href="{{ $resource->resolved_url }}" target="_blank" rel="noopener">
                <span><strong>{{ $resource->title ?: ucfirst(str_replace('_', ' ', $resource->resource_type)) }}</strong><br><small>{{ strtoupper($resource->extension ?? '') }} {{ $resource->file_size ? ' · '.number_format($resource->file_size / 1024, 0).' KB' : '' }}</small></span>
                <span><i class="fa-solid fa-up-right-from-square"></i></span>
            </a>
        @empty
            <div class="wc-empty">No public documents available.</div>
        @endforelse
    </div>
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
