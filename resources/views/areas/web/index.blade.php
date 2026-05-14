@extends('layouts.app')

@push('styles')
    @include('areas.web.includes.css')
@endpush

@push('scripts')
    @include('areas.web.includes.js')
@endpush

@section('content')
    @include('areas.partials.idea-panel', ['areaKey' => 'webmaster'])
@endsection
