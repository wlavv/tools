@extends('product-core::layouts.module')

@section('module-content')
@include('product-core::partials.product-timeline', [
    'productGrowthTimelineCurrentStage' => 'product-core',
    'productGrowthTimelineShowPreviewButton' => false,
])

<div class="pc-department-work-layout">
    <aside class="product-core-card pc-panel pc-department-work-preview">
        @include('product-core::partials.product-preview-card')
    </aside>

    <section class="product-core-card pc-panel pc-department-work-main">
        <form id="product-core-form" method="POST" action="{{ route('product_growth.product_core.products.update', $product) }}" class="pc-form">
            @csrf
            @method('PUT')
            @include('product-core::products._form')
        </form>
    </section>
</div>
@endsection
