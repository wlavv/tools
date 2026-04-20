<style>
.productivity-manager-page{padding:1rem}
.productivity-manager-shell{display:grid;grid-template-columns:1fr;gap:1rem}

.productivityManager-card,
.productivity-manager-card,
.productivity-manager-toolbar,
.productivity-page-header,
.productivity-counter,
.productivity-item{
    border:1px solid rgba(255,255,255,.08);
    border-radius:5px;
    background:linear-gradient(180deg,rgba(37,47,59,.94) 0%,rgba(32,40,51,.96) 100%);
    box-shadow:0 8px 20px rgba(0,0,0,.14);
}
.productivity-page-header,.productivity-manager-card,.productivity-manager-toolbar{padding:1rem}
.productivity-page-header{padding:1rem 1.125rem}
.productivity-breadcrumbs{display:flex;align-items:center;flex-wrap:wrap;gap:.45rem;margin-bottom:.85rem;font-size:.8rem}
.productivity-breadcrumbs__link,.productivity-breadcrumbs__current,.productivity-breadcrumbs__sep{color:var(--text-muted,#94a3b8)}
.productivity-breadcrumbs__link:hover{color:var(--text-primary,#f8fafc)}
.productivity-page-header__main{display:flex;justify-content:space-between;gap:1rem;align-items:flex-start;flex-wrap:wrap}
.productivity-page-header__identity{display:flex;align-items:flex-start;gap:.9rem;min-width:0}
.productivity-page-header__icon{width:46px;height:46px;display:inline-flex;align-items:center;justify-content:center;border-radius:5px;border:1px solid rgba(37,99,235,.15);background:rgba(37,99,235,.08);color:#60a5fa;flex:0 0 auto}
.productivity-page-header__title{margin:0;font-size:1.2rem;line-height:1.15;color:var(--text-primary,#f8fafc)}
.productivity-page-header__subtitle{margin:.35rem 0 0 0;max-width:70ch;color:var(--text-muted,#94a3b8);line-height:1.55}

.lsg-page-actions{display:flex;flex-wrap:wrap;justify-content:flex-end;gap:.625rem}
.lsg-action-form{display:inline-flex}
.lsg-action-btn{--lsg-bg:rgba(37,99,235,.14);--lsg-border:rgba(37,99,235,.22);--lsg-text:#bfdbfe;display:inline-flex;align-items:center;justify-content:center;gap:.6rem;min-height:38px;padding:.65rem .9rem;border-radius:5px;border:1px solid var(--lsg-border);background:var(--lsg-bg);color:var(--lsg-text);font-weight:600;text-decoration:none;transition:all .2s ease}
.lsg-action-btn:hover{transform:translateY(-1px);filter:brightness(1.03)}
.lsg-action-btn--compact{min-height:34px;padding:.55rem .7rem}
.lsg-action-btn--primary{--lsg-bg:rgba(37,99,235,.14);--lsg-border:rgba(37,99,235,.22);--lsg-text:#bfdbfe}
.lsg-action-btn--success{--lsg-bg:rgba(34,197,94,.12);--lsg-border:rgba(34,197,94,.18);--lsg-text:#86efac}
.lsg-action-btn--warning{--lsg-bg:rgba(245,158,11,.12);--lsg-border:rgba(245,158,11,.18);--lsg-text:#fcd34d}
.lsg-action-btn--danger{--lsg-bg:rgba(239,68,68,.12);--lsg-border:rgba(239,68,68,.2);--lsg-text:#fca5a5}

.productivity-dashboard-grid{display:grid;grid-template-columns:1.2fr .8fr;gap:1rem;align-items:stretch}
.productivity-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}
.productivity-grid-1{grid-column:span 2}

.productivity-counters{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.75rem}
.productivity-counter{padding:.95rem;display:grid;gap:.3rem}
.productivity-counter__label{font-size:.76rem;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8}
.productivity-counter__value{font-size:1.35rem;line-height:1;color:var(--text-primary,#f8fafc)}

.productivity-card-title{margin:0 0 .75rem 0;font-size:1rem;color:var(--text-primary,#f8fafc)}
.productivity-list{display:grid;gap:.75rem}
.productivity-item{padding:.9rem;display:flex;justify-content:space-between;gap:.75rem;align-items:flex-start}
.productivity-item__title{margin:0 0 .25rem 0;color:var(--text-primary,#f8fafc);font-weight:600}
.productivity-item__meta{color:#94a3b8;font-size:.86rem}
.productivity-item__note{margin-top:.35rem;font-size:.88rem;color:#cbd5e1}
.productivity-badge{display:inline-flex;align-items:center;justify-content:center;padding:.25rem .55rem;border-radius:999px;font-size:.72rem;border:1px solid transparent}
.productivity-badge--success{color:#86efac;background:rgba(34,197,94,.08);border-color:rgba(34,197,94,.18)}
.productivity-badge--warning{color:#fcd34d;background:rgba(245,158,11,.08);border-color:rgba(245,158,11,.22)}
.productivity-badge--neutral{color:#cbd5e1;background:rgba(148,163,184,.12);border-color:rgba(148,163,184,.18)}
.productivity-progress{width:100%;height:10px;border-radius:999px;background:rgba(148,163,184,.16);overflow:hidden}
.productivity-progress__bar{height:100%;border-radius:999px;background:#2563eb}

.productivity-manager-alert{padding:.85rem 1rem;border-radius:5px;border:1px solid rgba(34,197,94,.18);color:#86efac;background:rgba(34,197,94,.08)}
.productivity-empty-state{display:grid;gap:.3rem;justify-items:center;padding:1rem;text-align:center}
.productivity-empty-state strong{color:var(--text-primary,#f8fafc)}
.productivity-empty-state span{color:#94a3b8}

body.theme-light .productivityManager-card,
body.theme-light .productivity-manager-card,
body.theme-light .productivity-manager-toolbar,
body.theme-light .productivity-page-header,
body.theme-light .productivity-counter,
body.theme-light .productivity-item,
body[data-theme="light"] .productivityManager-card,
body[data-theme="light"] .productivity-manager-card,
body[data-theme="light"] .productivity-manager-toolbar,
body[data-theme="light"] .productivity-page-header,
body[data-theme="light"] .productivity-counter,
body[data-theme="light"] .productivity-item{background:linear-gradient(180deg,rgba(255,255,255,.98) 0%,rgba(247,249,252,.98) 100%);border:1px solid rgba(21,32,51,.1);box-shadow:0 8px 20px rgba(15,23,42,.06)}
body.theme-light .productivity-page-header__title,body.theme-light .productivity-counter__value,body.theme-light .productivity-card-title,body.theme-light .productivity-item__title,body.theme-light .productivity-empty-state strong,
body[data-theme="light"] .productivity-page-header__title,body[data-theme="light"] .productivity-counter__value,body[data-theme="light"] .productivity-card-title,body[data-theme="light"] .productivity-item__title,body[data-theme="light"] .productivity-empty-state strong{color:#18212b}
body.theme-light .productivity-breadcrumbs__link,body.theme-light .productivity-breadcrumbs__current,body.theme-light .productivity-breadcrumbs__sep,body.theme-light .productivity-page-header__subtitle,body.theme-light .productivity-counter__label,body.theme-light .productivity-item__meta,body.theme-light .productivity-empty-state span,
body[data-theme="light"] .productivity-breadcrumbs__link,body[data-theme="light"] .productivity-breadcrumbs__current,body[data-theme="light"] .productivity-breadcrumbs__sep,body[data-theme="light"] .productivity-page-header__subtitle,body[data-theme="light"] .productivity-counter__label,body[data-theme="light"] .productivity-item__meta,body[data-theme="light"] .productivity-empty-state span{color:#64748b}
body.theme-light .productivity-item__note,body[data-theme="light"] .productivity-item__note{color:#334155}
body.theme-light .lsg-action-btn--primary,body[data-theme="light"] .lsg-action-btn--primary{--lsg-bg:rgba(37,99,235,.08);--lsg-border:rgba(37,99,235,.16);--lsg-text:#2563eb}
body.theme-light .lsg-action-btn--success,body[data-theme="light"] .lsg-action-btn--success{--lsg-text:#15803d}
body.theme-light .lsg-action-btn--warning,body[data-theme="light"] .lsg-action-btn--warning{--lsg-text:#b45309}
body.theme-light .lsg-action-btn--danger,body[data-theme="light"] .lsg-action-btn--danger{--lsg-text:#b91c1c}

@media (max-width:991px){.productivity-dashboard-grid,.productivity-grid,.productivity-counters{grid-template-columns:1fr}.productivity-grid-1{grid-column:span 1}}
@media (max-width:768px){.productivity-manager-page{padding:.75rem}.productivity-page-header,.productivity-manager-card,.productivity-manager-toolbar{padding:.9rem}.productivity-page-header__main{flex-direction:column}.productivity-page-header__identity{width:100%}.lsg-page-actions{width:100%;justify-content:flex-start}.productivity-item{flex-direction:column}}
</style>
