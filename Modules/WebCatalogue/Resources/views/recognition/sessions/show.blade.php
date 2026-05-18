@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell">
@if(session('success'))<div class="wc-alert">{{ session('success') }}</div>@endif
@if(session('error'))<div class="wc-alert wc-alert-warning">{{ session('error') }}</div>@endif
@if($errors->any())<div class="wc-alert wc-alert-warning">{{ $errors->first() }}</div>@endif
@php
    $objectCapture = $item->captures->where('capture_type', 'object_photo')->sortByDesc('id')->first();
    $capturePreview = $objectCapture?->resolved_url;
    $captureCropPreview = $objectCapture?->metadata['detected_object_crop_url'] ?? null;
    $forcedCompare = session('forced_compare');
    $matchByProduct = $item->matches->mapWithKeys(fn($match) => [$match->id_product => [
        'match_id' => $match->id,
        'rank' => $match->rank,
        'status' => $match->status,
        'provider' => $match->match_provider,
        'score' => (float) $match->score,
        'scores' => $match->metadata['scores'] ?? [],
    ]]);
@endphp

<style>
.wc-score-breakdown{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:6px;margin:10px 0}.wc-score-breakdown span{background:rgba(15,23,42,.05);border:1px solid rgba(15,23,42,.08);border-radius:5px;padding:6px 8px;font-size:12px;color:#475569}.wc-score-breakdown strong{display:block;color:#0f172a;font-size:11px;text-transform:uppercase;letter-spacing:.04em}.wc-match-card .wc-preview-media{height:210px;background:#f8fafc}.wc-match-card .wc-preview-media img{width:100%;height:100%;object-fit:contain}.wc-capture-analysis{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.wc-capture-shot{min-height:180px;background:#f8fafc;border:1px solid rgba(148,163,184,.22);display:flex;align-items:center;justify-content:center;overflow:hidden}.wc-capture-shot img{width:100%;height:100%;max-height:260px;object-fit:contain}.wc-capture-shot i{font-size:32px;color:#94a3b8}.wc-capture-label{margin:7px 0 0;color:#64748b;font-size:12px}.wc-capture-meta{margin:8px 0 0;color:#475569;font-size:12px}.wc-associate-preview{display:none;margin:12px 0;padding:10px;border:1px solid rgba(148,163,184,.28);border-radius:5px;background:#f8fafc}.wc-associate-preview.is-visible{display:block}.wc-associate-compare{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px}.wc-associate-shot{min-height:160px;border:1px solid rgba(148,163,184,.22);border-radius:5px;background:#fff;display:flex;align-items:center;justify-content:center;overflow:hidden}.wc-associate-shot img{width:100%;height:100%;max-height:220px;object-fit:contain}.wc-associate-shot span{color:#94a3b8;font-size:12px}.wc-associate-info p{margin:5px 0;color:#475569}.wc-associate-info strong{color:#0f172a}.wc-match-mini-title{display:flex;justify-content:space-between;gap:8px;align-items:flex-start}.wc-match-mini-title h4{margin:0}@media(max-width:768px){.wc-associate-compare,.wc-capture-analysis{grid-template-columns:1fr}}
</style>

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
                        @php($analysis = $capture->metadata['recognition_analysis'] ?? [])
                        @php($cropUrl = $capture->metadata['detected_object_crop_url'] ?? null)
                        @php($opencv = $capture->metadata['opencv_analysis'] ?? [])
                        @php($opencvUrl = $opencv['normalized_url'] ?? null)
                        @php($opencvDebugUrl = $opencv['debug_url'] ?? null)
                        @php($identifiers = $capture->metadata['identifiers'] ?? [])
                        <div class="wc-preview-body">
                            <h4>{{ str_replace('_',' ', $capture->capture_type) }}</h4>
                            <p class="wc-muted">{{ $capture->created_at?->format('Y-m-d H:i') }}</p>
                            <div class="wc-capture-analysis">
                                <div>
                                    <div class="wc-capture-shot">@if($capture->resolved_url)<img src="{{ $capture->resolved_url }}" alt="Original capture">@else<i class="fa-solid fa-image"></i>@endif</div>
                                    <p class="wc-capture-label">Original capture</p>
                                </div>
                                <div>
                                    <div class="wc-capture-shot">@if($cropUrl)<img src="{{ $cropUrl }}" alt="Detected object crop">@else<i class="fa-solid fa-crop-simple"></i>@endif</div>
                                    <p class="wc-capture-label">Detected crop used by recognition</p>
                                </div>
                                @if($opencvUrl)
                                    <div>
                                        <div class="wc-capture-shot"><img src="{{ $opencvUrl }}" alt="OpenCV normalized image"></div>
                                        <p class="wc-capture-label">OpenCV normalized</p>
                                    </div>
                                @endif
                                @if($opencvDebugUrl)
                                    <div>
                                        <div class="wc-capture-shot"><img src="{{ $opencvDebugUrl }}" alt="OpenCV debug image"></div>
                                        <p class="wc-capture-label">OpenCV debug</p>
                                    </div>
                                @endif
                            </div>
                            @if(!empty($analysis))
                                <p class="wc-capture-meta">
                                    Box: {{ implode(', ', $analysis['object_box'] ?? []) ?: '-' }}
                                    @if(!empty($analysis['object_aspect_ratio'])) · Aspect: {{ $analysis['object_aspect_ratio'] }}@endif
                                    @if(!empty($analysis['algorithm'])) · {{ $analysis['algorithm'] }}@endif
                                </p>
                            @endif
                            @if(!empty($identifiers))
                                <p class="wc-capture-meta">
                                    Identifiers:
                                    @foreach($identifiers as $identifier)
                                        <span class="wc-badge">{{ $identifier['format'] ?? 'code' }}: {{ $identifier['rawValue'] ?? $identifier['value'] ?? '' }}</span>
                                    @endforeach
                                </p>
                            @endif
                            @if(empty($analysis) && $capture->capture_type === 'object_photo')
                                <p class="wc-capture-meta">Run or force a comparison to generate the detected crop.</p>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="wc-list-empty"><i class="fa-solid fa-image"></i><div><strong>No captures.</strong></div></div>
                @endforelse
            </div>
        </div>

        <div class="wc-card wc-spaced-card">
            <div class="wc-section-head"><div><h3>Recognition matches</h3><p class="wc-muted">Internal matching suggestions generated from captured product images.</p></div></div>
            @if(!empty($item->metadata['match_error']) || !empty($item->metadata['capture_error']))
                <div class="wc-alert wc-alert-warning">
                    <strong>{{ $item->metadata['match_error'] ?? $item->metadata['capture_error'] }}</strong>
                    @if(!empty($item->metadata['match_exception']))
                        <br><span>{{ $item->metadata['match_exception'] }}</span>
                    @endif
                    @if(!empty($item->metadata['capture_profile_failures']))
                        <div class="wc-score-breakdown">
                            @foreach($item->metadata['capture_profile_failures'] as $failure)
                                <span>
                                    <strong>Capture #{{ $failure['capture_id'] ?? '-' }}</strong>
                                    {{ str_replace('_', ' ', $failure['reason'] ?? 'unknown') }}
                                    @if(array_key_exists('file_exists', $failure)) - file {{ !empty($failure['file_exists']) ? 'exists' : 'missing' }}@endif
                                    @if(array_key_exists('normalized_exists', $failure) && $failure['normalized_exists'] !== null) - normalized {{ !empty($failure['normalized_exists']) ? 'exists' : 'missing' }}@endif
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
            <div class="wc-grid">
                @php($rankedMatches = $item->matches->sortBy('rank')->take(3))
                @forelse($rankedMatches as $match)
                    @php($productImage = $match->product?->mainImageResource?->resolved_url)
                    <div class="wc-preview-card wc-match-card">
                        <div class="wc-preview-media">@if($productImage)<img src="{{ $productImage }}" alt="{{ $match->product?->name }}">@else<i class="fa-solid fa-image"></i>@endif</div>
                        <div class="wc-preview-body">
                            <div class="wc-match-mini-title">
                                <h4>{{ $match->product->name ?? 'Product #' . $match->id_product }}</h4>
                                <span class="wc-badge">Rank {{ $match->rank }}</span>
                            </div>
                            <p class="wc-muted">Score: <strong>{{ number_format((float) $match->score, 2) }}%</strong> - Provider: {{ $match->match_provider }}</p>
                            @php($scores = $match->metadata['scores'] ?? [])
                            @if(!empty($scores))
                                <div class="wc-score-breakdown">
                                    <span><strong>Embedding</strong> {{ number_format((float) ($scores['embedding_score'] ?? 0), 2) }}%</span>
                                    <span><strong>pHash</strong> {{ number_format((float) ($scores['phash_score'] ?? 0), 2) }}%</span>
                                    <span><strong>Edges</strong> {{ number_format((float) ($scores['edge_score'] ?? 0), 2) }}%</span>
                                    <span><strong>Color</strong> {{ number_format((float) ($scores['color_score'] ?? 0), 2) }}%</span>
                                    @if(array_key_exists('verification_score', $scores))<span><strong>Verification</strong> {{ number_format((float) ($scores['verification_score'] ?? 0), 2) }}%</span>@endif
                                    @if(!empty($scores['candidate_sources']))<span><strong>Candidate source</strong> {{ implode(', ', (array) $scores['candidate_sources']) }}</span>@endif
                                </div>
                                @if(array_key_exists('marker_score', $scores))
                                    <p class="wc-muted">Marker score: <strong>{{ number_format((float) ($scores['marker_score'] ?? 0), 2) }}%</strong> - {{ ($scores['marker_applied'] ?? false) ? 'boost applied' : ($scores['marker_status'] ?? 'observed') }}</p>
                                    <div class="wc-score-breakdown">
                                        <span><strong>Marker boost</strong> {{ number_format((float) ($scores['marker_boost'] ?? 0), 2) }}</span>
                                        <span><strong>Good matches</strong> {{ (int) ($scores['marker_good_matches'] ?? 0) }} / {{ (int) ($scores['marker_matches'] ?? 0) }}</span>
                                        <span><strong>Inlier ratio</strong> {{ number_format(((float) ($scores['marker_inlier_ratio'] ?? 0)) * 100, 2) }}%</span>
                                        <span><strong>Base score</strong> {{ number_format((float) ($scores['final_score_before_markers'] ?? $match->score), 2) }}%</span>
                                        @if(array_key_exists('marker_confidence_score', $scores))<span><strong>Marker confidence</strong> {{ number_format((float) ($scores['marker_confidence_score'] ?? 0), 2) }}%</span>@endif
                                        @if(array_key_exists('marker_hash_distance', $scores))<span><strong>Marker hash distance</strong> {{ $scores['marker_hash_distance'] ?? '-' }}</span>@endif
                                    </div>
                                @endif
                                @if(!empty($scores['region_scores']))
                                    <p class="wc-muted">Region score: <strong>{{ number_format((float) ($scores['region_score'] ?? 0), 2) }}%</strong> - Mode: {{ $scores['scoring_mode'] ?? 'structured_regions' }} @if(array_key_exists('region_applied', $scores))- {{ $scores['region_applied'] ? 'applied' : 'global kept' }}@endif</p>
                                    <div class="wc-score-breakdown">
                                        @foreach($scores['region_scores'] as $regionName => $regionScores)
                                            <span><strong>{{ ucfirst($regionName) }}</strong> {{ number_format((float) ($regionScores['final_score'] ?? 0), 2) }}%</span>
                                        @endforeach
                                    </div>
                                @endif
                            @endif
                            <p><span class="wc-badge">{{ $match->status }}</span></p>
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
                    <div class="wc-list-empty"><i class="fa-solid fa-wand-magic-sparkles"></i><div><strong>No matches recorded.</strong><p class="wc-muted">No visual candidate could be scored yet. Check the diagnostics above or run a forced comparison after the capture is available.</p></div></div>
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
                        <select name="id_product" required data-associate-product-select>
                            <option value="">Select product</option>
                            @foreach($products as $product)
                                @php($image = $product->mainImageResource?->resolved_url)
                                @php($matchData = $matchByProduct[$product->id] ?? null)
                                <option
                                    value="{{ $product->id }}"
                                    @selected((string) old('id_product') === (string) $product->id)
                                    data-image="{{ $image }}"
                                    data-reference="{{ $product->reference }}"
                                    data-name="{{ strip_tags($product->name) }}"
                                    data-match='@json($matchData)'
                                >#{{ $product->reference }} - {{ strip_tags($product->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <input type="hidden" name="match_id" data-associate-match-id>
                    <div class="wc-associate-preview" data-associate-preview>
                        <div class="wc-associate-compare">
                            <div>
                                <div class="wc-associate-shot">@if($captureCropPreview ?: $capturePreview)<img src="{{ $captureCropPreview ?: $capturePreview }}" alt="Capture">@else<span>No capture image</span>@endif</div>
                                <p class="wc-muted">{{ $captureCropPreview ? 'Detected scan crop' : 'Scan capture' }}</p>
                            </div>
                            <div>
                                <div class="wc-associate-shot" data-product-image-wrap><span>Select product</span></div>
                                <p class="wc-muted" data-product-caption>Product image</p>
                            </div>
                        </div>
                        <div class="wc-associate-info" data-associate-info></div>
                    </div>
                    @if(is_array($forcedCompare))
                        <div class="wc-associate-preview is-visible">
                            <div class="wc-associate-info">
                                @if($forcedCompare['ok'] ?? false)
                                    <p><strong>Forced comparison:</strong> {{ $forcedCompare['product_reference'] ?? '' }} - {{ $forcedCompare['product_name'] ?? '' }}</p>
                                    <p><strong>Score:</strong> {{ number_format((float) ($forcedCompare['score'] ?? 0), 2) }}% - <strong>Provider:</strong> {{ $forcedCompare['provider'] ?? '-' }}</p>
                                    @php($forcedScores = $forcedCompare['scores'] ?? [])
                                    <div class="wc-score-breakdown">
                                        <span><strong>Embedding</strong> {{ number_format((float) ($forcedScores['embedding_score'] ?? 0), 2) }}%</span>
                                        <span><strong>pHash</strong> {{ number_format((float) ($forcedScores['phash_score'] ?? 0), 2) }}%</span>
                                        <span><strong>Edges</strong> {{ number_format((float) ($forcedScores['edge_score'] ?? 0), 2) }}%</span>
                                        <span><strong>Color</strong> {{ number_format((float) ($forcedScores['color_score'] ?? 0), 2) }}%</span>
                                    </div>
                                    @if(array_key_exists('marker_score', $forcedScores))
                                        <p><strong>Marker score:</strong> {{ number_format((float) ($forcedScores['marker_score'] ?? 0), 2) }}% - <strong>Status:</strong> {{ ($forcedScores['marker_applied'] ?? false) ? 'boost applied' : ($forcedScores['marker_status'] ?? 'observed') }}</p>
                                        <div class="wc-score-breakdown">
                                            <span><strong>Marker boost</strong> {{ number_format((float) ($forcedScores['marker_boost'] ?? 0), 2) }}</span>
                                            <span><strong>Good matches</strong> {{ (int) ($forcedScores['marker_good_matches'] ?? 0) }} / {{ (int) ($forcedScores['marker_matches'] ?? 0) }}</span>
                                            <span><strong>Inlier ratio</strong> {{ number_format(((float) ($forcedScores['marker_inlier_ratio'] ?? 0)) * 100, 2) }}%</span>
                                            <span><strong>Base score</strong> {{ number_format((float) ($forcedScores['final_score_before_markers'] ?? $forcedCompare['score'] ?? 0), 2) }}%</span>
                                            @if(array_key_exists('marker_confidence_score', $forcedScores))<span><strong>Marker confidence</strong> {{ number_format((float) ($forcedScores['marker_confidence_score'] ?? 0), 2) }}%</span>@endif
                                            @if(array_key_exists('marker_hash_distance', $forcedScores))<span><strong>Marker hash distance</strong> {{ $forcedScores['marker_hash_distance'] ?? '-' }}</span>@endif
                                            @if(!empty($forcedScores['candidate_sources']))<span><strong>Candidate source</strong> {{ implode(', ', (array) $forcedScores['candidate_sources']) }}</span>@endif
                                        </div>
                                    @endif
                                    @if(!empty($forcedScores['region_scores']))
                                        <p><strong>Region score:</strong> {{ number_format((float) ($forcedScores['region_score'] ?? 0), 2) }}% - <strong>Mode:</strong> {{ $forcedScores['scoring_mode'] ?? 'structured_regions' }} @if(array_key_exists('region_applied', $forcedScores))- {{ $forcedScores['region_applied'] ? 'applied' : 'global kept' }}@endif</p>
                                        <div class="wc-score-breakdown">
                                            @foreach($forcedScores['region_scores'] as $regionName => $regionScores)
                                                <span><strong>{{ ucfirst($regionName) }}</strong> {{ number_format((float) ($regionScores['final_score'] ?? 0), 2) }}%</span>
                                            @endforeach
                                        </div>
                                    @endif
                                    <p><span class="wc-badge">Capture {{ $forcedScores['capture_variant'] ?? '-' }}</span> <span class="wc-badge">Product {{ $forcedScores['resource_variant'] ?? '-' }}</span></p>
                                @else
                                    <p><strong>Forced comparison failed:</strong> {{ $forcedCompare['message'] ?? 'No result.' }}</p>
                                @endif
                            </div>
                        </div>
                    @endif
                    <div class="wc-actions-row">
                        <button class="wc-secondary-btn" type="submit" formaction="{{ route('webcatalogue.recognition.sessions.compare_product', $item) }}"><i class="fa-solid fa-scale-balanced"></i> Compare selected</button>
                        <button class="wc-primary-btn" type="submit"><i class="fa-solid fa-link"></i> Associate scan</button>
                    </div>
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
<script>
document.addEventListener('DOMContentLoaded', function () {
    const select = document.querySelector('[data-associate-product-select]');
    const preview = document.querySelector('[data-associate-preview]');
    const imageWrap = document.querySelector('[data-product-image-wrap]');
    const caption = document.querySelector('[data-product-caption]');
    const info = document.querySelector('[data-associate-info]');
    const matchInput = document.querySelector('[data-associate-match-id]');
    if (!select || !preview || !imageWrap || !info) return;

    const pct = (value) => {
        const number = parseFloat(value || 0);
        return Number.isFinite(number) ? number.toFixed(2) + '%' : '-';
    };

    const scoreBlock = (scores) => {
        if (!scores || Object.keys(scores).length === 0) return '';
        return '<div class="wc-score-breakdown">'
            + '<span><strong>Embedding</strong> ' + pct(scores.embedding_score) + '</span>'
            + '<span><strong>pHash</strong> ' + pct(scores.phash_score) + '</span>'
            + '<span><strong>Edges</strong> ' + pct(scores.edge_score) + '</span>'
            + '<span><strong>Color</strong> ' + pct(scores.color_score) + '</span>'
            + '</div>';
    };

    const markerBlock = (scores) => {
        if (!scores || typeof scores.marker_score === 'undefined') return '';
        return '<p><strong>Marker score:</strong> ' + pct(scores.marker_score) + ' - <strong>Status:</strong> ' + (scores.marker_applied ? 'boost applied' : (scores.marker_status || 'observed')) + '</p>'
            + '<div class="wc-score-breakdown">'
            + '<span><strong>Marker boost</strong> ' + pct(scores.marker_boost) + '</span>'
            + '<span><strong>Good matches</strong> ' + (scores.marker_good_matches || 0) + ' / ' + (scores.marker_matches || 0) + '</span>'
            + '<span><strong>Inlier ratio</strong> ' + pct((scores.marker_inlier_ratio || 0) * 100) + '</span>'
            + '<span><strong>Base score</strong> ' + pct(scores.final_score_before_markers || 0) + '</span>'
            + (typeof scores.marker_confidence_score !== 'undefined' ? '<span><strong>Marker confidence</strong> ' + pct(scores.marker_confidence_score) + '</span>' : '')
            + (typeof scores.marker_hash_distance !== 'undefined' ? '<span><strong>Marker hash distance</strong> ' + (scores.marker_hash_distance ?? '-') + '</span>' : '')
            + (scores.candidate_sources ? '<span><strong>Candidate source</strong> ' + scores.candidate_sources.join(', ') + '</span>' : '')
            + '</div>';
    };

    select.addEventListener('change', function () {
        const option = select.options[select.selectedIndex];
        const image = option?.dataset.image || '';
        const name = option?.dataset.name || '';
        const reference = option?.dataset.reference || '';
        let match = null;

        try {
            match = option?.dataset.match ? JSON.parse(option.dataset.match) : null;
        } catch (e) {
            match = null;
        }

        preview.classList.toggle('is-visible', !!option?.value);
        matchInput.value = match?.match_id || '';
        imageWrap.innerHTML = image ? '<img src="' + image + '" alt="Product image">' : '<span>No product image</span>';
        caption.textContent = reference ? ('#' + reference + ' - ' + name) : 'Product image';

        if (!option?.value) {
            info.innerHTML = '';
            return;
        }

        if (match) {
            info.innerHTML = '<p><strong>Score:</strong> ' + pct(match.score) + ' - <strong>Provider:</strong> ' + (match.provider || '-') + '</p>'
                + scoreBlock(match.scores || {})
                + markerBlock(match.scores || {})
                + (match.scores?.region_scores ? '<p><strong>Region score:</strong> ' + pct(match.scores.region_score) + ' - <strong>Mode:</strong> ' + (match.scores.scoring_mode || 'structured_regions') + '</p>' : '')
                + '<p><span class="wc-badge">' + (match.status || 'suggested') + '</span> <span class="wc-badge">Rank ' + (match.rank || '-') + '</span></p>';
        } else {
            info.innerHTML = '<p><strong>No recorded match for this product.</strong></p>'
                + '<p class="wc-muted">Este produto não ficou nos candidatos gravados desta sessão. Para saber a posição exata seria necessário correr uma análise full-rank para esta captura.</p>';
        }
    });

    if (select.value) {
        select.dispatchEvent(new Event('change'));
    }
});
</script>
@endsection
