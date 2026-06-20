@extends('layouts.app')

@push('styles')
    @include('areas.finance.includes.css')
@endpush

@push('scripts')
    @include('areas.finance.includes.js')
@endpush

@section('content')
    @include('areas.partials.product-growth-work-panel', ['areaKey' => 'finance'])
    @include('areas.partials.idea-panel', ['areaKey' => 'finance'])
@endsection
