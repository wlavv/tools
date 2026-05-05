@extends('webcatalogue::front.layouts.app')

@section('content')
<div class="wc-front-container">
    <section class="wc-front-hero">
        <div>
            <span class="wc-front-kicker"><i class="fa-solid fa-check-circle"></i> Request submitted</span>
            <h1>Thank you</h1>
            <p>Your product request was recorded. The WebCatalogue team can now review this request and evaluate demand.</p>
        </div>
    </section>

    <div class="wc-front-card" style="padding:24px;border-radius:var(--wc-radius,5px)">
        <h3>Recognition session</h3>
        <p>Status: <strong>{{ $session->status }}</strong></p>
        @if($session->lead)
            <p>Brand: <strong>{{ $session->lead->brand ?: '—' }}</strong></p>
            <p>Model: <strong>{{ $session->lead->model ?: '—' }}</strong></p>
        @endif
        <a class="wc-front-btn wc-front-btn-primary" href="{{ route('webcatalogue.front.store.show', $store->slug) }}">Back to catalogue</a>
    </div>
</div>
@endsection
