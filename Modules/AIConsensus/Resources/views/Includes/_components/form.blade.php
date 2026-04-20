<form id="lsg-form" method="POST" action="{{ $action }}" enctype="multipart/form-data" data-ai-loading-form>
    @csrf
    @if(!empty($method) && strtoupper($method) !== 'POST')
        @method($method)
    @endif
    <div class="row">
        <div class="col-lg-9">
            <div class="ai-card">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Título</label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $run->title ?? '') }}">
                        @error('title')<div class="ai-field-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Template key</label>
                        <input type="text" name="template_key" class="form-control @error('template_key') is-invalid @enderror" value="{{ old('template_key', $run->template_key ?? ($defaults['template_key'] ?? 'module_scaffold_v1')) }}">
                        @error('template_key')<div class="ai-field-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Prompt</label>
                        <div class="ai-copy-block">
                            <div class="ai-copy-toolbar">
                                <button type="button" class="lsg-action-btn lsg-action-btn--success lsg-action-btn--compact" data-copy-target="prompt-field" data-copy-title="{{ __('ai-consensus::actions.copy') }}" data-copied-title="{{ __('ai-consensus::actions.copied') }}" title="{{ __('ai-consensus::actions.copy') }}">
                                    <span class="lsg-action-btn__icon"><i class="fa-solid fa-copy"></i></span>
                                </button>
                            </div>
                            <textarea id="prompt-field" name="prompt" class="form-control ai-textarea ai-copy-field @error('prompt') is-invalid @enderror" required>{{ old('prompt', $run->prompt ?? '') }}</textarea>
                        </div>
                        @error('prompt')<div class="ai-field-error">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="ai-card">
                <h5 class="mb-3">Opções</h5>
                <div class="row g-3">
                    <div class="col-md-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="options[include_files]" value="1" id="include_files"
                                @checked((bool) old('options.include_files', data_get($run->options ?? [], 'include_files', true)))>
                            <label class="form-check-label" for="include_files">Incluir ficheiros no prompt</label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="options[run_claude]" value="1" id="run_claude"
                                @checked((bool) old('options.run_claude', data_get($run->options ?? [], 'run_claude', true)))>
                            <label class="form-check-label" for="run_claude">Executar Claude</label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="options[run_gemini]" value="1" id="run_gemini"
                                @checked((bool) old('options.run_gemini', data_get($run->options ?? [], 'run_gemini', true)))>
                            <label class="form-check-label" for="run_gemini">Executar Gemini</label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="options[run_openai_final]" value="1" id="run_openai_final"
                                @checked((bool) old('options.run_openai_final', data_get($run->options ?? [], 'run_openai_final', true)))>
                            <label class="form-check-label" for="run_openai_final">Integrar no OpenAI</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Ficheiros</label>
                        <input type="file" name="files[]" class="form-control @error('files') is-invalid @enderror @error('files.*') is-invalid @enderror" multiple>
                        <div class="small ai-muted mt-1">TXT, CSV, JSON, XML, HTML, LOG, SQL, PHP, JS, TS, CSS, DOCX e PDF.</div>
                        @error('files')<div class="ai-field-error">{{ $message }}</div>@enderror
                        @error('files.*')<div class="ai-field-error">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            <div class="ai-card">
                <div class="ai-actions" style="justify-content:space-between;">
                    <a href="{{ route('ai_consensus.index') }}" class="lsg-action-btn lsg-action-btn--primary">
                        <span class="lsg-action-btn__icon"><i class="fa-solid fa-angle-left" aria-hidden="true"></i></span>
                        <span class="lsg-action-btn__label">Back</span>
                    </a>
                    <button type="submit" class="lsg-action-btn lsg-action-btn--success" data-loading-label="{{ $submitLabel ?? 'Guardar' }}">
                        <span class="lsg-action-btn__icon"><i class="fa-solid fa-cogs" aria-hidden="true"></i></span>
                        <span class="lsg-action-btn__label">{{ $submitLabel ?? 'Guardar' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
