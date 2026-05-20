@extends('layouts.app')

@section('title', __('module-integration-validator::messages.title'))

@section('content')
<div class="lsg-content">
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">{{ __('module-integration-validator::messages.home') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('module-integration-validator::messages.title') }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fa-solid fa-plug me-2"></i>{{ __('module-integration-validator::messages.run_validation') }}</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('module-integration-validator.run') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">{{ __('module-integration-validator::messages.module_name') }}</label>
                                <input type="text" name="module_name" class="form-control" value="{{ old('module_name', $moduleName ?? '') }}" placeholder="IdeaLab" required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">{{ __('module-integration-validator::messages.module_path') }}</label>
                                <input type="text" name="module_path" class="form-control" value="{{ old('module_path', $modulePath ?? $defaultPath) }}" required>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fa-solid fa-play me-1"></i>{{ __('module-integration-validator::messages.run') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if($result)
        @include('module-integration-validator::partials.result', ['result' => $result])
    @endif
</div>
@endsection
