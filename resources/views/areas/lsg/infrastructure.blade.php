@extends('layouts.app')

@push('styles')
    <style>
        .lsg-infra-shell{display:grid;gap:14px;min-width:0}
        .lsg-infra-grid{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:12px}
        .lsg-infra-card{grid-column:span 4;position:relative;display:flex;flex-direction:column;gap:12px;min-width:0;min-height:178px;border:1px solid var(--border-soft,rgba(148,163,184,.22));background:linear-gradient(180deg,var(--bg-panel,var(--card-bg,#fff)),var(--bg-panel-soft,rgba(148,163,184,.06)));color:var(--text-primary,#111827);padding:16px;overflow:hidden}
        .lsg-infra-card--wide{grid-column:span 6}
        .lsg-infra-card--hero{grid-column:span 8;min-height:220px}
        .lsg-infra-card::before{content:"";position:absolute;inset:0 0 auto 0;height:3px;background:linear-gradient(90deg,var(--infra-accent,#d4a017),transparent);opacity:.85}
        .lsg-infra-card__head{display:grid;grid-template-columns:46px minmax(0,1fr);gap:12px;align-items:center;position:relative}
        .lsg-infra-icon{width:46px;height:46px;display:grid;place-items:center;border:1px solid color-mix(in srgb,var(--infra-accent,#d4a017) 38%,transparent);background:color-mix(in srgb,var(--infra-accent,#d4a017) 14%,transparent);color:var(--infra-accent,#d4a017);font-size:18px}
        .lsg-infra-title{min-width:0}
        .lsg-infra-title span{display:block;color:var(--infra-accent,#d4a017);font-size:11px;font-weight:900;letter-spacing:.06em;text-transform:uppercase}
        .lsg-infra-title strong{display:block;margin-top:2px;font-size:1rem;font-weight:950;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .lsg-infra-copy{margin:0;color:var(--text-muted,#64748b);font-size:.86rem;line-height:1.55;position:relative}
        .lsg-infra-card--hero .lsg-infra-copy{max-width:720px}
        .lsg-infra-chip-row{display:flex;gap:6px;flex-wrap:wrap;margin-top:auto}
        .lsg-infra-chip{display:inline-flex;align-items:center;gap:5px;border:1px solid var(--border-soft,rgba(148,163,184,.2));background:rgba(148,163,184,.06);color:var(--text-muted,#64748b);padding:4px 7px;font-size:.74rem;font-weight:850}
        .lsg-infra-actions{display:flex;flex-wrap:wrap;gap:7px;position:relative}
        .lsg-infra-actions .btn{display:inline-flex;align-items:center;gap:6px;font-weight:850}
        .lsg-infra-card--ai{--infra-accent:#60a5fa}
        .lsg-infra-card--backup{--infra-accent:#22c55e}
        .lsg-infra-card--ops{--infra-accent:#f59e0b}
        .lsg-infra-card--docs{--infra-accent:#d4a017}
        .lsg-infra-card--monitor{--infra-accent:#38bdf8}
        .lsg-infra-card--audit{--infra-accent:#a78bfa}
        .lsg-infra-quick{grid-column:span 4;display:grid;gap:12px}
        .lsg-infra-mini{display:grid;grid-template-columns:42px minmax(0,1fr) auto;gap:10px;align-items:center;border:1px solid var(--border-soft,rgba(148,163,184,.22));background:var(--bg-panel,var(--card-bg,#fff));padding:12px;color:var(--text-primary,#111827);text-decoration:none}
        .lsg-infra-mini:hover{border-color:rgba(212,160,23,.5);text-decoration:none;color:var(--text-primary,#111827)}
        .lsg-infra-mini i:first-child{width:42px;height:42px;display:grid;place-items:center;background:rgba(212,160,23,.12);color:#d4a017;border:1px solid rgba(212,160,23,.22)}
        .lsg-infra-mini strong,.lsg-infra-mini span{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .lsg-infra-mini span{color:var(--text-muted,#64748b);font-size:.78rem}
        .lsg-infra-mini .fa-chevron-right{color:var(--text-muted,#64748b);font-size:.75rem}
        @media(max-width:1200px){.lsg-infra-card,.lsg-infra-card--wide,.lsg-infra-card--hero,.lsg-infra-quick{grid-column:span 6}}
        @media(max-width:760px){.lsg-infra-grid{grid-template-columns:1fr}.lsg-infra-card,.lsg-infra-card--wide,.lsg-infra-card--hero,.lsg-infra-quick{grid-column:span 1}.lsg-infra-card{min-height:auto}.lsg-infra-actions .btn{width:100%;justify-content:center}}
    </style>
@endpush

@section('content')
    <div class="lsg-infra-shell">
        <div class="lsg-infra-grid">
            <section class="lsg-infra-card lsg-infra-card--hero">
                <div class="lsg-infra-card__head">
                    <div class="lsg-infra-icon"><i class="fa-solid fa-globe"></i></div>
                    <div class="lsg-infra-title">
                        <span>Core</span>
                        <strong>Sites, dominios e presenca digital</strong>
                    </div>
                </div>
                <p class="lsg-infra-copy">Centro operacional para sites LSG, dominios, PageSpeed, lojas e ligacoes a projetos. E o ponto de partida para gerir a infraestrutura visivel do grupo.</p>
                <div class="lsg-infra-chip-row">
                    <span class="lsg-infra-chip"><i class="fa-solid fa-sitemap"></i> Site Manager</span>
                    <span class="lsg-infra-chip"><i class="fa-solid fa-gauge-high"></i> PageSpeed</span>
                    <span class="lsg-infra-chip"><i class="fa-solid fa-diagram-project"></i> Projetos</span>
                </div>
                <div class="lsg-infra-actions">
                    @if(Route::has('lsg.site_manager.dashboard'))<a class="btn btn-sm btn-outline-primary" href="{{ route('lsg.site_manager.dashboard') }}"><i class="fa-solid fa-chart-line"></i> Dashboard</a>@endif
                    @if(Route::has('lsg.site_manager.sites.index'))<a class="btn btn-sm btn-outline-primary" href="{{ route('lsg.site_manager.sites.index') }}"><i class="fa-solid fa-globe"></i> Sites</a>@endif
                    @if(Route::has('lsg.site_manager.sites.create'))<a class="btn btn-sm btn-outline-success" href="{{ route('lsg.site_manager.sites.create') }}"><i class="fa-solid fa-plus"></i> Novo site</a>@endif
                </div>
            </section>

            <div class="lsg-infra-quick">
                @if(Route::has('admin.infrastructure.documentation.index'))
                    <a class="lsg-infra-mini" href="{{ route('admin.infrastructure.documentation.index') }}">
                        <i class="fa-solid fa-book"></i>
                        <span><strong>Documentacao tecnica</strong><span>Modulos, APIs e notas internas</span></span>
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                @endif
                @if(Route::has('admin.infrastructure.ai-backups.index'))
                    <a class="lsg-infra-mini" href="{{ route('admin.infrastructure.ai-backups.index') }}">
                        <i class="fa-solid fa-server"></i>
                        <span><strong>AI Server Backups</strong><span>Backups, checksum e logs</span></span>
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                @endif
                @if(Route::has('admin.lsg-ai.index'))
                    <a class="lsg-infra-mini" href="{{ route('admin.lsg-ai.index') }}">
                        <i class="fa-solid fa-brain"></i>
                        <span><strong>LSG AI Gateway</strong><span>Health e testes AI</span></span>
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                @endif
            </div>

            <section class="lsg-infra-card lsg-infra-card--ai">
                <div class="lsg-infra-card__head">
                    <div class="lsg-infra-icon"><i class="fa-solid fa-brain"></i></div>
                    <div class="lsg-infra-title"><span>AI</span><strong>Servidor AI LSG</strong></div>
                </div>
                <p class="lsg-infra-copy">Health, testes, OCR, Vision e gateway local exposto por API protegida.</p>
                <div class="lsg-infra-actions">
                    @if(Route::has('admin.lsg-ai.index'))<a class="btn btn-sm btn-outline-primary" href="{{ route('admin.lsg-ai.index') }}"><i class="fa-solid fa-heart-pulse"></i> Gateway</a>@endif
                    @if(Route::has('document-manager.ai.index'))<a class="btn btn-sm btn-outline-primary" href="{{ route('document-manager.ai.index') }}"><i class="fa-solid fa-file-lines"></i> OCR</a>@endif
                </div>
            </section>

            <section class="lsg-infra-card lsg-infra-card--backup">
                <div class="lsg-infra-card__head">
                    <div class="lsg-infra-icon"><i class="fa-solid fa-server"></i></div>
                    <div class="lsg-infra-title"><span>Protecao</span><strong>Backups AI</strong></div>
                </div>
                <p class="lsg-infra-copy">Listagem, validacao, download e limpeza dos backups do servidor AI.</p>
                <div class="lsg-infra-actions">
                    @if(Route::has('admin.infrastructure.ai-backups.index'))<a class="btn btn-sm btn-outline-primary" href="{{ route('admin.infrastructure.ai-backups.index') }}"><i class="fa-solid fa-box-archive"></i> Abrir</a>@endif
                    @if(Route::has('admin.infrastructure.ai-backups.logs'))<a class="btn btn-sm btn-outline-primary" href="{{ route('admin.infrastructure.ai-backups.logs') }}"><i class="fa-solid fa-file-lines"></i> Logs</a>@endif
                </div>
            </section>

            <section class="lsg-infra-card lsg-infra-card--docs">
                <div class="lsg-infra-card__head">
                    <div class="lsg-infra-icon"><i class="fa-solid fa-book"></i></div>
                    <div class="lsg-infra-title"><span>Conhecimento</span><strong>Documentacao tecnica</strong></div>
                </div>
                <p class="lsg-infra-copy">Consulta rapida de documentacao dos modulos, integracoes AI e notas internas.</p>
                <div class="lsg-infra-actions">
                    @if(Route::has('admin.infrastructure.documentation.index'))<a class="btn btn-sm btn-outline-primary" href="{{ route('admin.infrastructure.documentation.index') }}"><i class="fa-solid fa-book-open"></i> Abrir biblioteca</a>@endif
                </div>
            </section>

            <section class="lsg-infra-card lsg-infra-card--ops">
                <div class="lsg-infra-card__head">
                    <div class="lsg-infra-icon"><i class="fa-solid fa-screwdriver-wrench"></i></div>
                    <div class="lsg-infra-title"><span>Operacao</span><strong>Ferramentas operacionais</strong></div>
                </div>
                <p class="lsg-infra-copy">Manutencao, ambiente e configuracao tecnica do Webtools.</p>
                <div class="lsg-infra-actions">
                    @if(Route::has('system-tools.index'))<a class="btn btn-sm btn-outline-primary" href="{{ route('system-tools.index') }}"><i class="fa-solid fa-toolbox"></i> System Tools</a>@endif
                    @if(Route::has('environment-manager.index'))<a class="btn btn-sm btn-outline-primary" href="{{ route('environment-manager.index') }}"><i class="fa-solid fa-sliders"></i> Environment</a>@endif
                    @if(Route::has('settings.index'))<a class="btn btn-sm btn-outline-primary" href="{{ route('settings.index') }}"><i class="fa-solid fa-cog"></i> Settings</a>@endif
                </div>
            </section>

            <section class="lsg-infra-card lsg-infra-card--monitor">
                <div class="lsg-infra-card__head">
                    <div class="lsg-infra-icon"><i class="fa-solid fa-chart-line"></i></div>
                    <div class="lsg-infra-title"><span>Estado</span><strong>Monitorizacao</strong></div>
                </div>
                <p class="lsg-infra-copy">Saude dos modulos, integracoes, filas e logs tecnicos.</p>
                <div class="lsg-infra-actions">
                    @if(Route::has('module_health.dashboard'))<a class="btn btn-sm btn-outline-primary" href="{{ route('module_health.dashboard') }}"><i class="fa-solid fa-heart-pulse"></i> Module Health</a>@endif
                    @if(Route::has('integration_health.dashboard'))<a class="btn btn-sm btn-outline-primary" href="{{ route('integration_health.dashboard') }}"><i class="fa-solid fa-plug-circle-check"></i> Integration</a>@endif
                    @if(Route::has('system-logs.index'))<a class="btn btn-sm btn-outline-primary" href="{{ route('system-logs.index') }}"><i class="fa-solid fa-file-lines"></i> Logs</a>@endif
                    @if(Route::has('job-queue-monitor.index'))<a class="btn btn-sm btn-outline-primary" href="{{ route('job-queue-monitor.index') }}"><i class="fa-solid fa-list-check"></i> Queues</a>@endif
                </div>
            </section>

            <section class="lsg-infra-card lsg-infra-card--audit">
                <div class="lsg-infra-card__head">
                    <div class="lsg-infra-icon"><i class="fa-solid fa-clipboard-list"></i></div>
                    <div class="lsg-infra-title"><span>Governanca</span><strong>Auditoria e compliance</strong></div>
                </div>
                <p class="lsg-infra-copy">Auditoria central, compliance dos modulos e validacoes tecnicas.</p>
                <div class="lsg-infra-actions">
                    @if(Route::has('audit_log_central.dashboard'))<a class="btn btn-sm btn-outline-primary" href="{{ route('audit_log_central.dashboard') }}"><i class="fa-solid fa-clipboard-list"></i> Audit Log</a>@endif
                    @if(Route::has('module_compliance_center.dashboard'))<a class="btn btn-sm btn-outline-primary" href="{{ route('module_compliance_center.dashboard') }}"><i class="fa-solid fa-shield-halved"></i> Compliance</a>@endif
                    @if(Route::has('module_integration_validator.index'))<a class="btn btn-sm btn-outline-primary" href="{{ route('module_integration_validator.index') }}"><i class="fa-solid fa-check-double"></i> Validator</a>@endif
                </div>
            </section>
        </div>
    </div>
@endsection
