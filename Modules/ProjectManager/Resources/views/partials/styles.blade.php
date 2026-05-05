<style>
/* ProjectManager v32 — LSG CSS refactor
   Rule: structural containers are transparent; only cards/panels receive a theme surface.
   The module inherits the BO light/dark palette and keeps the LSG 5px radius everywhere. */

.pm-wrap,
.pm-shell{
    --pm-radius:5px;
    --pm-accent:var(--lsg-accent, var(--bs-warning, #c9a646));
    --pm-accent-rgb:201,166,70;
    --pm-surface:var(--bs-card-bg, var(--card-bg, var(--lsg-card-bg, #ffffff)));
    --pm-surface-soft:var(--bs-tertiary-bg, var(--lsg-card-bg-soft, #f8f9fa));
    --pm-input:var(--bs-body-bg, var(--lsg-input-bg, #ffffff));
    --pm-text:var(--bs-body-color, var(--lsg-text, inherit));
    --pm-muted:var(--bs-secondary-color, var(--lsg-muted, #6b7280));
    --pm-border:var(--bs-border-color, var(--lsg-border, rgba(17,24,39,.14)));
    --pm-shadow:var(--lsg-card-shadow, 0 4px 14px rgba(15,23,42,.05));

    background:transparent!important;
    background-image:none!important;
    box-shadow:none!important;
    color:var(--pm-text)!important;
}

html[data-pm-theme="dark"] .pm-wrap,
html[data-pm-theme="dark"] .pm-shell,
body[data-pm-theme="dark"] .pm-wrap,
body[data-pm-theme="dark"] .pm-shell,
[data-bs-theme="dark"] .pm-wrap,
[data-bs-theme="dark"] .pm-shell,
[data-theme="dark"] .pm-wrap,
[data-theme="dark"] .pm-shell,
body.dark .pm-wrap,
body.dark .pm-shell,
body.dark-mode .pm-wrap,
body.dark-mode .pm-shell,
html.dark .pm-wrap,
html.dark .pm-shell{
    --pm-surface:var(--bs-card-bg, var(--card-bg, var(--lsg-card-bg, #1f2937)));
    --pm-surface-soft:var(--bs-tertiary-bg, var(--lsg-card-bg-soft, #243142));
    --pm-input:var(--bs-body-bg, var(--lsg-input-bg, #16202d));
    --pm-text:var(--bs-body-color, var(--lsg-text, #eef3f8));
    --pm-muted:var(--bs-secondary-color, var(--lsg-muted, #aeb9c5));
    --pm-border:var(--bs-border-color, var(--lsg-border, rgba(226,232,240,.18)));
    --pm-shadow:var(--lsg-card-shadow, 0 4px 14px rgba(0,0,0,.22));
}

.pm-wrap{padding:0!important;}
.pm-shell{display:flex;flex-direction:column;gap:12px;}

/* Panels / cards */
.pm-card,
.pm-project-sidebar,
.pm-tabs-wrap,
.pm-detail-nav,
.pm-detail-panel,
.pm-kanban-column,
.pm-eisenhower-cell,
.pm-accordion,
.pm-accordion summary,
.pm-accordion-body,
.pm-modal-panel,
.pm-modal-card,
.pm-wc-hero-main,
.pm-wc-side-card,
.pm-wc-project-hero,
.pm-wc-metric,
.pm-project-accordion,
.pm-project-accordion summary,
.pm-project-accordion-body,
.pm-project-filter,
.pm-open-project,
.pm-icon-action,
.pm-matrix-task,
.pm-kanban-card,
.pm-task-card,
.pm-record-row,
.pm-tree-row,
.pm-tile,
.pm-details,
.pm-roadmap-card,
.pm-roadmap-dot,
.pm-flow-node,
.pm-timeline-item>summary,
.pm-timeline-body,
.pm-milestone-chip,
.pm-milestone-progress,
.pm-guided-form,
.pm-guided-field,
.pm-upload-zone,
.pm-asset-preview,
.pm-project-logo-box,
.pm-project-state-card,
.pm-mini-stat,
.pm-detail-operation-path,
.pm-field-hidden-note{
    border:1px solid var(--pm-border)!important;
    border-radius:var(--pm-radius)!important;
    background:var(--pm-surface)!important;
    background-image:none!important;
    color:var(--pm-text)!important;
    box-shadow:var(--pm-shadow)!important;
    text-shadow:none!important;
    backdrop-filter:none!important;
}

.pm-card{padding:14px;}
.pm-card--compact{padding:10px;}
.pm-card-title{font-weight:800;font-size:.96rem;display:flex;align-items:center;gap:7px;margin-bottom:4px;color:var(--pm-text)!important;}
.pm-card-title i{color:var(--pm-accent)!important;}
.pm-card-subtitle,.pm-muted,.pm-small{color:var(--pm-muted)!important;}
.pm-small{font-size:.8rem;}

/* Layout */
.pm-grid{display:grid;gap:12px;}
.pm-grid-2{grid-template-columns:repeat(2,minmax(0,1fr));}
.pm-grid-3{grid-template-columns:repeat(3,minmax(0,1fr));}
.pm-two-col{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:12px;align-items:start;}
.pm-two-col--wide-right{grid-template-columns:minmax(0,1fr) 420px;}
.pm-entry-layout{display:grid;grid-template-columns:320px minmax(0,1fr);gap:16px;align-items:start;}
.pm-entry-top{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:14px;}
.pm-charts-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px;}
.pm-detail-layout{display:grid;grid-template-columns:320px minmax(0,1fr);gap:12px;align-items:start;}
.pm-project-sidebar,.pm-detail-nav{position:sticky;top:12px;max-height:calc(100vh - 140px);overflow:auto;}
.pm-section-bar{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:12px;padding-bottom:10px;border-bottom:1px solid var(--pm-border)!important;}

/* Buttons / form controls */
.pm-btn,
.pm-tab,
.pm-pill,
.pm-task-state-select,
.pm-form-field input,
.pm-form-field select,
.pm-form-field textarea,
.pm-form .form-control,
.pm-form .form-select,
.pm-guided-field input,
.pm-guided-field select,
.pm-guided-field textarea,
.pm-project-state-form select,
.pm-upload-form-grid input,
.pm-upload-form-grid select,
.pm-upload-form-grid textarea,
.pm-matrix-inputs input,
.pm-field-chip,
.pm-save-indicator,
.pm-matrix-task-status{
    border:1px solid var(--pm-border)!important;
    border-radius:var(--pm-radius)!important;
    background:var(--pm-input)!important;
    background-image:none!important;
    color:var(--pm-text)!important;
    box-shadow:none!important;
}
.pm-btn{display:inline-flex;align-items:center;gap:7px;padding:7px 10px;text-decoration:none;font-size:.82rem;font-weight:800;line-height:1.1;cursor:pointer;}
.pm-btn:hover,.pm-tab:hover,.pm-open-project:hover,.pm-icon-action:hover,.pm-project-filter:hover{
    background:rgba(var(--pm-accent-rgb),.12)!important;
    border-color:var(--pm-accent)!important;
    color:var(--pm-text)!important;
    transform:none!important;
    text-decoration:none!important;
}
.pm-btn--compact{padding:6px 8px;font-size:.76rem;}
.pm-btn--primary,.pm-btn--success,.pm-btn--warning,.pm-btn--danger,.pm-btn--ghost{background:var(--pm-input)!important;color:var(--pm-text)!important;border-color:var(--pm-border)!important;}
.pm-actions{display:flex;gap:7px;flex-wrap:wrap;align-items:center;}
.pm-actions--right{justify-content:flex-end;}
.pm-form label,.pm-form-label,.pm-form-field label,.pm-guided-field label{font-size:.76rem;font-weight:800;color:var(--pm-text)!important;margin-bottom:4px;}
.pm-form-field textarea,.pm-guided-field textarea{resize:vertical;min-height:90px;}

/* Tabs */
.pm-tabs-wrap{padding:6px;overflow:hidden;}
.pm-tabs{display:flex;gap:5px;overflow-x:auto;scrollbar-width:thin;}
.pm-tab{display:inline-flex;align-items:center;gap:7px;padding:8px 10px;white-space:nowrap;font-size:.84rem;font-weight:800;}
.pm-tab.is-active,
.pm-detail-nav-item.is-active,
.pm-project-filter.is-active,
.pm-flow-node.is-current,
.pm-roadmap-node.is-current .pm-roadmap-dot,
.pm-roadmap-node.is-current .pm-roadmap-card,
.pm-timeline-item.is-current>summary,
.pm-milestone-chip:hover{
    background:rgba(var(--pm-accent-rgb),.16)!important;
    border-color:var(--pm-accent)!important;
    color:var(--pm-text)!important;
    box-shadow:none!important;
}

/* Tables / lists */
.pm-table{width:100%;border-collapse:separate;border-spacing:0;}
.pm-table th{font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;color:var(--pm-muted)!important;background:var(--pm-surface-soft)!important;border-bottom:1px solid var(--pm-border)!important;padding:9px;}
.pm-table td{border-bottom:1px solid var(--pm-border)!important;padding:9px;vertical-align:middle;color:var(--pm-text)!important;}
.pm-empty{border-style:dashed!important;padding:16px;text-align:center;color:var(--pm-muted)!important;background:var(--pm-surface-soft)!important;}
.pm-tree-list{display:flex;flex-direction:column;gap:6px;}
.pm-tree-row,.pm-task-card,.pm-matrix-task,.pm-kanban-card,.pm-record-row{padding:10px;display:flex;justify-content:space-between;gap:10px;align-items:flex-start;}
.pm-tree-main{display:flex;align-items:flex-start;gap:8px;min-width:0;}
.pm-tree-meta,.pm-task-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap;justify-content:flex-end;}
.pm-task-card--blocked{border-color:rgba(220,38,38,.35)!important;}

/* Pills / status */
.pm-pill{display:inline-flex;align-items:center;gap:5px;padding:4px 8px;font-size:.75rem;font-weight:800;white-space:nowrap;}
.pm-pill--gold{border-color:rgba(var(--pm-accent-rgb),.35)!important;background:rgba(var(--pm-accent-rgb),.12)!important;}
.pm-pill--success,.pm-pill--ok{border-color:rgba(22,163,74,.35)!important;background:rgba(22,163,74,.12)!important;}
.pm-pill--danger{border-color:rgba(220,38,38,.35)!important;background:rgba(220,38,38,.12)!important;}
.pm-status-dot{width:9px;height:9px;border-radius:var(--pm-radius)!important;background:#9ca3af;flex:0 0 auto;}
.pm-status-in-progress,.pm-status-dot--execution{background:#22c55e!important;}
.pm-status-ready{background:#2563eb!important;}
.pm-status-blocked{background:#dc2626!important;}
.pm-status-done,.pm-status-completed,.pm-status-dot--done{background:#64748b!important;}
.pm-status-dot--hold{background:#f59e0b!important;}
.pm-status-dot--pending{background:#94a3b8!important;}

/* WebCatalogue-like hero without hardcoded theme surfaces */
.pm-wc-hero{display:grid;grid-template-columns:minmax(0,1.3fr) minmax(280px,.7fr);gap:16px;align-items:stretch;margin-bottom:16px;}
.pm-wc-hero-main{position:relative;overflow:hidden;padding:22px;min-height:160px;}
.pm-wc-hero-main:after{display:none!important;}
.pm-wc-kicker{font-size:11px;text-transform:uppercase;letter-spacing:.13em;font-weight:900;color:var(--pm-accent)!important;margin-bottom:8px;}
.pm-wc-title,.pm-wc-project-title{font-weight:900;letter-spacing:-.04em;line-height:1.05;margin:0 0 8px;color:var(--pm-text)!important;}
.pm-wc-title{font-size:26px;}
.pm-wc-project-title{font-size:24px;}
.pm-wc-lead{font-size:13px;color:var(--pm-muted)!important;max-width:720px;margin:0;}
.pm-wc-hero-actions,.pm-wc-project-actions{display:flex;gap:8px;flex-wrap:wrap;}
.pm-wc-side-card{padding:18px;}
.pm-wc-metric-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
.pm-wc-metric{padding:12px;}
.pm-wc-metric span,.pm-mini-stat span{display:block;font-size:10px;text-transform:uppercase;letter-spacing:.08em;font-weight:900;color:var(--pm-muted)!important;}
.pm-wc-metric strong,.pm-mini-stat strong{font-size:22px;letter-spacing:-.05em;color:var(--pm-text)!important;}
.pm-wc-project-hero{display:grid;grid-template-columns:auto minmax(0,1fr) auto;gap:16px;align-items:center;margin-bottom:16px;padding:18px;}
.pm-wc-project-logo,.pm-project-logo,.pm-project-logo-fallback{object-fit:contain;display:inline-flex;align-items:center;justify-content:center;font-weight:900;color:var(--pm-text)!important;background:var(--pm-input)!important;border-color:var(--pm-border)!important;}
.pm-wc-project-logo{width:72px;height:72px;padding:8px;font-size:24px;}
.pm-project-logo,.pm-project-logo-fallback{width:34px!important;height:34px!important;flex:0 0 34px!important;font-size:12px;}

/* Dashboard project selector */
.pm-project-group{margin-bottom:14px;}
.pm-project-group-title{display:flex;align-items:center;justify-content:space-between;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:var(--pm-muted)!important;margin:0 0 8px;}
.pm-project-accordion{overflow:hidden;margin-top:10px;}
.pm-project-accordion summary{list-style:none;cursor:pointer;display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 11px;font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:.06em;}
.pm-project-accordion summary::-webkit-details-marker{display:none;}
.pm-project-accordion-body{padding:9px;border-top:1px solid var(--pm-border)!important;}
.pm-project-list{display:flex;flex-direction:column;gap:7px;}
.pm-project-row{display:grid;grid-template-columns:minmax(0,1fr) 34px;gap:6px;align-items:stretch;}
.pm-project-filter{width:100%;padding:9px 10px;text-align:left;display:flex;align-items:center;justify-content:space-between;gap:8px;transition:.15s ease;}
.pm-project-filter-main{display:flex;align-items:center;gap:9px;min-width:0;}
.pm-project-name-wrap{min-width:0;}
.pm-project-name-wrap strong,.pm-project-name-wrap small{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block;}
.pm-icon-action,.pm-open-project{width:34px;height:34px;display:inline-flex;align-items:center;justify-content:center;text-decoration:none;flex:0 0 34px;}
.pm-selected-bar{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px;}
.pm-hidden-by-filter{display:none!important;}

/* Detail page */
.pm-detail-nav{padding:6px;display:flex;flex-direction:column;gap:4px;}
.pm-detail-nav-item{width:100%;border:1px solid transparent;background:transparent;border-radius:var(--pm-radius)!important;padding:8px;display:grid;grid-template-columns:28px minmax(0,1fr) auto;gap:7px;align-items:center;text-align:left;color:var(--pm-text)!important;cursor:pointer;}
.pm-detail-nav-icon{width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;background:var(--pm-input)!important;border:1px solid var(--pm-border)!important;border-radius:var(--pm-radius)!important;}
.pm-detail-nav-text{min-width:0;display:flex;flex-direction:column;line-height:1.15;}
.pm-project-identity-strip{display:grid;grid-template-columns:110px minmax(0,1fr) 300px;gap:14px;align-items:stretch;margin-bottom:14px;}
.pm-project-logo-box{display:flex;align-items:center;justify-content:center;min-height:104px;overflow:hidden;}
.pm-project-logo-box img{max-width:100%;max-height:96px;object-fit:contain;padding:10px;}
.pm-project-state-form{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:8px;align-items:end;}
.pm-upload-form-grid,.pm-guided-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;}
.pm-upload-form-grid .full,.pm-guided-field.full{grid-column:1 / -1;}
.pm-asset-preview-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px;margin-top:12px;}
.pm-asset-preview img{width:100%;height:74px;object-fit:contain;background:var(--pm-surface-soft)!important;border-radius:var(--pm-radius)!important;margin-bottom:6px;}
.pm-guided-form{padding:14px;}
.pm-guided-form-intro{display:flex;align-items:flex-start;gap:10px;background:rgba(var(--pm-accent-rgb),.08)!important;border:1px solid rgba(var(--pm-accent-rgb),.18)!important;border-radius:var(--pm-radius)!important;padding:10px 12px;margin-bottom:14px;color:var(--pm-muted)!important;font-size:12px;}
.pm-guided-field{padding:10px;}
.pm-field-hidden-note{border-style:dashed!important;padding:10px;font-size:12px;color:var(--pm-muted)!important;}

/* Roadmap / productivity */
.pm-kanban{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;align-items:start;}
.pm-eisenhower{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;}
.pm-eisenhower-cell{min-height:180px;padding:12px;}
.pm-eisenhower-title{display:flex;align-items:center;gap:8px;font-size:13px;text-transform:uppercase;letter-spacing:.05em;color:var(--pm-text)!important;}
.pm-roadmap-line{display:flex;gap:8px;align-items:stretch;overflow-x:auto;padding:6px 2px 12px;}
.pm-roadmap-node{position:relative;display:flex;align-items:center;gap:8px;min-width:210px;}
.pm-roadmap-node:not(:last-child)::after{content:'';position:absolute;right:-8px;top:21px;width:8px;height:2px;background:var(--pm-border);}
.pm-roadmap-dot{width:42px;height:42px;display:flex;align-items:center;justify-content:center;flex:0 0 auto;}
.pm-roadmap-card{padding:9px;min-width:145px;display:flex;flex-direction:column;gap:3px;}
.pm-roadmap-card span{font-size:.72rem;color:var(--pm-muted)!important;font-weight:800;}
.pm-roadmap-node.is-done .pm-roadmap-dot{border-color:rgba(22,163,74,.35)!important;background:rgba(22,163,74,.12)!important;}
.pm-progress-track,.pm-progress,.pm-gantt-track{border-radius:var(--pm-radius)!important;background:var(--pm-surface-soft)!important;overflow:hidden;border:1px solid var(--pm-border)!important;}
.pm-progress-track{height:9px;}
.pm-progress,.pm-gantt-track{height:22px;}
.pm-progress-track span,.pm-progress span,.pm-gantt-track span{display:block;height:100%;border-radius:var(--pm-radius)!important;background:var(--pm-accent)!important;}
.pm-gantt--wide .pm-gantt-label{width:260px;}
.pm-gantt-task-meta{font-size:11px;color:var(--pm-muted)!important;margin-top:2px;}
.pm-progress-item{margin-bottom:12px;}
.pm-progress-head{display:flex;justify-content:space-between;gap:10px;font-size:12px;margin-bottom:5px;color:var(--pm-muted)!important;}

/* Modal */
.pm-modal-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.55)!important;z-index:1050;display:none;align-items:center;justify-content:center;padding:18px;}
.pm-modal-backdrop.is-open,.pm-modal-backdrop.is-visible{display:flex;}
.pm-modal-panel,.pm-modal-card{width:min(760px,100%);max-height:calc(100vh - 36px);overflow:auto;}
.pm-modal-head{display:flex;justify-content:space-between;gap:12px;align-items:flex-start;padding:16px 18px;border-bottom:1px solid var(--pm-border)!important;}
.pm-modal-body{padding:18px;}
.pm-modal-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
.pm-form-field--full{grid-column:1 / -1;}
.pm-modal-foot{display:flex;justify-content:flex-end;gap:8px;padding:14px 18px;border-top:1px solid var(--pm-border)!important;}

.pm-alert{border-radius:var(--pm-radius)!important;border:1px solid rgba(22,163,74,.25)!important;background:rgba(22,163,74,.12)!important;color:var(--pm-text)!important;padding:10px 12px;font-weight:700;}

@media(max-width:1200px){.pm-tile-grid{grid-template-columns:repeat(2,minmax(0,1fr));}.pm-grid-3,.pm-kanban{grid-template-columns:1fr;}.pm-two-col,.pm-two-col--wide-right{grid-template-columns:1fr;}}
@media(max-width:1100px){.pm-entry-layout,.pm-detail-layout,.pm-wc-hero{grid-template-columns:1fr}.pm-project-sidebar,.pm-detail-nav{position:relative;top:auto;max-height:none}.pm-entry-top,.pm-charts-row{grid-template-columns:1fr 1fr}.pm-project-identity-strip{grid-template-columns:90px 1fr}.pm-project-state-card{grid-column:1 / -1}.pm-wc-project-hero{grid-template-columns:auto 1fr}.pm-wc-project-actions{grid-column:1 / -1;justify-content:flex-start}}
@media(max-width:760px){.pm-modal-grid,.pm-upload-form-grid,.pm-guided-grid{grid-template-columns:1fr}.pm-section-bar{align-items:flex-start;flex-direction:column}.pm-actions--right{justify-content:flex-start}.pm-eisenhower{grid-template-columns:1fr}}
@media(max-width:680px){.pm-grid-2,.pm-tile-grid,.pm-entry-top,.pm-charts-row,.pm-wc-metric-grid{grid-template-columns:1fr}.pm-project-identity-strip,.pm-project-state-form,.pm-wc-project-hero{grid-template-columns:1fr}.pm-wc-project-logo{width:64px;height:64px}.pm-wc-title,.pm-wc-project-title{font-size:22px}}


/* v33 productivity theme completion
   The global/project productivity screens use additional structural classes.
   These must inherit the same LSG tokens as the dashboard and never force their own light/dark palette. */
.pm-page-intro,
.pm-milestone-strip,
.pm-matrix-list,
.pm-kanban-actions,
.pm-task-badges,
.pm-matrix-task-actions,
.pm-detail-actions-footer{
    background:transparent!important;
    background-image:none!important;
    color:var(--pm-text)!important;
    box-shadow:none!important;
    text-shadow:none!important;
}

.pm-page-intro{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:12px;
    margin-bottom:12px;
}

.pm-page-kicker{
    font-size:11px;
    text-transform:uppercase;
    letter-spacing:.12em;
    font-weight:900;
    color:var(--pm-accent)!important;
}

.pm-page-lead,
.pm-kanban-card-meta,
.pm-matrix-task-meta{
    color:var(--pm-muted)!important;
}

.pm-context-pill,
.pm-kanban-head,
.pm-task-badges span,
.pm-matrix-task-status,
.pm-progress-mini{
    border:1px solid var(--pm-border)!important;
    border-radius:var(--pm-radius)!important;
    background:var(--pm-input)!important;
    background-image:none!important;
    color:var(--pm-text)!important;
    box-shadow:none!important;
}

.pm-context-pill{
    display:inline-flex;
    align-items:center;
    gap:7px;
    padding:7px 10px;
    font-size:.78rem;
    font-weight:800;
    white-space:nowrap;
}

.pm-kanban-head{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:8px;
    padding:9px 10px;
    margin-bottom:10px;
    font-size:.82rem;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.04em;
}

.pm-kanban-head i,
.pm-eisenhower-title i,
.pm-page-intro i,
.pm-context-pill i{
    color:var(--pm-accent)!important;
}

.pm-kanban-card-title,
.pm-matrix-task strong{
    color:var(--pm-text)!important;
    font-weight:850;
}

.pm-kanban-card-meta,
.pm-matrix-task-meta{
    font-size:.76rem;
    line-height:1.35;
}

.pm-task-badges{
    display:flex;
    gap:5px;
    flex-wrap:wrap;
    margin:7px 0;
}

.pm-task-badges span{
    display:inline-flex;
    align-items:center;
    gap:5px;
    padding:3px 7px;
    font-size:.72rem;
    font-weight:800;
}

.pm-matrix-list{
    display:flex;
    flex-direction:column;
    gap:7px;
    min-height:72px;
}

.pm-empty--small{
    padding:10px!important;
    font-size:.78rem!important;
}

.pm-progress-mini{
    height:7px;
    overflow:hidden;
    margin-top:7px;
}

.pm-progress-mini span{
    display:block;
    height:100%;
    border-radius:var(--pm-radius)!important;
    background:var(--pm-accent)!important;
}

.pm-kanban-card--blocked{
    border-color:rgba(220,38,38,.45)!important;
}

.pm-task-state-select,
.pm-matrix-task-status,
.pm-save-indicator{
    min-height:28px;
}

.pm-save-indicator{
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:5px 8px;
    font-size:.72rem;
    font-weight:850;
}

.pm-section-bar,
.pm-card,
.pm-kanban-column,
.pm-eisenhower-cell,
.pm-matrix-task,
.pm-kanban-card{
    color:var(--pm-text)!important;
}

.pm-section-bar *:not(.pm-btn):not(.pm-task-state-select),
.pm-card *:not(.pm-btn):not(.pm-task-state-select):not(input):not(select):not(textarea),
.pm-kanban-column *:not(.pm-btn):not(.pm-task-state-select):not(input):not(select):not(textarea),
.pm-eisenhower-cell *:not(.pm-btn):not(.pm-task-state-select):not(input):not(select):not(textarea){
    text-shadow:none!important;
}

html[data-pm-theme="dark"] .pm-page-intro,
html[data-pm-theme="dark"] .pm-card,
html[data-pm-theme="dark"] .pm-kanban-column,
html[data-pm-theme="dark"] .pm-eisenhower-cell,
html[data-pm-theme="dark"] .pm-matrix-task,
html[data-pm-theme="dark"] .pm-kanban-card,
body[data-pm-theme="dark"] .pm-page-intro,
body[data-pm-theme="dark"] .pm-card,
body[data-pm-theme="dark"] .pm-kanban-column,
body[data-pm-theme="dark"] .pm-eisenhower-cell,
body[data-pm-theme="dark"] .pm-matrix-task,
body[data-pm-theme="dark"] .pm-kanban-card{
    color:var(--pm-text)!important;
}

</style>

<script>
(function(){
    function parseRgb(value){
        if(!value || value === 'transparent') return null;
        var m = value.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/i);
        if(!m) return null;
        return [parseInt(m[1],10), parseInt(m[2],10), parseInt(m[3],10)];
    }
    function luminance(rgb){
        return (0.2126 * rgb[0] + 0.7152 * rgb[1] + 0.0722 * rgb[2]);
    }
    function detectPmTheme(){
        var root = document.documentElement;
        var body = document.body;
        if(!body) return;
        var forcedDark = root.matches('[data-bs-theme="dark"],[data-theme="dark"],.dark,.dark-mode') || body.matches('[data-bs-theme="dark"],[data-theme="dark"],.dark,.dark-mode');
        var forcedLight = root.matches('[data-bs-theme="light"],[data-theme="light"],.light') || body.matches('[data-bs-theme="light"],[data-theme="light"],.light');
        var probe = document.querySelector('#yieldContent') || document.querySelector('main') || body;
        var rgb = parseRgb(window.getComputedStyle(probe).backgroundColor) || parseRgb(window.getComputedStyle(body).backgroundColor);
        var isDark = forcedDark || (!forcedLight && rgb && luminance(rgb) < 128);
        root.setAttribute('data-pm-theme', isDark ? 'dark' : 'light');
        body.setAttribute('data-pm-theme', isDark ? 'dark' : 'light');
    }
    if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', detectPmTheme); else detectPmTheme();
    document.addEventListener('click', function(e){
        if(e.target && (e.target.closest('[data-theme-toggle]') || e.target.closest('.theme-toggle'))) {
            setTimeout(detectPmTheme, 120);
            setTimeout(detectPmTheme, 450);
        }
    });
})();
</script>
