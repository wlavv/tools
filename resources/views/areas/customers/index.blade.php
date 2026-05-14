@extends('layouts.app')

@push('styles')
    @include('areas.customers.includes.css')
@endpush

@push('scripts')
    @include('areas.customers.includes.js')
@endpush

@section('content')
    @include('areas.partials.idea-panel', ['areaKey' => 'support'])
@endsection
