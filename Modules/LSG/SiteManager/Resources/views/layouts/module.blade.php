@extends('layouts.app')

@section('content')
@include('site-manager::Includes.css')
<div class="site-manager-page">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('warning'))<div class="alert alert-warning">{{ session('warning') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="sm-shell">
        <nav class="sm-nav sm-card">
            <a href="{{ route('lsg.site_manager.dashboard') }}"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
            <a href="{{ route('lsg.site_manager.sites.index') }}"><i class="fa-solid fa-globe"></i> Sites</a>
        </nav>
        <main>@yield('module-content')</main>
    </div>
</div>
@include('site-manager::Includes.js')
@endsection
