@extends(config('project-manager.layout', 'layouts.app'))

@section('content')
@include('project-manager::partials.styles')
@php
    use Illuminate\Support\Str;
    use Modules\ProjectManager\Services\ProjectManagerSectionRegistry;

    $detailSections = [];
    foreach ($groups as $groupLabel => $sectionKeys) {
        foreach ($sectionKeys as $key) {
            $meta = ProjectManagerSectionRegistry::get($key);
            if (!$meta) { continue; }
            $detailSections[$key] = array_merge($meta, [
                'key' => $key,
                'group' => $groupLabel,
                'count' => $summary[$key] ?? 0,
                'indexRoute' => route(ProjectManagerSectionRegistry::routeName($key, 'index'), $project->id),
                'createRoute' => route(ProjectManagerSectionRegistry::routeName($key, 'create'), $project->id),
                'storeRoute' => route(ProjectManagerSectionRegistry::routeName($key, 'store'), $project->id),
            ]);
        }
    }

    $firstKey = array_key_first($detailSections);


    $quickFields = [
        'modules' => ['name','type','status','description'],
        'links' => ['type','label','url','description','is_primary'],
        'contacts' => ['name','role','company','email','phone','notes','is_primary'],
        'external-dependencies' => ['name','type','owner','status','risk_level','needed_by','description'],
        'design-profiles' => ['name','description','layout_rules','component_rules','icon_rules','status','is_primary'],
        'design-tokens' => ['token_key','token_type','value','usage_context','is_active'],
        'assets' => ['type','name','variant','description','is_primary'],
        'technical-stack' => ['project_module_id','category','name','version','purpose','is_required'],
        'environments' => ['name','type','url','repository_branch','php_version','node_version','is_active','notes'],
        'guidelines' => ['project_module_id','category','title','importance','status','content'],
        'documentation' => ['project_module_id','parent_id','type','title','summary','status','is_pinned'],
        'decisions' => ['project_module_id','title','status','context','decision','impact'],
        'notes' => ['project_module_id','project_task_id','type','title','content','visibility','is_pinned'],
        'blocks' => ['type','title','summary','content','status','is_pinned'],
        'activity' => ['event_type','entity_type','description'],
    ];

    $fieldLabels = [
        'project_module_id' => 'Área / módulo', 'parent_id' => 'Registo pai', 'depends_on_id' => 'Depende de',
        'task_id' => 'Task', 'depends_on_task_id' => 'Depende da task', 'project_task_id' => 'Task associada',
        'name' => 'Nome', 'title' => 'Título', 'label' => 'Etiqueta', 'type' => 'Tipo', 'category' => 'Categoria',
        'status' => 'Estado', 'description' => 'Descrição', 'summary' => 'Resumo', 'content' => 'Conteúdo',
        'url' => 'URL', 'email' => 'Email', 'phone' => 'Telefone', 'company' => 'Empresa', 'role' => 'Função',
        'owner' => 'Responsável', 'risk_level' => 'Risco', 'needed_by' => 'Necessário até',
        'version' => 'Versão', 'purpose' => 'Objetivo', 'notes' => 'Notas', 'documentation_url' => 'URL documentação',
        'repository_branch' => 'Branch', 'php_version' => 'PHP', 'node_version' => 'Node',
        'importance' => 'Importância', 'visibility' => 'Visibilidade', 'variant' => 'Variante', 'usage_context' => 'Contexto de uso',
        'token_key' => 'Token', 'token_type' => 'Tipo de token', 'value' => 'Valor',
        'layout_rules' => 'Regras de layout', 'component_rules' => 'Regras de componentes', 'icon_rules' => 'Regras de ícones',
        'is_primary' => 'Principal', 'is_active' => 'Ativo', 'is_required' => 'Obrigatório', 'is_pinned' => 'Fixar',
    ];

    $fieldHelp = [
        'project_module_id' => 'Escolhe pelo nome da área. Não é necessário saber IDs.',
        'parent_id' => 'Opcional. Usa apenas se este conteúdo pertencer a outro bloco.',
        'project_task_id' => 'Opcional. Liga a nota ou bloqueio a uma task existente.',
        'name' => 'Nome curto e fácil de identificar.',
        'title' => 'Título claro para encontrar rapidamente depois.',
        'description' => 'Descreve só o essencial. Podes detalhar mais tarde.',
        'content' => 'Conteúdo principal. Mantém em formato prático e reutilizável.',
        'url' => 'Cola o link completo, incluindo https://.',
        'status' => 'Deixa ativo se ainda está em uso no projeto.',
        'is_primary' => 'Marca apenas quando este item for o principal deste tipo.',
    ];

    $placeholders = [
        'name' => 'Ex: Backend OMS, Logo principal, Produção',
        'title' => 'Ex: Regra para controllers, Decisão sobre layout',
        'description' => 'Resumo rápido do objetivo deste registo',
        'summary' => 'Resumo curto para leitura rápida',
        'content' => 'Escreve aqui a regra, nota ou documentação essencial',
        'url' => 'https://...',
        'token_key' => 'Ex: color-primary, radius-card, font-base',
        'value' => 'Ex: #C9A646, 5px, Inter',
    ];
