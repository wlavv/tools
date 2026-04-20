<style>
.password-manager-page{padding:1rem}
.password-manager-shell{display:grid;grid-template-columns:1fr;gap:1rem}

.passwordManager-card,
.password-manager-card,
.password-manager-form-card,
.password-manager-toolbar,
.pm-page-header,
.password-manager-stat,
.password-manager-meta{
    border:1px solid rgba(255,255,255,.08);
    border-radius:5px;
    background:linear-gradient(180deg,rgba(37,47,59,.94) 0%,rgba(32,40,51,.96) 100%);
    box-shadow:0 8px 20px rgba(0,0,0,.14);
}

.pm-page-header,.password-manager-card,.password-manager-form-card,.password-manager-toolbar{padding:1rem}
.pm-page-header{padding:1rem 1.125rem}
.pm-breadcrumbs{display:flex;align-items:center;flex-wrap:wrap;gap:.45rem;margin-bottom:.85rem;font-size:.8rem}
.pm-breadcrumbs__link,.pm-breadcrumbs__current,.pm-breadcrumbs__sep{color:var(--text-muted,#94a3b8)}
.pm-breadcrumbs__link:hover{color:var(--text-primary,#f8fafc)}
.pm-page-header__main{display:flex;justify-content:space-between;gap:1rem;align-items:flex-start;flex-wrap:wrap}
.pm-page-header__identity{display:flex;align-items:flex-start;gap:.9rem;min-width:0}
.pm-page-header__icon{width:46px;height:46px;display:inline-flex;align-items:center;justify-content:center;border-radius:5px;border:1px solid rgba(37,99,235,.15);background:rgba(37,99,235,.08);color:#60a5fa;flex:0 0 auto}
.pm-page-header__title{margin:0;font-size:1.2rem;line-height:1.15;color:var(--text-primary,#f8fafc)}
.pm-page-header__subtitle{margin:.35rem 0 0 0;max-width:70ch;color:var(--text-muted,#94a3b8);line-height:1.55}

.lsg-page-actions{display:flex;flex-wrap:wrap;justify-content:flex-end;gap:.625rem}
.lsg-action-form{display:inline-flex;margin:0}
.lsg-action-btn{--lsg-bg:rgba(37,99,235,.14);--lsg-border:rgba(37,99,235,.22);--lsg-text:#bfdbfe;display:inline-flex;align-items:center;justify-content:center;gap:.6rem;min-height:38px;padding:.65rem .9rem;border-radius:5px;border:1px solid var(--lsg-border);background:var(--lsg-bg);color:var(--lsg-text);font-weight:600;text-decoration:none;transition:all .2s ease}
.lsg-action-btn:hover{transform:translateY(-1px);filter:brightness(1.03)}
.lsg-action-btn--compact{min-height:34px;padding:.55rem .7rem}
.lsg-action-btn--primary{--lsg-bg:rgba(37,99,235,.14);--lsg-border:rgba(37,99,235,.22);--lsg-text:#bfdbfe}
.lsg-action-btn--success{--lsg-bg:rgba(34,197,94,.12);--lsg-border:rgba(34,197,94,.18);--lsg-text:#86efac}
.lsg-action-btn--warning{--lsg-bg:rgba(245,158,11,.12);--lsg-border:rgba(245,158,11,.18);--lsg-text:#fcd34d}
.lsg-action-btn--danger{--lsg-bg:rgba(239,68,68,.12);--lsg-border:rgba(239,68,68,.2);--lsg-text:#fca5a5}

.pm-dashboard-grid{display:grid;grid-template-columns:1.2fr .8fr;gap:1rem;align-items:stretch}
.pm-toolbar-grid,.password-manager-toolbar{display:grid;gap:.75rem}
.pm-toolbar-search{display:grid;grid-template-columns:1fr auto;gap:.75rem;align-items:center}
.pm-toolbar-search__field{display:grid;grid-template-columns:auto 1fr;gap:.7rem;align-items:center;border-radius:5px;border:1px solid rgba(255,255,255,.08);padding:0 0 0 .8rem;background:rgba(255,255,255,.03)}
.pm-toolbar-search__field i{color:#94a3b8}
.pm-toolbar-search__field .password-manager-input{border:0;background:transparent;padding-left:0;padding-right:0}

.password-manager-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.75rem}
.password-manager-stat{padding:.95rem;display:grid;gap:.3rem}
.password-manager-stat__label{font-size:.76rem;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8}
.password-manager-stat__value{font-size:1.35rem;line-height:1;color:var(--text-primary,#f8fafc)}

.password-manager-table-wrap{overflow-x:auto}
.password-manager-table{width:100%;border-collapse:collapse;min-width:760px}
.password-manager-table th,.password-manager-table td{padding:.9rem .75rem;border-bottom:1px solid rgba(255,255,255,.08);text-align:left;vertical-align:middle}
.password-manager-table th{font-size:.75rem;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8}
.pm-table-title{display:grid;gap:.15rem}
.pm-table-title strong{color:var(--text-primary,#f8fafc)}
.pm-table-title span,.pm-table-url{color:#94a3b8}

.password-manager-badge{display:inline-flex;align-items:center;justify-content:center;padding:.25rem .55rem;border-radius:999px;font-size:.72rem;border:1px solid transparent}
.password-manager-badge--favorite{color:#86efac;background:rgba(34,197,94,.08);border-color:rgba(34,197,94,.18)}
.password-manager-badge--neutral{color:#cbd5e1;background:rgba(148,163,184,.12);border-color:rgba(148,163,184,.18)}

.password-manager-actions{display:flex;flex-wrap:wrap;gap:.5rem}
.password-manager-actions--center{justify-content:center}
.password-manager-btn,.password-manager-input,.password-manager-textarea{border-radius:5px}
.password-manager-btn{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:.65rem .95rem;text-decoration:none;cursor:pointer;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.03);color:var(--text-primary,#f8fafc)}
.password-manager-btn-primary{background:#2563eb;color:#fff;border-color:#2563eb}
.password-manager-input,.password-manager-textarea{width:100%;border:1px solid rgba(255,255,255,.08);padding:.75rem .85rem;background:rgba(255,255,255,.03);color:var(--text-primary,#f8fafc)}
.password-manager-textarea{min-height:140px;resize:vertical}

.password-manager-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}
.password-manager-grid-1{grid-column:span 2}
.pm-password-field{display:grid;grid-template-columns:minmax(0,1fr) auto auto;gap:.6rem;align-items:start}
.pm-password-field--meta{margin-top:.45rem}
.pm-password-field--textarea{align-items:start}
.pm-password-field--textarea .lsg-action-btn{align-self:start}

.password-manager-label{display:block;margin-bottom:.45rem;font-size:.78rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#94a3b8}
.password-manager-meta{display:grid;gap:.35rem;padding:.9rem}
.password-manager-meta__label{font-size:.76rem;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;font-weight:700}
.pm-show-title{margin:0;font-size:1.15rem;color:var(--text-primary,#f8fafc)}
.pm-show-category{margin-top:.35rem;color:#94a3b8}
.pm-show-grid{margin-top:1rem}

.password-manager-alert{padding:.85rem 1rem;border-radius:5px;border:1px solid rgba(34,197,94,.18);color:#86efac;background:rgba(34,197,94,.08)}
.password-manager-alert--warning{border-color:rgba(245,158,11,.22);color:#fcd34d;background:rgba(245,158,11,.08)}

.pm-empty-state{display:grid;gap:.3rem;justify-items:center;padding:1rem;text-align:center}
.pm-empty-state strong{color:var(--text-primary,#f8fafc)}
.pm-empty-state span,.pm-mobile-item__sub,.pm-mobile-item__category{color:#94a3b8}
.password-manager-mobile-list{display:none}
.password-manager-mobile-item{padding:.95rem}
.pm-mobile-item__header{display:flex;justify-content:space-between;gap:.75rem;align-items:flex-start}
.pm-pagination-wrap{margin-top:1rem}

.pm-secret-textarea{color:transparent;text-shadow:0 0 8px rgba(100,116,139,.85);caret-color:transparent;user-select:none}
.pm-secret-textarea--revealed{color:var(--text-primary,#f8fafc);text-shadow:none;caret-color:auto;user-select:text}

body.theme-light .passwordManager-card,
body.theme-light .password-manager-card,
body.theme-light .password-manager-form-card,
body.theme-light .password-manager-toolbar,
body.theme-light .pm-page-header,
body.theme-light .password-manager-stat,
body.theme-light .password-manager-meta,
body[data-theme="light"] .passwordManager-card,
body[data-theme="light"] .password-manager-card,
body[data-theme="light"] .password-manager-form-card,
body[data-theme="light"] .password-manager-toolbar,
body[data-theme="light"] .pm-page-header,
body[data-theme="light"] .password-manager-stat,
body[data-theme="light"] .password-manager-meta{background:linear-gradient(180deg,rgba(255,255,255,.98) 0%,rgba(247,249,252,.98) 100%);border:1px solid rgba(21,32,51,.1);box-shadow:0 8px 20px rgba(15,23,42,.06)}
body.theme-light .pm-page-header__title,body.theme-light .password-manager-stat__value,body.theme-light .pm-table-title strong,body.theme-light .password-manager-btn,body.theme-light .password-manager-input,body.theme-light .password-manager-textarea,body.theme-light .pm-show-title,body.theme-light .pm-empty-state strong,body.theme-light .pm-secret-textarea--revealed,
body[data-theme="light"] .pm-page-header__title,body[data-theme="light"] .password-manager-stat__value,body[data-theme="light"] .pm-table-title strong,body[data-theme="light"] .password-manager-btn,body[data-theme="light"] .password-manager-input,body[data-theme="light"] .password-manager-textarea,body[data-theme="light"] .pm-show-title,body[data-theme="light"] .pm-empty-state strong,body[data-theme="light"] .pm-secret-textarea--revealed{color:#18212b}
body.theme-light .pm-breadcrumbs__link,body.theme-light .pm-breadcrumbs__current,body.theme-light .pm-breadcrumbs__sep,body.theme-light .pm-page-header__subtitle,body.theme-light .password-manager-stat__label,body.theme-light .password-manager-table th,body.theme-light .pm-table-title span,body.theme-light .pm-table-url,body.theme-light .password-manager-label,body.theme-light .password-manager-meta__label,body.theme-light .pm-show-category,body.theme-light .pm-empty-state span,body.theme-light .pm-mobile-item__sub,body.theme-light .pm-mobile-item__category,
body[data-theme="light"] .pm-breadcrumbs__link,body[data-theme="light"] .pm-breadcrumbs__current,body[data-theme="light"] .pm-breadcrumbs__sep,body[data-theme="light"] .pm-page-header__subtitle,body[data-theme="light"] .password-manager-stat__label,body[data-theme="light"] .password-manager-table th,body[data-theme="light"] .pm-table-title span,body[data-theme="light"] .pm-table-url,body[data-theme="light"] .password-manager-label,body[data-theme="light"] .password-manager-meta__label,body[data-theme="light"] .pm-show-category,body[data-theme="light"] .pm-empty-state span,body[data-theme="light"] .pm-mobile-item__sub,body[data-theme="light"] .pm-mobile-item__category{color:#64748b}
body.theme-light .pm-toolbar-search__field,body.theme-light .password-manager-btn,body.theme-light .password-manager-input,body.theme-light .password-manager-textarea,
body[data-theme="light"] .pm-toolbar-search__field,body[data-theme="light"] .password-manager-btn,body[data-theme="light"] .password-manager-input,body[data-theme="light"] .password-manager-textarea{background:rgba(255,255,255,.85);border-color:rgba(21,32,51,.1)}
body.theme-light .password-manager-table th,body.theme-light .password-manager-table td,body[data-theme="light"] .password-manager-table th,body[data-theme="light"] .password-manager-table td{border-bottom:1px solid rgba(226,232,240,.9)}
body.theme-light .lsg-action-btn--primary,body[data-theme="light"] .lsg-action-btn--primary{--lsg-bg:rgba(37,99,235,.08);--lsg-border:rgba(37,99,235,.16);--lsg-text:#2563eb}
body.theme-light .lsg-action-btn--success,body[data-theme="light"] .lsg-action-btn--success{--lsg-text:#15803d}
body.theme-light .lsg-action-btn--warning,body[data-theme="light"] .lsg-action-btn--warning{--lsg-text:#b45309}
body.theme-light .lsg-action-btn--danger,body[data-theme="light"] .lsg-action-btn--danger{--lsg-text:#b91c1c}

@media (max-width:991px){.pm-dashboard-grid,.password-manager-stats,.password-manager-grid{grid-template-columns:1fr}.password-manager-grid-1{grid-column:span 1}}
@media (max-width:768px){.password-manager-page{padding:.75rem}.pm-page-header,.password-manager-card,.password-manager-form-card,.password-manager-toolbar{padding:.9rem}.pm-page-header__main{flex-direction:column}.pm-page-header__identity{width:100%}.pm-page-header__actions,.lsg-page-actions{width:100%;justify-content:flex-start}.pm-toolbar-search{grid-template-columns:1fr}.password-manager-table-wrap{display:none}.password-manager-mobile-list{display:grid;gap:.75rem}}
</style>
