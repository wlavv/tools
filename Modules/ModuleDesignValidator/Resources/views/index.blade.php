@extends('layouts.app')

@section('content')
<div >
    <p class="text-muted mb-3">{{ __('module-design-validator::messages.subtitle') }}</p>

    <div class="card shadow-sm">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h2 class="h5 mb-0"><i class="fa fa-paint-brush me-2"></i>{{ __('module-design-validator::messages.run_validation') }}</h2>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('module-design-validator.run') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">{{ __('module-design-validator::messages.module_name') }}</label>
                        <input type="text" name="module_name" class="form-control" value="{{ old('module_name', 'IdeaLab') }}" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">{{ __('module-design-validator::messages.module_path') }}</label>
                        <input type="text" name="module_path" class="form-control" value="{{ old('module_path', 'Modules/IdeaLab') }}" required>
                        <small class="text-muted">Pode ser relativo ao projeto ou absoluto.</small>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fa fa-play me-1"></i> {{ __('module-design-validator::messages.run_validation') }}
                    </button>
                    <a href="{{ url('/') }}" class="btn btn-outline-primary">
                        <i class="fa fa-angle-left me-1"></i> Voltar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

