@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell">
@if(session('success'))<div class="wc-alert">{{ session('success') }}</div>@endif

<div class="wc-card">
    <div class="wc-list-toolbar">
        <div>
            <span class="wc-eyebrow"><i class="fa-solid fa-photo-film"></i> Assets</span>
            <h3>Resources</h3>
            <p class="wc-muted">Images, audio, video, documents, 3D models, AR and VR files.</p>
        </div>
    </div>
    <div class="wc-rich-list">
        @forelse($items as $item)
            @php $url = $item->resolved_url; @endphp
            <div class="wc-rich-card">
                <div class="wc-rich-media">
                    @if($item->is_image && $url)<img src="{{ $url }}" alt="{{ $item->title }}">@else<i class="{{ $item->icon }}"></i>@endif
                    <span class="wc-resource-type-tag">{{ $item->resource_type ?? 'resource' }}</span>
                </div>
                <div class="wc-rich-body">
                    <div class="wc-rich-title">
                        <h4><a href="{{ route('webcatalogue.resources.show', $item) }}">{{ $item->title ?? $item->filename ?? 'Resource #'.$item->id }}</a></h4>
                        <span class="wc-badge wc-status-{{ $item->status ?? 'active' }}">{{ $item->status ?? 'active' }}</span>
                    </div>
                    <div class="wc-rich-description">{{ $item->description ?: ($item->filename ?: 'No description defined yet.') }}</div>
                    <div class="wc-rich-meta">
                        <span class="wc-rich-metric"><i class="fa-solid fa-store"></i>{{ $item->store->name ?? 'No store' }}</span>
                        @if($item->product)<span class="wc-rich-metric"><i class="fa-solid fa-box"></i>{{ $item->product->reference ?? $item->product->name }}</span>@endif
                        @if($item->catalogue)<span class="wc-rich-metric"><i class="fa-solid fa-book-open"></i>{{ $item->catalogue->name }}</span>@endif
                        <span class="wc-rich-metric"><i class="fa-solid fa-file"></i>{{ strtoupper($item->extension ?? 'file') }}</span>
                        @if($item->file_size)<span class="wc-rich-metric"><i class="fa-solid fa-hard-drive"></i>{{ number_format($item->file_size / 1024, 1, ',', ' ') }} KB</span>@endif
                    </div>
                </div>
                <div class="wc-rich-actions">
                    <a class="wc-action-link" href="{{ route('webcatalogue.resources.show', $item) }}"><i class="fa-solid fa-eye"></i> View</a>
                    <a class="wc-action-link" href="{{ route('webcatalogue.resources.edit', $item) }}"><i class="fa-solid fa-pencil"></i> Edit</a>
                </div>
            </div>
        @empty
            <div class="wc-list-empty"><i class="fa-solid fa-photo-film"></i><div><strong>No resources yet.</strong><br><span>Upload images, 3D assets, AR/VR files, audio or documents.</span></div></div>
        @endforelse
    </div>
    @if($items instanceof \Illuminate\Contracts\Pagination\Paginator || $items instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
        <div class="wc-pagination">{{ $items->links() }}</div>
    @endif
</div>
</div>
@endsection
