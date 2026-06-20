@extends('layouts.app')

@push('styles')
    @include('areas.sales.includes.css')
@endpush

@push('scripts')
    @include('areas.sales.includes.js')
@endpush

@section('content')
    @include('areas.partials.product-growth-work-panel', ['areaKey' => 'sales'])
    @include('areas.partials.idea-panel', ['areaKey' => 'sales'])
@endsection
