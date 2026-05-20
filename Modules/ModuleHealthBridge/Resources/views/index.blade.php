@extends('layouts.app')

@section('content')
<div class="lsg-content">
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">{{ __('module-health-bridge::messages.dashboard') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('module-health-bridge::messages.title') }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">
                        <i class="fa-solid fa-heart-pulse me-2"></i>
                        {{ __('module-health-bridge::messages.run_validation') }}
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('module-health-bridge.run') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">{{ __('module-health-bridge::messages.module_name') }}</label>
                            <input type="text" name="module_name" class="form-control" placeholder="IdeaLab" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('module-health-bridge::messages.module_path') }}</label>
                            <input type="text" name="module_path" class="form-control" placeholder="{{ base_path('Modules/IdeaLab') }}" required>
                            <small class="text-muted">{{ __('module-health-bridge::messages.module_path_help') }}</small>
                        </div>
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fa-solid fa-play me-1"></i>
                            {{ __('module-health-bridge::messages.run') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fa-solid fa-circle-info me-2"></i>{{ __('module-health-bridge::messages.about') }}</h6>
                </div>
                <div class="card-body small text-muted">
                    <p>{{ __('module-health-bridge::messages.about_text') }}</p>
                    <ul class="mb-0">
                        <li>{{ __('module-health-bridge::messages.about_1') }}</li>
                        <li>{{ __('module-health-bridge::messages.about_2') }}</li>
                        <li>{{ __('module-health-bridge::messages.about_3') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
