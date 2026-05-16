@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell">
@if(session('success'))<div class="wc-alert">{{ session('success') }}</div>@endif
@if($errors->any())<div class="wc-alert wc-alert-warning">{{ $errors->first() }}</div>@endif

<style>.wc-score-breakdown{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:6px;margin:10px 0}.wc-score-breakdown span{background:rgba(15,23,42,.05);border:1px solid rgba(15,23,42,.08);border-radius:5px;padding:6px 8px;font-size:12px;color:#475569}.wc-score-breakdown strong{display:block;color:#0f172a;font-size:11px;text-transform:uppercase;letter-spacing:.04em}</style>

<div class="wc-editor-layout">
    <div>
        <div class="wc-detail-hero wc-detail-hero-resource">
            <div>
                <span class="wc-eyebrow"><i class="fa-solid fa-camera"></i> Recognition Session</span>
                <h2>Session #{{ $item->id }}</h2>
                <p>{{ $item->status }}</p>
                <div class="wc-detail-tags">
                    <span class="wc-badge">{{ $item->store->name ?? 'No store' }}</span>
                    <span class="wc-badge">{{ $item->created_at?->format('Y-m-d H:i') }}</span>
                    @if($item->product)<span class="wc-badge">Product #{{ $item->product->reference }}</span>@endif
                </div>
            </div>
            <div class="wc-detail-icon"><i class="fa-solid fa-camera"></i></div>
        </div>

        <div class="wc-card wc-spaced-card">
            <div class="wc-section-head"><div><h3>Captures</h3><p class="wc-muted">Object and label images submitted by the user.</p></div></div>
            <div class="wc-grid">
                @forelse($item->captures as $capture)
                    <div class="wc-preview-card">
                        <div class="wc-preview-media">@if($capture->resolved_url)<img src="{{ $capture->resolved_url }}" alt="capture">@else<i class="fa-solid fa-image"></i>@endif</div>
                        <div class="wc-preview-body"><h4>{{ str_replace('_',' ', $capture->capture_type) }}</h4><p class="wc-muted">{{ $capture->created_at?->format('Y-m-d H:i') }}</p></div>
                    </div>
                @empty
                    <div class="wc-list-empty"><i class="fa-solid fa-image"></i><div><strong>No captures.</strong></div></div>
                @endforelse
            </div>
        </div>

        <div class="wc-card wc-spaced-card">
            <div class="wc-section-head"><div><h3>Recognition matches</h3><p class="wc-muted">Internal matching suggestions generated from captured product images.</p></div></div>
            <div class="wc-grid">
                @forelse($item->matches()->with('product')->orderBy('rank')->get() as $match)
                    <div class="wc-preview-card">
                        <div class="wc-preview-body">
                            <h4>{{ $match->product->name ?? 'Product #' . $match->id_product }}</h4>
                            <p class="wc-muted">Score: <strong>{{ number_format((float) $match->score, 2) }}%</strong> - Provider: {{ $match->match_provider }}</p>
                            @php($scores = $match->metadata['scores'] ?? [])
                            @if(!empty($scores))
                                <div class="wc-score-breakdown">
                                    <span><strong>pHash</strong> {{ number_format((float) ($scores['phash_score'] ?? 0), 2) }}%</span>
                                    <span><strong>Edges</strong> {{ number_format((float) ($scores['edge_score'] ?? 0), 2) }}%</span>
                                    <span><strong>Color</strong> {{ number_format((float) ($scores['color_score'] ?? 0), 2) }}%</span>
                                </div>
                            @endif
                            <p><span class="wc-badge">{{ $match->status }}</span> <span class="wc-badge">Rank {{ $match->rank }}</span></p>
                            @if($match->product)
                                <div class="wc-actions-row">
                                    <a class="wc-action-link" href="{{ route('webcatalogue.products.show', $match->product) }}"><i class="fa-solid fa-arrow-up-right-from-square"></i> Open product</a>
                                    <form method="post" action="{{ route('webcatalogue.recognition.sessions.associate_product', $item) }}">
                                        @csrf
                                        <input type="hidden" name="id_product" value="{{ $match->product->id }}">
                                        <input type="hidden" name="match_id" value="{{ $match->id }}">
                                        <button class="wc-action-link" type="submit"><i class="fa-solid fa-check"></i> Confirm</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="wc-list-empty"><i class="fa-solid fa-wand-magic-sparkles"></i><div><strong>No matches recorded.</strong><p class="wc-muted">Run recognition from the front scan page to create suggestions.</p></div></div>
                @endforelse
            </div>
        </div>

        @if($item->lead)
            <div class="wc-card wc-spaced-card">
                <div class="wc-section-head"><div><h3>Unmatched lead</h3></div></div>
                <p><strong>Brand:</strong> {{ $item->lead->brand ?: '-' }}</p>
                <p><strong>Model:</strong> {{ $item->lead->model ?: '-' }}</p>
                <a class="wc-action-link" href="{{ route('webcatalogue.recognition.leads.show', $item->lead) }}"><i class="fa-solid fa-bullseye"></i> Open lead</a>
            </div>
        @endif
    </div>

    <aside class="wc-preview-panel">
        <div class="wc-preview-card">
            <div class="wc-preview-body">
                <h4>Review actions</h4>
                <form method="post" action="{{ route('webcatalogue.recognition.sessions.associate_product', $item) }}">
                    @csrf
                    <div class="wc-field">
                        <label>Associate product</label>
                        <select name="id_product" required>
                            <option value="">Select product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">#{{ $product->reference }} - {{ strip_tags($product->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="wc-primary-btn wc-full-action" type="submit"><i class="fa-solid fa-link"></i> Associate scan</button>
                </form>
            </div>
        </div>

        <div class="wc-preview-card">
            <div class="wc-preview-body">
                <h4>Create lead</h4>
                <form method="post" action="{{ route('webcatalogue.recognition.sessions.create_lead', $item) }}">
                    @csrf
                    <div class="wc-field"><label>Brand</label><input name="brand" value="{{ $item->lead->brand ?? '' }}"></div>
                    <div class="wc-field"><label>Model</label><input name="model" value="{{ $item->lead->model ?? '' }}"></div>
                    <div class="wc-field"><label>Reference</label><input name="reference" value="{{ $item->lead->reference ?? '' }}"></div>
                    <div class="wc-field"><label>Email</label><input type="email" name="customer_email" value="{{ $item->lead->customer_email ?? '' }}"></div>
                    <div class="wc-field"><label>Description</label><textarea name="description" rows="3">{{ $item->lead->description ?? '' }}</textarea></div>
                    <button class="wc-secondary-btn wc-full-action" type="submit"><i class="fa-solid fa-bullseye"></i> Save lead</button>
                </form>
            </div>
        </div>

        <div class="wc-preview-card">
            <div class="wc-preview-body">
                <h4>Create product</h4>
                <form method="post" action="{{ route('webcatalogue.recognition.sessions.create_product', $item) }}">
                    @csrf
                    <div class="wc-field"><label>Name</label><input name="name" required value="{{ $item->lead->model ?? '' }}"></div>
                    <div class="wc-field"><label>Reference</label><input name="reference" value="{{ $item->lead->reference ?? 'SCAN-'.$item->id }}"></div>
                    <div class="wc-field"><label>Brand</label><input name="brand" value="{{ $item->lead->brand ?? '' }}"></div>
                    <div class="wc-field"><label>Category</label><input name="category"></div>
                    <div class="wc-field"><label>Description</label><textarea name="description" rows="3">{{ $item->lead->description ?? '' }}</textarea></div>
                    <button class="wc-primary-btn wc-full-action" type="submit"><i class="fa-solid fa-box-open"></i> Create product</button>
                </form>
            </div>
        </div>

        <div class="wc-preview-card">
            <div class="wc-preview-body">
                <h4>Session metadata</h4>
                <p><strong>IP:</strong> {{ $item->ip_address ?: '-' }}</p>
                <p><strong>Device:</strong> {{ $item->device_type ?: '-' }}</p>
                <p><strong>Algorithm:</strong> {{ $item->metadata['recognition_algorithm'] ?? '-' }}</p>
                <p><strong>Best score:</strong> {{ isset($item->metadata['best_debug_score']) ? number_format((float) $item->metadata['best_debug_score'], 2) . '%' : '-' }}</p>
                <p><strong>Auto threshold:</strong> {{ $item->metadata['auto_threshold'] ?? config('webcatalogue.recognition.auto_match_threshold') }}%</p>
                <p><strong>Suggestion threshold:</strong> {{ $item->metadata['suggestion_threshold'] ?? config('webcatalogue.recognition.suggestion_threshold') }}%</p>
            </div>
        </div>
    </aside>
</div>
</div>
@endsection
