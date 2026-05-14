@extends('layouts.app')

@push('styles')
    @include('areas.marketing.includes.css')
@endpush

@push('scripts')
    @include('areas.marketing.includes.js')
@endpush

@section('content')
    @include('areas.partials.idea-panel', ['areaKey' => 'marketing'])
@endsection
