@extends('layouts.app')

@section('content')
<div class="lsg-content">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="h3 mb-1"><i class="fa-solid fa-shield-check me-2"></i>{{ __('module-compliance-core::messages.title') }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">B.O.</a></li>
                    <li class="breadcrumb-item active">Module Compliance Core</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <p class="mb-0">{{ __('module-compliance-core::messages.description') }}</p>
        </div>
    </div>
</div>
@endsection
