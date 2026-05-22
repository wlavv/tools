@extends(config('ai_consensus.layout', 'layouts.app'))

@section('content')
<div>
    @php($isCreate = $isCreate ?? !$template->exists)
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

    <form id="lsg-form" method="POST" action="{{ $isCreate ? route('ai_consensus.templates.store') : route('ai_consensus.templates.update', $template) }}" class="card">
        @csrf
        @unless($isCreate)
            @method('PATCH')
        @endunless
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Key</label>
                    <input name="template_key" class="form-control" value="{{ old('template_key', $template->template_key) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Name</label>
                    <input name="name" class="form-control" value="{{ old('name', $template->name) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Scope</label>
                    <input name="module_scope" class="form-control" value="{{ old('module_scope', $template->module_scope) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Category</label>
                    <input name="category" class="form-control" value="{{ old('category', $template->category) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Output type</label>
                    <select name="default_output_type" class="form-select">
                        @foreach($outputTypes as $key => $type)
                            <option value="{{ $key }}" @selected(old('default_output_type', $template->default_output_type) === $key)>{{ $key }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2">{{ old('description', $template->description) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">System prompt</label>
                    <textarea name="system_prompt" class="form-control" rows="6">{{ old('system_prompt', $template->system_prompt) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">User prompt template</label>
                    <textarea name="user_prompt_template" class="form-control" rows="8" required>{{ old('user_prompt_template', $template->user_prompt_template) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Version</label>
                    <input name="version" class="form-control" value="{{ old('version', $template->version) }}">
                </div>
                <div class="col-md-6">
                    <div class="form-check mt-4">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $template->is_active))>
                        <label class="form-check-label">Active</label>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
