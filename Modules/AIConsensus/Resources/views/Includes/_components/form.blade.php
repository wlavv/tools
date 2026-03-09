<form method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    @if(!empty($method) && strtoupper($method) !== 'POST')
        @method($method)
    @endif

    <div class="ai-card">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Título</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $run->title ?? '') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Template key</label>
                <input type="text" name="template_key" class="form-control" value="{{ old('template_key', $run->template_key ?? ($defaults['template_key'] ?? 'module_scaffold_v1')) }}">
            </div>
            <div class="col-12">
                <label class="form-label">Prompt</label>
                <textarea name="prompt" class="form-control ai-textarea" required>{{ old('prompt', $run->prompt ?? '') }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Ficheiros</label>
                <input type="file" name="files[]" class="form-control" multiple>
                <div class="small ai-muted mt-1">TXT, CSV, JSON, XML, HTML, LOG, SQL, PHP, JS, TS, CSS, DOCX e PDF.</div>
            </div>
        </div>
    </div>

    <div class="ai-card">
        <h5 class="mb-3">Opções</h5>
        <div class="row g-3">
            <div class="col-md-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="options[include_files]" value="1" id="include_files"
                        @checked((bool) old('options.include_files', data_get($run->options ?? [], 'include_files', true)))>
                    <label class="form-check-label" for="include_files">Incluir ficheiros no prompt</label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="options[run_claude]" value="1" id="run_claude"
                        @checked((bool) old('options.run_claude', data_get($run->options ?? [], 'run_claude', true)))>
                    <label class="form-check-label" for="run_claude">Executar Claude</label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="options[run_gemini]" value="1" id="run_gemini"
                        @checked((bool) old('options.run_gemini', data_get($run->options ?? [], 'run_gemini', true)))>
                    <label class="form-check-label" for="run_gemini">Executar Gemini</label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="options[run_openai_final]" value="1" id="run_openai_final"
                        @checked((bool) old('options.run_openai_final', data_get($run->options ?? [], 'run_openai_final', true)))>
                    <label class="form-check-label" for="run_openai_final">Integrar no OpenAI</label>
                </div>
            </div>
        </div>
    </div>

    <div class="ai-actions">
        <button type="submit" class="btn btn-primary">{{ $submitLabel ?? 'Guardar' }}</button>
        <a href="{{ route('ai_consensus.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
