@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell">
<div class="wc-card">
    <div class="wc-list-toolbar">
        <div>
            <span class="wc-eyebrow"><i class="fa-solid fa-camera"></i> Visual Recognition</span>
            <h3>Sessions</h3>
            <p class="wc-muted">Review queue separated by recognition status. Confirm suggested products directly from the list.</p>
        </div>
    </div>

    <div class="wc-recognition-tabs">
        @foreach($groups as $key => $group)
            <a class="wc-recognition-tab {{ $activeGroup === $key ? 'is-active' : '' }}" href="{{ route('webcatalogue.recognition.sessions.index', ['group' => $key]) }}">
                <span>{{ $group['label'] }}</span>
                <strong>{{ $groupCounts[$key] ?? 0 }}</strong>
            </a>
        @endforeach
    </div>

    <div class="wc-rich-list">
        @forelse($items as $item)
            @php($suggestions = $item->matches->whereNotNull('product')->sortBy('rank')->take(3))
            @php($capture = $item->captures->firstWhere('capture_type', 'object_photo') ?: $item->captures->first())
            <div class="wc-rich-card">
                <div class="wc-rich-media wc-session-capture-media">
                    @if($capture?->resolved_url)
                        <img src="{{ $capture->resolved_url }}" alt="Session capture">
                    @else
                        <i class="fa-solid fa-camera-retro"></i>
                    @endif
                </div>
                <div class="wc-rich-body">
                    <div class="wc-rich-title">
                        <h4><a href="{{ route('webcatalogue.recognition.sessions.show', $item) }}">Session #{{ $item->id }}</a></h4>
                        <span class="wc-badge">{{ $item->status }}</span>
                    </div>
                    <div class="wc-rich-meta">
                        <span class="wc-rich-metric"><i class="fa-solid fa-store"></i>{{ $item->store->name ?? '-' }}</span>
                        <span class="wc-rich-metric"><i class="fa-solid fa-box"></i>{{ $item->product->name ?? 'No product match' }}</span>
                        <span class="wc-rich-metric"><i class="fa-solid fa-clock"></i>{{ $item->created_at?->format('Y-m-d H:i') }}</span>
                    </div>
                    @if(!empty($item->metadata['match_error']) || !empty($item->metadata['capture_error']))
                        <p class="wc-muted">
                            {{ $item->metadata['match_error'] ?? $item->metadata['capture_error'] }}
                            @if(!empty($item->metadata['capture_profile_failures'][0]['reason']))
                                - {{ str_replace('_', ' ', $item->metadata['capture_profile_failures'][0]['reason']) }}
                            @endif
                        </p>
                    @endif

                    @if($suggestions->isNotEmpty())
                        <div class="wc-session-match-list">
                            @foreach($suggestions as $match)
                                @php($productImage = $match->product?->mainImageResource?->resolved_url)
                                <div class="wc-session-match-row">
                                    <div class="wc-session-match-product">
                                        <form method="post" action="{{ route('webcatalogue.recognition.sessions.associate_product', $item) }}" class="wc-session-visual-match-form">
                                            @csrf
                                            <input type="hidden" name="id_product" value="{{ $match->product->id }}">
                                            <input type="hidden" name="match_id" value="{{ $match->id }}">
                                            <button class="wc-icon-action wc-icon-action-primary" type="submit" title="Confirm match" aria-label="Confirm match"><i class="fa-solid fa-check"></i></button>
                                        </form>
                                        <div class="wc-session-product-thumb">
                                            @if($productImage)
                                                <img src="{{ $productImage }}" alt="{{ strip_tags($match->product->name ?? 'Product') }}">
                                            @else
                                                <i class="fa-solid fa-box"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <strong><a href="{{ route('webcatalogue.products.show', $match->product) }}"><span class="wc-html-inline">{!! $match->product->name ?? 'Product #'.$match->id_product !!}</span></a></strong>
                                            <span>#{{ $match->product->reference ?? $match->id_product }} - {{ number_format((float) $match->score, 2) }}% - Rank {{ $match->rank }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @elseif($item->product)
                        @php($productImage = $item->product?->mainImageResource?->resolved_url)
                        <div class="wc-session-match-list">
                            <div class="wc-session-match-row">
                                <div class="wc-session-match-product">
                                    <div class="wc-session-product-thumb">
                                        @if($productImage)
                                            <img src="{{ $productImage }}" alt="{{ strip_tags($item->product->name ?? 'Product') }}">
                                        @else
                                            <i class="fa-solid fa-box"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <strong><a href="{{ route('webcatalogue.products.show', $item->product) }}"><span class="wc-html-inline">{!! $item->product->name !!}</span></a></strong>
                                        <span>#{{ $item->product->reference }} - assigned</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="wc-rich-actions">
                    <a class="wc-icon-action" href="{{ route('webcatalogue.recognition.sessions.show', $item) }}" title="Details" aria-label="Details"><i class="fa-solid fa-eye"></i></a>
                    <form method="post" action="{{ route('webcatalogue.recognition.sessions.destroy', $item) }}" onsubmit="return confirm('Remove this recognition session?')">
                        @csrf
                        @method('DELETE')
                        <button class="wc-icon-action wc-icon-action-danger" type="submit" title="Remove" aria-label="Remove"><i class="fa-solid fa-trash"></i></button>
                    </form>
                </div>
            </div>
        @empty
            <div class="wc-list-empty"><i class="fa-solid fa-camera"></i><div><strong>No sessions in this queue.</strong><br><span>Use another status tab to review older or resolved scans.</span></div></div>
        @endforelse
    </div>
    <div class="wc-pagination">{{ $items->links() }}</div>
</div>
</div>
@endsection
