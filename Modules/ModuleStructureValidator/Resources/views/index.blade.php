@extends('layouts.app')

@section('content')
<div class="lsg-content">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="h3 mb-1">{{ __('module-structure-validator::messages.title') }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">B.O.</a></li>
                    <li class="breadcrumb-item active">Module Structure Validator</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form action="{{ route('module_structure_validator.run') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">{{ __('module-structure-validator::messages.module_name') }}</label>
                        <input type="text" name="module_name" class="form-control" placeholder="IdeaLab" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">{{ __('module-structure-validator::messages.module_path') }}</label>
                        <input type="text" name="module_path" class="form-control" value="{{ base_path('Modules/IdeaLab') }}" required>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fa fa-play me-1"></i> {{ __('module-structure-validator::messages.run_validation') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
