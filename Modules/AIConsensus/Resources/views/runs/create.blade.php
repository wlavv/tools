@extends(config('ai_consensus.layout', 'layouts.app'))

@section('content')
<div>
    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Existem erros no formulário.</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('ai_consensus.runs.store') }}" class="card">
        @csrf
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Source module</label>
                    <input name="source_module" class="form-control" value="{{ old('source_module', 'Manual') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Source type</label>
                    <input name="source_type" class="form-control" value="{{ old('source_type', 'manual_run') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Source ID</label>
                    <input name="source_id" class="form-control" value="{{ old('source_id') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Template</label>
                    <select name="template_key" class="form-select" required>
                        @foreach($templates as $template)
                            <option value="{{ $template->template_key }}" @selected(old('template_key') === $template->template_key)>
                                {{ $template->template_key }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Output type</label>
                    <select name="output_type" class="form-select" required>
                        @foreach($outputTypes as $key => $type)
                            <option value="{{ $key }}" @selected(old('output_type') === $key)>{{ $key }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Input payload JSON</label>
                    <textarea name="input_payload[description]" class="form-control" rows="6" required>{{ old('input_payload.description') }}</textarea>
                </div>
                <div class="col-md-3">
                    <div class="form-check mt-4">
                        <input type="hidden" name="options[async]" value="0">
                        <input type="checkbox" name="options[async]" value="1" class="form-check-input" checked>
                        <label class="form-check-label">Async</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Language</label>
                    <input name="options[language]" class="form-control" value="{{ old('options.language', 'pt') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Mode</label>
                    <input name="options[consensus_mode]" class="form-control" value="{{ old('options.consensus_mode', 'single_provider') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Format</label>
                    <input name="options[return_format]" class="form-control" value="{{ old('options.return_format', 'json') }}">
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <a href="{{ route('ai_consensus.runs.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <button class="btn btn-primary">
                <i class="fas fa-save"></i> Create
            </button>
        </div>
    </form>
</div>
@endsection
