<style>
.ai-consensus-page{padding:1rem}
.ai-consensus-shell{display:grid;grid-template-columns:1fr;gap:1rem}
.ai-workspace{display:grid;grid-template-columns:260px minmax(0,1fr);gap:1rem;align-items:start}
.ai-content-panel{min-width:0}
.ai-content-card{border:1px solid rgba(255,255,255,.08);border-radius:5px;background:linear-gradient(180deg,rgba(37,47,59,.94) 0%,rgba(32,40,51,.96) 100%);box-shadow:0 8px 20px rgba(0,0,0,.14);padding:1rem}
.ai-content-card__head{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:.85rem}
.ai-content-card__title{margin:0;font-size:1rem;color:var(--text-primary,#f8fafc)}
.ai-content-card__subtitle{margin:.25rem 0 0;color:#94a3b8;font-size:.875rem}
.ai-side-nav{position:sticky;top:1rem;display:grid;gap:1rem;border:1px solid rgba(255,255,255,.08);border-radius:5px;background:linear-gradient(180deg,rgba(37,47,59,.96) 0%,rgba(27,35,45,.98) 100%);box-shadow:0 8px 20px rgba(0,0,0,.14);padding:1rem}
.ai-side-nav__header{display:flex;align-items:center;gap:.75rem;padding-bottom:.85rem;border-bottom:1px solid rgba(255,255,255,.08)}
.ai-side-nav__icon{width:38px;height:38px;display:inline-flex;align-items:center;justify-content:center;border-radius:5px;border:1px solid rgba(37,99,235,.22);background:rgba(37,99,235,.12);color:#93c5fd}
.ai-side-nav__header strong{display:block;color:var(--text-primary,#f8fafc);line-height:1.1}
.ai-side-nav__header span:not(.ai-side-nav__icon){display:block;color:#94a3b8;font-size:.8rem;margin-top:.2rem}
.ai-side-nav__links{display:grid;gap:.45rem}
.ai-side-nav__link,.ai-side-nav__provider{display:flex;align-items:center;gap:.65rem;width:100%;min-height:40px;padding:.65rem .75rem;border-radius:5px;border:1px solid transparent;background:transparent;color:#cbd5e1;text-decoration:none;font-weight:650;text-align:left;transition:all .18s ease}
.ai-side-nav__link:hover,.ai-side-nav__provider:hover{background:rgba(255,255,255,.05);color:var(--text-primary,#f8fafc);transform:translateY(-1px)}
.ai-side-nav__link.is-active{background:rgba(37,99,235,.15);border-color:rgba(37,99,235,.24);color:#bfdbfe}
.ai-side-nav__link.is-primary{background:rgba(34,197,94,.12);border-color:rgba(34,197,94,.2);color:#86efac}
.ai-side-nav__link i,.ai-side-nav__provider i{width:18px;text-align:center;color:inherit}
.ai-side-nav__section{display:grid;gap:.45rem;padding-top:.85rem;border-top:1px solid rgba(255,255,255,.08)}
.ai-side-nav__section-title{font-size:.72rem;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;font-weight:800}
.ai-side-nav__provider{font-size:.875rem;cursor:pointer}
.ai-side-nav__provider em{margin-left:auto;font-size:.72rem;font-style:normal;color:#94a3b8}

.aiConsensus-card,
.ai-card,
.ai-form-card,
.ai-toolbar,
.ai-page-header,
.ai-stat,
.ai-counter,
.ai-provider-box{
    border:1px solid rgba(255,255,255,.08);
    border-radius:5px;
    background:linear-gradient(180deg,rgba(37,47,59,.94) 0%,rgba(32,40,51,.96) 100%);
    box-shadow:0 8px 20px rgba(0,0,0,.14);
}
.ai-page-header,.ai-card,.ai-form-card,.ai-toolbar{padding:1rem}
.ai-page-header{padding:1rem 1.125rem}
.ai-breadcrumbs{display:flex;align-items:center;flex-wrap:wrap;gap:.45rem;margin-bottom:.85rem;font-size:.8rem}
.ai-breadcrumbs__link,.ai-breadcrumbs__current,.ai-breadcrumbs__sep{color:var(--text-muted,#94a3b8)}
.ai-breadcrumbs__link:hover{color:var(--text-primary,#f8fafc)}
.ai-page-header__main{display:flex;justify-content:space-between;gap:1rem;align-items:flex-start;flex-wrap:wrap}
.ai-page-header__identity{display:flex;align-items:flex-start;gap:.9rem;min-width:0}
.ai-page-header__icon{width:46px;height:46px;display:inline-flex;align-items:center;justify-content:center;border-radius:5px;border:1px solid rgba(37,99,235,.15);background:rgba(37,99,235,.08);color:#60a5fa;flex:0 0 auto}
.ai-page-header__title{margin:0;font-size:1.2rem;line-height:1.15;color:var(--text-primary,#f8fafc)}
.ai-page-header__subtitle{margin:.35rem 0 0 0;max-width:70ch;color:var(--text-muted,#94a3b8);line-height:1.55}

.lsg-page-actions{display:flex;flex-wrap:wrap;justify-content:flex-end;gap:.625rem}
.lsg-action-form{display:inline-flex;margin:0}
.lsg-action-btn{--lsg-bg:rgba(37,99,235,.14);--lsg-border:rgba(37,99,235,.22);--lsg-text:#bfdbfe;display:inline-flex;align-items:center;justify-content:center;gap:.6rem;min-height:38px;padding:.65rem .9rem;border-radius:5px;border:1px solid var(--lsg-border);background:var(--lsg-bg);color:var(--lsg-text);font-weight:600;text-decoration:none;transition:all .2s ease}
.lsg-action-btn:hover{transform:translateY(-1px);filter:brightness(1.03)}
.lsg-action-btn--compact{min-height:34px;padding:.55rem .7rem}
.lsg-action-btn--primary{--lsg-bg:rgba(37,99,235,.14);--lsg-border:rgba(37,99,235,.22);--lsg-text:#bfdbfe}
.lsg-action-btn--success{--lsg-bg:rgba(34,197,94,.12);--lsg-border:rgba(34,197,94,.18);--lsg-text:#86efac}
.lsg-action-btn--warning{--lsg-bg:rgba(245,158,11,.12);--lsg-border:rgba(245,158,11,.18);--lsg-text:#fcd34d}
.lsg-action-btn--danger{--lsg-bg:rgba(239,68,68,.12);--lsg-border:rgba(239,68,68,.2);--lsg-text:#fca5a5}

.ai-dashboard-grid{display:grid;grid-template-columns:1.2fr .8fr;gap:1rem;align-items:stretch}
.ai-toolbar-grid,.ai-toolbar{display:grid;gap:.75rem}
.ai-toolbar-search{display:grid;grid-template-columns:1fr auto;gap:.75rem;align-items:center}
.ai-toolbar-search__field{display:grid;grid-template-columns:auto 1fr;gap:.7rem;align-items:center;border-radius:5px;border:1px solid rgba(255,255,255,.08);padding:0 0 0 .8rem;background:rgba(255,255,255,.03)}
.ai-toolbar-search__field i{color:#94a3b8}
.ai-toolbar-search__field .ai-copy-input{border:0;background:transparent;padding-left:0;padding-right:0;color:var(--text-primary,#f8fafc)}

.ai-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.75rem}
.ai-stat{padding:.95rem;display:grid;gap:.3rem}
.ai-stat__label{font-size:.76rem;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8}
.ai-stat__value{font-size:1.35rem;line-height:1;color:var(--text-primary,#f8fafc)}

.ai-grid{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:1rem}
.ai-grid-1{grid-column:span 2}
.ai-table-wrap,.ai-consensus-page .table-responsive{overflow-x:auto}
.ai-table{width:100%;border-collapse:collapse;min-width:760px}
.ai-table th,.ai-table td,.ai-consensus-page table th,.ai-consensus-page table td{padding:.9rem .75rem;border-bottom:1px solid rgba(255,255,255,.08);vertical-align:middle}
.ai-table th,.ai-consensus-page table th{font-size:.75rem;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8}
.ai-table-title{display:grid;gap:.15rem}
.ai-table-title strong{color:var(--text-primary,#f8fafc)}
.ai-table-title span,.ai-table-url{color:#94a3b8}

.ai-badge{display:inline-flex;align-items:center;justify-content:center;padding:.25rem .55rem;border-radius:999px;font-size:.72rem;border:1px solid transparent}
.ai-badge--success{color:#86efac;background:rgba(34,197,94,.08);border-color:rgba(34,197,94,.18)}
.ai-badge--neutral{color:#cbd5e1;background:rgba(148,163,184,.12);border-color:rgba(148,163,184,.18)}

.ai-actions{display:flex;flex-wrap:wrap;gap:.5rem}
.ai-actions--center{justify-content:center}
.ai-copy-input,.ai-copy-field,.ai-textarea{border-radius:5px}
.ai-copy-input,.ai-copy-field,.ai-textarea{width:100%;border:1px solid rgba(255,255,255,.08);padding:.75rem .85rem;background:rgba(255,255,255,.03);color:var(--text-primary,#f8fafc)}
.ai-textarea,.ai-copy-field{min-height:140px;resize:vertical}
.ai-pre{white-space:pre-wrap;word-break:break-word;background:rgba(255,255,255,.03);border-radius:5px;padding:14px;border:1px solid rgba(255,255,255,.06);color:var(--text-primary,#f8fafc)}
.ai-muted{color:#94a3b8}
.ai-files-list{display:grid;gap:10px}
.ai-provider-box{padding:12px}
.ai-copy-block{display:grid;gap:10px}
.ai-copy-toolbar{display:flex;justify-content:flex-end;gap:10px}

.ai-show-title{margin:0;font-size:1.15rem;color:var(--text-primary,#f8fafc)}
.ai-show-subtitle{margin-top:.35rem;color:#94a3b8}
.ai-show-grid{margin-top:1rem}
.ai-alert{padding:.85rem 1rem;border-radius:5px;border:1px solid rgba(34,197,94,.18);color:#86efac;background:rgba(34,197,94,.08)}
.ai-empty-state{display:grid;gap:.3rem;justify-items:center;padding:1rem;text-align:center}
.ai-empty-state strong{color:var(--text-primary,#f8fafc)}
.ai-empty-state span{color:#94a3b8}

.ai-counters{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:.75rem;margin-bottom:1rem}
.ai-counter{padding:.95rem;display:grid;gap:.3rem}
.ai-counter-label{font-size:.76rem;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8}
.ai-counter-value{font-size:1.1rem;line-height:1.2;color:var(--text-primary,#f8fafc);font-weight:700;word-break:break-word}
.ai-counter--highlight .ai-counter-value{color:#fcd34d}

.ai-collapse-card{margin-bottom:1rem}
.ai-collapse-toggle{width:100%;display:flex;align-items:center;justify-content:space-between;gap:.75rem;padding:.95rem 1rem;border-radius:5px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.03);color:var(--text-primary,#f8fafc);cursor:pointer;text-align:left;transition:all .2s ease}
.ai-collapse-toggle:hover{transform:translateY(-1px);filter:brightness(1.03)}
.ai-collapse-title{font-size:.92rem;font-weight:700;color:inherit}
.ai-collapse-icon{display:inline-flex;align-items:center;justify-content:center;transition:transform .18s ease;color:#94a3b8}
.ai-collapse-toggle[aria-expanded="true"] .ai-collapse-icon{transform:rotate(180deg)}
.ai-collapse-body{padding-top:.75rem}
.ai-collapse-body[hidden]{display:none!important}

.ai-table-actions{display:flex;justify-content:flex-end;align-items:center;gap:.5rem;flex-wrap:wrap}
.ai-table-actions .lsg-action-form{display:inline-flex;margin:0}
.ai-table-actions .lsg-action-btn{min-width:40px;min-height:40px;padding:0 .8rem}
.ai-table-actions .lsg-action-btn--compact{min-width:34px;min-height:34px;padding:.55rem .7rem}
.ai-table-actions .lsg-action-btn__icon{display:inline-flex;align-items:center;justify-content:center}

.ai-secret-field{display:grid;grid-template-columns:minmax(0,1fr) auto auto;gap:.6rem;align-items:start}
.ai-secret-field--meta{margin-top:.45rem}
.ai-secret-field--textarea{align-items:start}
.ai-secret-field--textarea .lsg-action-btn{align-self:start}
.ai-secret-textarea{color:transparent;text-shadow:0 0 8px rgba(100,116,139,.85);caret-color:transparent;user-select:none}
.ai-secret-textarea--revealed{color:var(--text-primary,#f8fafc);text-shadow:none;caret-color:auto;user-select:text}
.ai-field-error{margin-top:6px;font-size:12px;color:#fca5a5;font-weight:500}
.ai-copy-input.is-invalid,.ai-copy-field.is-invalid,.ai-textarea.is-invalid{border-color:#ef4444!important;box-shadow:0 0 0 3px rgba(239,68,68,.12)}

body.theme-light .aiConsensus-card,
body.theme-light .ai-card,
body.theme-light .ai-form-card,
body.theme-light .ai-toolbar,
body.theme-light .ai-page-header,
body.theme-light .ai-content-card,
body.theme-light .ai-side-nav,
body.theme-light .ai-stat,
body.theme-light .ai-counter,
body.theme-light .ai-provider-box,
body[data-theme="light"] .aiConsensus-card,
body[data-theme="light"] .ai-card,
body[data-theme="light"] .ai-form-card,
body[data-theme="light"] .ai-toolbar,
body[data-theme="light"] .ai-page-header,
body[data-theme="light"] .ai-content-card,
body[data-theme="light"] .ai-side-nav,
body[data-theme="light"] .ai-stat,
body[data-theme="light"] .ai-counter,
body[data-theme="light"] .ai-provider-box{background:linear-gradient(180deg,rgba(255,255,255,.98) 0%,rgba(247,249,252,.98) 100%);border:1px solid rgba(21,32,51,.1);box-shadow:0 8px 20px rgba(15,23,42,.06)}
body.theme-light .ai-page-header__title,body.theme-light .ai-stat__value,body.theme-light .ai-table-title strong,body.theme-light .ai-copy-input,body.theme-light .ai-copy-field,body.theme-light .ai-textarea,body.theme-light .ai-pre,body.theme-light .ai-show-title,body.theme-light .ai-empty-state strong,body.theme-light .ai-counter-value,body.theme-light .ai-collapse-toggle,body.theme-light .ai-secret-textarea--revealed,
body.theme-light .ai-content-card__title,body.theme-light .ai-side-nav__header strong,
body[data-theme="light"] .ai-page-header__title,body[data-theme="light"] .ai-stat__value,body[data-theme="light"] .ai-table-title strong,body[data-theme="light"] .ai-copy-input,body[data-theme="light"] .ai-copy-field,body[data-theme="light"] .ai-textarea,body[data-theme="light"] .ai-pre,body[data-theme="light"] .ai-show-title,body[data-theme="light"] .ai-empty-state strong,body[data-theme="light"] .ai-counter-value,body[data-theme="light"] .ai-collapse-toggle,body[data-theme="light"] .ai-secret-textarea--revealed,
body[data-theme="light"] .ai-content-card__title,body[data-theme="light"] .ai-side-nav__header strong{color:#18212b}
body.theme-light .ai-breadcrumbs__link,body.theme-light .ai-breadcrumbs__current,body.theme-light .ai-breadcrumbs__sep,body.theme-light .ai-page-header__subtitle,body.theme-light .ai-stat__label,body.theme-light .ai-table th,body.theme-light .ai-consensus-page table th,body.theme-light .ai-table-title span,body.theme-light .ai-table-url,body.theme-light .ai-muted,body.theme-light .ai-show-subtitle,body.theme-light .ai-empty-state span,body.theme-light .ai-counter-label,body.theme-light .ai-collapse-icon,
body[data-theme="light"] .ai-breadcrumbs__link,body[data-theme="light"] .ai-breadcrumbs__current,body[data-theme="light"] .ai-breadcrumbs__sep,body[data-theme="light"] .ai-page-header__subtitle,body[data-theme="light"] .ai-stat__label,body[data-theme="light"] .ai-table th,body[data-theme="light"] .ai-consensus-page table th,body[data-theme="light"] .ai-table-title span,body[data-theme="light"] .ai-table-url,body[data-theme="light"] .ai-muted,body[data-theme="light"] .ai-show-subtitle,body[data-theme="light"] .ai-empty-state span,body[data-theme="light"] .ai-counter-label,body[data-theme="light"] .ai-collapse-icon{color:#64748b}
body.theme-light .ai-toolbar-search__field,body.theme-light .ai-copy-input,body.theme-light .ai-copy-field,body.theme-light .ai-textarea,body.theme-light .ai-collapse-toggle,body.theme-light .ai-pre,
body[data-theme="light"] .ai-toolbar-search__field,body[data-theme="light"] .ai-copy-input,body[data-theme="light"] .ai-copy-field,body[data-theme="light"] .ai-textarea,body[data-theme="light"] .ai-collapse-toggle,body[data-theme="light"] .ai-pre{background:rgba(255,255,255,.85);border-color:rgba(21,32,51,.1)}
body.theme-light .ai-side-nav__header,body.theme-light .ai-side-nav__section,body[data-theme="light"] .ai-side-nav__header,body[data-theme="light"] .ai-side-nav__section{border-color:rgba(21,32,51,.1)}
body.theme-light .ai-side-nav__link,body.theme-light .ai-side-nav__provider,body[data-theme="light"] .ai-side-nav__link,body[data-theme="light"] .ai-side-nav__provider{color:#475569}
body.theme-light .ai-side-nav__link:hover,body.theme-light .ai-side-nav__provider:hover,body[data-theme="light"] .ai-side-nav__link:hover,body[data-theme="light"] .ai-side-nav__provider:hover{background:rgba(15,23,42,.04);color:#18212b}
body.theme-light .ai-table th,body.theme-light .ai-table td,body.theme-light .ai-consensus-page table th,body.theme-light .ai-consensus-page table td,
body[data-theme="light"] .ai-table th,body[data-theme="light"] .ai-table td,body[data-theme="light"] .ai-consensus-page table th,body[data-theme="light"] .ai-consensus-page table td{border-bottom:1px solid rgba(226,232,240,.9)}
body.theme-light .lsg-action-btn--primary,body[data-theme="light"] .lsg-action-btn--primary{--lsg-bg:rgba(37,99,235,.08);--lsg-border:rgba(37,99,235,.16);--lsg-text:#2563eb}
body.theme-light .lsg-action-btn--success,body[data-theme="light"] .lsg-action-btn--success{--lsg-text:#15803d}
body.theme-light .lsg-action-btn--warning,body[data-theme="light"] .lsg-action-btn--warning{--lsg-text:#b45309}
body.theme-light .lsg-action-btn--danger,body[data-theme="light"] .lsg-action-btn--danger{--lsg-text:#b91c1c}

@media (max-width:991px){.ai-workspace,.ai-dashboard-grid,.ai-stats,.ai-grid,.ai-counters{grid-template-columns:1fr}.ai-side-nav{position:static}.ai-grid-1{grid-column:span 1}}
@media (max-width:768px){.ai-consensus-page{padding:.75rem}.ai-page-header,.ai-card,.ai-form-card,.ai-toolbar{padding:.9rem}.ai-page-header__main{flex-direction:column}.ai-page-header__identity{width:100%}.lsg-page-actions{width:100%;justify-content:flex-start}}
</style>
