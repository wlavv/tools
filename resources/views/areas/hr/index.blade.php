@extends('layouts.app')

@push('styles')
    @include('areas.hr.includes.css')
@endpush

@push('scripts')
    @include('areas.hr.includes.js')
@endpush

@section('content')
    @include('areas.partials.product-growth-work-panel', ['areaKey' => 'hr'])
    @include('areas.partials.idea-panel', ['areaKey' => 'hr'])
@endsection