@endphp

<div class="lsg-content pm-wrap">
    <div class="pm-shell">
        @include('project-manager::partials.project-tabs', ['activeTab' => 'details'])

        <style>
            .pm-project-identity-strip{display:grid;grid-template-columns:110px minmax(0,1fr) 300px;gap:14px;align-items:stretch;margin-bottom:14px;}
            .pm-project-logo-box{border:1px solid var(--pm-border);border-radius:10px;background:linear-gradient(135deg,#fff,#faf8ef);display:flex;align-items:center;justify-content:center;min-height:104px;overflow:hidden;}
            .pm-project-logo-box img{max-width:100%;max-height:96px;object-fit:contain;padding:10px;}
            .pm-project-logo-fallback{font-size:32px;font-weight:900;color:#8a6d18;letter-spacing:-.08em;}
            .pm-project-state-card{border:1px solid var(--pm-border);border-radius:10px;background:linear-gradient(135deg,#fff,#fbfaf4);padding:12px;}
            .pm-project-state-form{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:8px;align-items:end;}
            .pm-project-state-form select{border:1px solid var(--pm-border);border-radius:8px;background:#fff;padding:8px 10px;width:100%;}
            .pm-upload-zone{border:1px dashed rgba(201,166,70,.75);border-radius:10px;background:linear-gradient(135deg,rgba(201,166,70,.08),rgba(255,255,255,.9));padding:12px;}
            .pm-upload-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
            .pm-upload-form-grid .full{grid-column:1 / -1;}
            .pm-upload-form-grid input,.pm-upload-form-grid select,.pm-upload-form-grid textarea{width:100%;border:1px solid var(--pm-border);border-radius:8px;padding:8px 10px;background:#fff;}
            .pm-asset-preview-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px;margin-top:12px;}
            .pm-asset-preview{border:1px solid var(--pm-border);border-radius:8px;background:#fff;padding:8px;min-height:110px;}
            .pm-asset-preview img{width:100%;height:74px;object-fit:contain;background:#f8fafc;border-radius:6px;margin-bottom:6px;}
            .pm-asset-preview strong{display:block;font-size:12px;line-height:1.2;}
            .pm-guided-form{background:linear-gradient(135deg,#fff,#fbfaf4);border:1px solid rgba(201,166,70,.22);border-radius:12px;padding:14px;}
            .pm-guided-form-intro{display:flex;align-items:flex-start;gap:10px;background:rgba(201,166,70,.08);border:1px solid rgba(201,166,70,.18);border-radius:10px;padding:10px 12px;margin-bottom:14px;color:#4b5563;font-size:12px;}
            .pm-guided-form-intro i{color:#9a7619;margin-top:2px;}
            .pm-guided-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;}
            .pm-guided-field{border:1px solid rgba(17,24,39,.08);border-radius:10px;background:#fff;padding:10px;}
            .pm-guided-field.full{grid-column:1 / -1;}
            .pm-guided-field label{display:flex;align-items:center;justify-content:space-between;gap:8px;font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:.06em;color:#374151;margin-bottom:6px;}
            .pm-guided-field .hint{font-size:11px;color:#6b7280;margin-top:6px;line-height:1.35;}
            .pm-guided-field input,.pm-guided-field select,.pm-guided-field textarea{width:100%;border:1px solid var(--pm-border);border-radius:8px;background:#fff;padding:8px 10px;color:var(--pm-text);}
            .pm-guided-field textarea{resize:vertical;min-height:92px;}
            .pm-guided-field.is-boolean{display:flex;align-items:center;justify-content:space-between;gap:10px;}
            .pm-guided-field.is-boolean label{margin:0;text-transform:none;font-size:13px;letter-spacing:0;}
            .pm-guided-field.is-boolean select{max-width:110px;}
            .pm-field-hidden-note{border:1px dashed rgba(148,163,184,.5);background:#f8fafc;color:#64748b;border-radius:10px;padding:10px;font-size:12px;}
            @media(max-width:760px){.pm-guided-grid{grid-template-columns:1fr}}
            @media(max-width:1100px){.pm-project-identity-strip{grid-template-columns:90px 1fr}.pm-project-state-card{grid-column:1 / -1;}}
            @media(max-width:700px){.pm-project-identity-strip{grid-template-columns:1fr}.pm-upload-form-grid{grid-template-columns:1fr}.pm-project-state-form{grid-template-columns:1fr}}
        </style>

        <div class="pm-project-identity-strip">
            <div class="pm-project-logo-box">
                @php $logoUrl = $project->logo ?? ($primaryLogo->public_url ?? null); @endphp
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $project->name }} logo">
                @else
                    <div class="pm-project-logo-fallback">{{ strtoupper(substr($project->name ?? 'P', 0, 2)) }}</div>
                @endif
            </div>

            <div class="pm-card pm-card--compact mb-0">
                <div class="pm-card-title"><i class="fa-solid fa-diagram-project"></i> {{ $project->name }}</div>
                <div class="pm-card-subtitle mb-0">Project Details concentra estrutura, identidade visual, uploads, stack técnica, documentação e decisões. As operações principais são feitas aqui sem sair da página.</div>
            </div>

            <div class="pm-project-state-card">
                <form method="POST" action="{{ route('project_manager.projects.status.update', $project->id) }}" class="pm-project-state-form">
                    @csrf
                    <div>
                        <label class="pm-form-label">Estado do projeto</label>
                        <select name="status">
                            @foreach(['in_progress' => 'Em execução', 'hold' => 'Hold', 'pending' => 'Pending', 'done' => 'Done'] as $statusValue => $statusLabel)
                                <option value="{{ $statusValue }}" {{ in_array(($project->status ?? ''), [$statusValue, str_replace('_', ' ', $statusValue)], true) ? 'selected' : '' }}>{{ $statusLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="pm-btn pm-btn--primary" type="submit"><i class="fa-solid fa-floppy-disk"></i></button>
                </form>
            </div>
        </div>

        <div class="pm-detail-layout" data-pm-details-layout>
            <aside class="pm-detail-nav pm-detail-nav--wide" aria-label="Project details sections">
                @foreach($detailSections as $key => $section)
                    <button type="button" class="pm-detail-nav-item {{ $key === $firstKey ? 'is-active' : '' }}" data-pm-detail-target="{{ $key }}">
                        <span class="pm-detail-nav-icon"><i class="{{ $section['icon'] ?? 'fa-solid fa-circle' }}"></i></span>
                        <span class="pm-detail-nav-text"><strong>{{ $section['label'] }}</strong><small>{{ $section['group'] }}</small></span>
                        <span class="pm-detail-nav-count">{{ $section['count'] }}</span>
                    </button>
                @endforeach
            </aside>

            <section class="pm-detail-main">
                @foreach($detailSections as $key => $section)
                    @php $records = $detailRecords[$key] ?? collect(); @endphp
                    <article class="pm-detail-panel {{ $key === $firstKey ? 'is-active' : '' }}" data-pm-detail-panel="{{ $key }}">
                        <div class="pm-detail-panel-head">
                            <div>
                                <div class="pm-card-title mb-1"><i class="{{ $section['icon'] ?? 'fa-solid fa-circle' }}"></i> {{ $section['label'] }}</div>
                                <div class="pm-card-subtitle mb-0">{{ $section['description'] ?? '' }}</div>
                            </div>
                            <div class="pm-actions pm-actions--right">
                                <button type="button" class="pm-btn pm-btn--success" data-pm-open-modal="modal-{{ $key }}"><i class="fa-solid fa-plus"></i> Novo</button>
                                <a class="pm-btn pm-btn--primary" href="{{ $section['indexRoute'] }}"><i class="fa-solid fa-table-list"></i> Lista completa</a>
                            </div>
                        </div>

                        <div class="pm-detail-content-grid pm-detail-content-grid--compact">
                            <div class="pm-detail-info-card">
                                <div class="pm-detail-info-title">Caminho operacional</div>
                                <div class="pm-detail-operation-path"><span>Project Details</span><i class="fa-solid fa-angle-right"></i><span>{{ $section['group'] }}</span><i class="fa-solid fa-angle-right"></i><strong>{{ $section['label'] }}</strong></div>
                            </div>
                            <div class="pm-detail-info-card">
                                <div class="pm-detail-info-title">Registos</div>
                                <div class="pm-detail-big-number">{{ $section['count'] }}</div>
                            </div>
                        </div>

                        @if($key === 'assets')
                            <div class="pm-upload-zone mt-3">
                                <div class="pm-section-bar">
                                    <div>
                                        <div class="pm-card-title"><i class="fa-solid fa-cloud-arrow-up"></i> Upload de assets</div>
                                        <div class="pm-card-subtitle mb-0">Carrega logos, prints, mockups e ficheiros do projeto sem sair desta página.</div>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('project_manager.projects.assets.upload', $project->id) }}" enctype="multipart/form-data">
                                    @csrf
                                    <div class="pm-upload-form-grid">
                                        <div>
                                            <label class="pm-form-label">Ficheiro</label>
                                            <input type="file" name="asset_file" required>
                                        </div>
                                        <div>
                                            <label class="pm-form-label">Tipo</label>
                                            <select name="type" required>
                                                <option value="logo">Logo</option>
                                                <option value="image">Print / imagem</option>
                                                <option value="mockup">Mockup</option>
                                                <option value="document">Documento</option>
                                                <option value="font">Fonte</option>
                                                <option value="icon">Ícone</option>
                                                <option value="other">Outro</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="pm-form-label">Nome</label>
                                            <input type="text" name="name" placeholder="Ex: Logo principal, Homepage print...">
                                        </div>
                                        <div>
                                            <label class="pm-form-label">Variante / língua</label>
                                            <input type="text" name="variant" placeholder="Ex: dark, light, mobile, EN...">
                                        </div>
                                        <div class="full">
                                            <label class="pm-form-label">Descrição / regras de uso</label>
                                            <textarea name="description" rows="2" placeholder="Notas rápidas sobre onde usar este asset"></textarea>
                                        </div>
                                        <div class="full">
                                            <label><input type="checkbox" name="is_primary" value="1"> Definir como asset principal deste tipo</label>
                                        </div>
                                    </div>
                                    <div class="pm-detail-actions-footer">
                                        <button class="pm-btn pm-btn--success" type="submit"><i class="fa-solid fa-upload"></i> Carregar asset</button>
                                    </div>
                                </form>

                                @if(($assetRecords ?? collect())->count())
                                    <div class="pm-asset-preview-grid">
                                        @foreach(($assetRecords ?? collect())->take(8) as $asset)
                                            <div class="pm-asset-preview">
                                                @if(!empty($asset->public_url) && str_starts_with((string)($asset->mime_type ?? ''), 'image/'))
                                                    <img src="{{ $asset->public_url }}" alt="{{ $asset->name ?? 'Asset' }}">
                                                @else
                                                    <div class="pm-empty pm-empty--small"><i class="fa-solid fa-file"></i></div>
                                                @endif
                                                <strong>{{ $asset->name ?? 'Asset' }}</strong>
                                                <div class="pm-muted pm-small">{{ $asset->type ?? 'file' }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif

                        <div class="pm-card pm-card--compact mt-3">
                            <div class="pm-section-bar">
                                <div>
                                    <div class="pm-card-title"><i class="fa-solid fa-list"></i> Últimos registos</div>
                                    <div class="pm-card-subtitle mb-0">Edição completa disponível na lista. Inserção rápida disponível em modal.</div>
                                </div>
                            </div>

                            <div class="pm-record-list">
                                @forelse($records as $record)
                                    @php
                                        $main = $record->title ?? $record->name ?? $record->label ?? $record->token_key ?? ('Registo #' . $record->id);
                                        $secondary = $record->description ?? $record->summary ?? $record->content ?? $record->notes ?? $record->status ?? '';
                                    @endphp
                                    <div class="pm-record-row">
                                        <div>
                                            <strong>{{ $main }}</strong>
                                            @if($secondary)<div class="pm-muted pm-small">{{ Str::limit(strip_tags($secondary), 120) }}</div>@endif
                                        </div>
                                        <div class="pm-record-actions">
                                            <a class="pm-btn pm-btn--compact pm-btn--warning" href="{{ route(ProjectManagerSectionRegistry::routeName($key, 'edit'), [$project->id, $record->id]) }}"><i class="fa-solid fa-pencil"></i></a>
                                            <form method="POST" action="{{ route(ProjectManagerSectionRegistry::routeName($key, 'destroy'), [$project->id, $record->id]) }}" onsubmit="return confirm('Remover este registo?')">
                                                @csrf @method('DELETE')
                                                <button class="pm-btn pm-btn--compact pm-btn--danger"><i class="fa-solid fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    <div class="pm-empty">Ainda não existem registos nesta área.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="pm-card pm-card--compact mt-3">
                            <div class="pm-card-title"><i class="fa-solid fa-pen-to-square"></i> Inserção simplificada</div>
                            <div class="pm-card-subtitle mb-2">O formulário rápido mostra apenas os campos essenciais. As relações são selecionadas por nome.</div>
                            <div class="pm-field-chip-wrap">
                                @foreach(($quickFields[$key] ?? []) as $field)
                                    <span class="pm-field-chip">{{ $fieldLabels[$field] ?? ucfirst(str_replace('_', ' ', $field)) }}</span>
                                @endforeach
                            </div>
                        </div>
                    </article>

                    <div class="pm-modal-backdrop" id="modal-{{ $key }}" data-pm-modal>
                        <div class="pm-modal-card pm-modal-card--wide">
                            <div class="pm-section-bar">
                                <div>
                                    <div class="pm-card-title"><i class="{{ $section['icon'] ?? 'fa-solid fa-circle' }}"></i> Novo registo · {{ $section['label'] }}</div>
                                    <div class="pm-card-subtitle mb-0">Inserção rápida sem sair de Project Details.</div>
                                </div>
                                <button type="button" class="pm-btn pm-btn--compact" data-pm-close-modal><i class="fa-solid fa-xmark"></i></button>
                            </div>
                            <form method="POST" action="{{ $section['storeRoute'] }}" class="pm-guided-form">
                                @csrf
                                <input type="hidden" name="return_to_details" value="1">
                                @php
                                    $fieldsForModal = $quickFields[$key] ?? array_slice(($section['fields'] ?? []), 0, 6);
                                    $hiddenFields = [];
                                @endphp
                                <div class="pm-guided-form-intro">
                                    <i class="fa-solid fa-circle-info"></i>
                                    <div>
                                        <strong>Inserção rápida contextual.</strong><br>
                                        Os campos técnicos foram reduzidos ao essencial. Sempre que existir uma relação, escolhes pelo nome e não pelo ID.
                                    </div>
                                </div>
                                <div class="pm-guided-grid">
                                    @foreach($fieldsForModal as $field)
                                        @php
                                            $isTextarea = in_array($field, $section['textarea'] ?? [], true) || in_array($field, ['description','summary','content','notes','layout_rules','component_rules','icon_rules','context','decision','reason','impact'], true);
                                            $isBoolean = in_array($field, $section['booleans'] ?? [], true);
                                            $options = $formOptions[$key][$field] ?? null;
                                            $default = old($field, $fieldDefaults[$field] ?? null);
                                            $isIdLike = $field === 'id' || str_ends_with($field, '_id');
                                            $label = $fieldLabels[$field] ?? ucfirst(str_replace('_', ' ', $field));
                                            $help = $fieldHelp[$field] ?? null;
                                            $placeholder = $placeholders[$field] ?? '';
                                        @endphp

                                        @if($isIdLike && empty($options))
                                            @if(!is_null($default))
                                                <input type="hidden" name="{{ $field }}" value="{{ $default }}">
                                            @else
                                                @php $hiddenFields[] = $label; @endphp
                                            @endif
                                            @continue
                                        @endif

                                        <div class="pm-guided-field {{ $isTextarea ? 'full' : '' }} {{ $isBoolean ? 'is-boolean' : '' }}">
                                            <label for="pm-field-{{ $key }}-{{ $field }}">
                                                <span>{{ $label }}</span>
                                                @if(in_array($field, ['name','title','type','status','category'], true))<small>*</small>@endif
                                            </label>

                                            @if($options)
                                                <select id="pm-field-{{ $key }}-{{ $field }}" name="{{ $field }}">
                                                    <option value="">{{ $isIdLike ? 'Selecionar pelo nome' : 'Selecionar' }}</option>
                                                    @foreach($options as $optionValue => $optionLabel)
                                                        <option value="{{ $optionValue }}" {{ (string)$default === (string)$optionValue ? 'selected' : '' }}>{{ $optionLabel }}</option>
                                                    @endforeach
                                                </select>
                                            @elseif($isBoolean)
                                                <select id="pm-field-{{ $key }}-{{ $field }}" name="{{ $field }}">
                                                    <option value="0" {{ (string)$default === '0' ? 'selected' : '' }}>Não</option>
                                                    <option value="1" {{ (string)$default === '1' ? 'selected' : '' }}>Sim</option>
                                                </select>
                                            @elseif($isTextarea)
                                                <textarea id="pm-field-{{ $key }}-{{ $field }}" name="{{ $field }}" rows="3" placeholder="{{ $placeholder }}">{{ old($field) }}</textarea>
                                            @elseif(str_contains($field, 'date') || str_ends_with($field, '_at') || in_array($field, ['needed_by'], true))
                                                <input id="pm-field-{{ $key }}-{{ $field }}" type="date" name="{{ $field }}" value="{{ old($field) }}">
                                            @elseif($field === 'url' || str_ends_with($field, '_url'))
                                                <input id="pm-field-{{ $key }}-{{ $field }}" type="url" name="{{ $field }}" value="{{ old($field) }}" placeholder="{{ $placeholder }}">
                                            @elseif($field === 'email')
                                                <input id="pm-field-{{ $key }}-{{ $field }}" type="email" name="{{ $field }}" value="{{ old($field) }}" placeholder="email@dominio.pt">
                                            @elseif(str_contains($field, 'order') || in_array($field, ['priority'], true))
                                                <input id="pm-field-{{ $key }}-{{ $field }}" type="number" name="{{ $field }}" value="{{ old($field, $default) }}" placeholder="{{ $placeholder }}">
                                            @else
                                                <input id="pm-field-{{ $key }}-{{ $field }}" type="text" name="{{ $field }}" value="{{ old($field, $default) }}" placeholder="{{ $placeholder }}">
                                            @endif

                                            @if($help)<div class="hint">{{ $help }}</div>@endif
                                        </div>
                                    @endforeach

                                    @if(count($hiddenFields))
                                        <div class="pm-field-hidden-note full">
                                            Campos técnicos omitidos por não existirem opções de seleção: {{ implode(', ', $hiddenFields) }}.
                                        </div>
                                    @endif
                                </div>
                                <div class="pm-detail-actions-footer">
                                    <button type="button" class="pm-btn" data-pm-close-modal>Cancelar</button>
                                    <button type="submit" class="pm-btn pm-btn--success"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach
            </section>
        </div>
    </div>
</div>

<script>
(function(){
    const layout = document.querySelector('[data-pm-details-layout]');
    if (layout) {
        const buttons = layout.querySelectorAll('[data-pm-detail-target]');
        const panels = layout.querySelectorAll('[data-pm-detail-panel]');
        function activate(key, updateHash = true) {
            buttons.forEach(button => button.classList.toggle('is-active', button.dataset.pmDetailTarget === key));
            panels.forEach(panel => panel.classList.toggle('is-active', panel.dataset.pmDetailPanel === key));
            if (updateHash) history.replaceState(null, '', '#details-' + key);
        }
        buttons.forEach(button => button.addEventListener('click', function(){ activate(this.dataset.pmDetailTarget); }));
        if (window.location.hash && window.location.hash.indexOf('#details-') === 0) {
            const key = window.location.hash.replace('#details-', '');
            if (layout.querySelector('[data-pm-detail-target="' + key + '"]')) activate(key, false);
        }
    }

    document.querySelectorAll('[data-pm-open-modal]').forEach(button => {
        button.addEventListener('click', function(){
            const modal = document.getElementById(this.dataset.pmOpenModal);
            if (modal) modal.classList.add('is-visible');
        });
    });
    document.querySelectorAll('[data-pm-close-modal]').forEach(button => {
        button.addEventListener('click', function(){
            const modal = this.closest('[data-pm-modal]');
            if (modal) modal.classList.remove('is-visible');
        });
    });
})();
</script>
@endsection
