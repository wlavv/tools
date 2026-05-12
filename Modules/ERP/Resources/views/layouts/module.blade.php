@extends('layouts.app')

@push('styles')
    <style>
        {!! file_get_contents(base_path('Modules/ERP/Resources/assets/css/erp.css')) !!}
    </style>
@endpush

@push('scripts')
    <script>
        {!! file_get_contents(base_path('Modules/ERP/Resources/assets/js/erp.js')) !!}
    </script>
@endpush

@section('content')
    <div class="erp-shell">
        @yield('erp-content')
    </div>
@endsection
