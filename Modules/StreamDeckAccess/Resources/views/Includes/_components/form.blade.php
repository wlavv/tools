@php
    $isEdit = filled($accessPoint?->id);
    $selectedType = old('type', $accessPoint?->type ?? 'task');
    $payloadJson = old('payload_json', $accessPoint?->payload ? json_encode($accessPoint->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '');
    $allowedIpsText = old('allowed_ips_text', $accessPoint?->allowed_ips ? implode("\n", $accessPoint->allowed_ips) : '');
    $taskLabels = config('streamdeck-access.task_labels', []);
    $tasks = config('streamdeck-access.tasks', []);
@endphp

<form method="POST" action="{{ $action }}" class="streamdeck-access-form sda-form-card streamdeck-access-card">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="sda-form-header">
        <span class="sda-form-icon"><i class="fa-solid fa-keyboard" aria-hidden="true"></i></span>
        <div>
            <strong>{{ $isEdit ? 'Editar access point' : 'Novo access point' }}</strong>
            <span>Configura o endpoint externo que será chamado pelo Stream Deck.</span>
        </div>
    </div>

    <div class="streamdeck-access-grid">
        <div class="streamdeck-access-field streamdeck-access-grid-1">
            <label class="streamdeck-access-label" for="name">Nome</label>
            <input id="name" type="text" name="name" class="streamdeck-access-input" value="{{ old('name', $accessPoint?->name) }}" required maxlength="150">
            @error('name')<div class="sda-field-error">{{ $message }}</div>@enderror
        </div>

        <div class="streamdeck-access-field">
            <label class="streamdeck-access-label" for="slug">Slug</label>
            <input id="slug" type="text" name="slug" class="streamdeck-access-input" value="{{ old('slug', $accessPoint?->slug) }}" maxlength="160" placeholder="gerado automaticamente se vazio">
            @error('slug')<div class="sda-field-error">{{ $message }}</div>@enderror
        </div>

        <div class="streamdeck-access-field">
            <label class="streamdeck-access-label" for="type">Tipo</label>
            <select id="type" name="type" class="streamdeck-access-select" data-sda-type required>
                @foreach(config('streamdeck-access.types', []) as $type => $label)
                    <option value="{{ $type }}" @selected($selectedType === $type)>{{ $label }}</option>
                @endforeach
            </select>
            @error('type')<div class="sda-field-error">{{ $message }}</div>@enderror
        </div>

        <div class="streamdeck-access-field streamdeck-access-grid-1">
            <label class="streamdeck-access-label" for="description">Descrição</label>
            <textarea id="description" name="description" class="streamdeck-access-textarea" rows="3">{{ old('description', $accessPoint?->description) }}</textarea>
            @error('description')<div class="sda-field-error">{{ $message }}</div>@enderror
        </div>

        <div class="streamdeck-access-field streamdeck-access-grid-1" data-sda-section="redirect">
            <label class="streamdeck-access-label" for="target_url">URL / caminho interno</label>
            <input id="target_url" type="text" name="target_url" class="streamdeck-access-input" value="{{ old('target_url', $accessPoint?->target_url) }}" placeholder="/backoffice/encomendas ou https://app.exemplo.pt/backoffice/encomendas">
            <small class="sda-help-text">Para páginas internas, o utilizador continua a precisar de sessão autenticada no B.O.</small>
            @error('target_url')<div class="sda-field-error">{{ $message }}</div>@enderror
        </div>

        <div class="streamdeck-access-field" data-sda-section="task">
            <label class="streamdeck-access-label" for="task_key">Tarefa</label>
            <select id="task_key" name="task_key" class="streamdeck-access-select">
                <option value="">Selecionar tarefa</option>
                @foreach($tasks as $taskKey => $taskClass)
                    <option value="{{ $taskKey }}" @selected(old('task_key', $accessPoint?->task_key) === $taskKey)>{{ $taskLabels[$taskKey] ?? $taskKey }}</option>
                @endforeach
            </select>
            @error('task_key')<div class="sda-field-error">{{ $message }}</div>@enderror
        </div>

        <div class="streamdeck-access-field" data-sda-section="task">
            <label class="streamdeck-access-label" for="queue">Queue</label>
            <input id="queue" type="text" name="queue" class="streamdeck-access-input" value="{{ old('queue', $accessPoint?->queue) }}" placeholder="{{ config('streamdeck-access.default_queue', 'default') }}">
            @error('queue')<div class="sda-field-error">{{ $message }}</div>@enderror
        </div>

        <div class="streamdeck-access-field streamdeck-access-grid-1" data-sda-section="task">
            <label class="streamdeck-access-label" for="payload_json">Payload JSON</label>
            <textarea id="payload_json" name="payload_json" class="streamdeck-access-textarea sda-code-textarea" rows="8" placeholder='{"url":"https://www.exemplo.pt/","strategy":"mobile"}'>{{ $payloadJson }}</textarea>
            <small class="sda-help-text">Usado pela tarefa selecionada. Deixa vazio para tarefas sem parâmetros.</small>
            @error('payload_json')<div class="sda-field-error">{{ $message }}</div>@enderror
            @error('payload')<div class="sda-field-error">{{ $message }}</div>@enderror
        </div>

        <div class="streamdeck-access-field">
            <label class="streamdeck-access-label" for="expires_at">Expira em</label>
            <input id="expires_at" type="datetime-local" name="expires_at" class="streamdeck-access-input" value="{{ old('expires_at', $accessPoint?->expires_at ? $accessPoint->expires_at->format('Y-m-d\TH:i') : '') }}">
            @error('expires_at')<div class="sda-field-error">{{ $message }}</div>@enderror
        </div>

        <div class="streamdeck-access-field">
            <label class="streamdeck-access-label" for="max_uses">Máximo de utilizações</label>
            <input id="max_uses" type="number" min="1" name="max_uses" class="streamdeck-access-input" value="{{ old('max_uses', $accessPoint?->max_uses) }}">
            @error('max_uses')<div class="sda-field-error">{{ $message }}</div>@enderror
        </div>

        <div class="streamdeck-access-field">
            <label class="streamdeck-access-label" for="cooldown_seconds">Cooldown em segundos</label>
            <input id="cooldown_seconds" type="number" min="0" max="86400" name="cooldown_seconds" class="streamdeck-access-input" value="{{ old('cooldown_seconds', $accessPoint?->cooldown_seconds ?? 0) }}">
            @error('cooldown_seconds')<div class="sda-field-error">{{ $message }}</div>@enderror
        </div>

        <div class="streamdeck-access-field">
            <label class="streamdeck-access-label" for="allowed_ips_text">IPs permitidos</label>
            <textarea id="allowed_ips_text" name="allowed_ips_text" class="streamdeck-access-textarea" rows="4" placeholder="203.0.113.10&#10;203.0.113.*&#10;10.10.0.0/16">{{ $allowedIpsText }}</textarea>
            <small class="sda-help-text">Opcional. Suporta IP exato, wildcard e CIDR IPv4.</small>
            @error('allowed_ips_text')<div class="sda-field-error">{{ $message }}</div>@enderror
            @error('allowed_ips')<div class="sda-field-error">{{ $message }}</div>@enderror
        </div>

        <div class="streamdeck-access-field sda-checkbox-field">
            <input type="hidden" name="enabled" value="0">
            <label class="sda-checkbox">
                <input type="checkbox" name="enabled" value="1" @checked(old('enabled', $accessPoint?->enabled ?? true))>
                <span>Ativo</span>
            </label>
        </div>

        <div class="streamdeck-access-field sda-checkbox-field">
            <input type="hidden" name="respond_json" value="0">
            <label class="sda-checkbox">
                <input type="checkbox" name="respond_json" value="1" @checked(old('respond_json', $accessPoint?->respond_json ?? true))>
                <span>Responder JSON nas tarefas</span>
            </label>
        </div>
    </div>

    <div class="sda-form-actions">
        <a href="{{ route('streamdeck_access.index') }}" class="lsg-action-btn lsg-action-btn--secondary">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
            <span>Voltar</span>
        </a>
        <button type="submit" class="lsg-action-btn lsg-action-btn--primary">
            <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
            <span>Guardar</span>
        </button>
    </div>
</form>
