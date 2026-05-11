@extends('layouts.app')

@section('content')
    <div class="dms-shell">
        @include('documentmanager::partials.nav')

        @if(session('success'))
            <div class="dms-alert dms-alert--success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="dms-alert dms-alert--danger">{{ session('error') }}</div>
        @endif

        @yield('documentmanager-content')
    </div>
@endsection
