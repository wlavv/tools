<style>
.productivity-manager-page {
    padding: 1rem;
}

.productivity-manager-shell {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
}

.productivityManager-card,
.productivity-manager-card,
.productivity-manager-toolbar,
.productivity-page-header {
    background: linear-gradient(180deg, rgba(255,255,255,.98) 0%, rgba(247,249,252,.98) 100%);
    border: 1px solid var(--border-soft, rgba(21,32,51,.12));
    border-radius: 5px;
    box-shadow: var(--shadow-soft, 0 8px 24px rgba(15,23,42,.05));
}

.productivity-page-header { padding: 1rem 1.125rem; }
.productivity-breadcrumbs { display:flex; align-items:center; flex-wrap:wrap; gap:.45rem; margin-bottom:.85rem; font-size:.8rem; }
.productivity-breadcrumbs__link,
.productivity-breadcrumbs__current,
.productivity-breadcrumbs__sep { color: var(--text-muted, #64748b); }
.productivity-breadcrumbs__link:hover { color: var(--text-primary, #18212b); }

.productivity-page-header__main { display:flex; justify-content:space-between; gap:1rem; align-items:flex-start; flex-wrap:wrap; }
.productivity-page-header__identity { display:flex; align-items:flex-start; gap:.9rem; min-width:0; }
.productivity-page-header__icon {
    width:46px; height:46px; display:inline-flex; align-items:center; justify-content:center; border-radius:5px;
    border:1px solid rgba(37,99,235,.15); background:rgba(37,99,235,.08); color:#2563eb; flex:0 0 auto;
}
.productivity-page-header__title { margin:0; font-size:1.2rem; line-height:1.15; color:var(--text-primary, #18212b); }
.productivity-page-header__subtitle { margin:.35rem 0 0 0; max-width:70ch; color:var(--text-muted, #64748b); line-height:1.55; }

.lsg-page-actions { display:flex; flex-wrap:wrap; justify-content:flex-end; gap:.625rem; }
.lsg-action-form { display:inline-flex; }
.lsg-action-btn {
    --lsg-bg: rgba(37,99,235,.08); --lsg-border: rgba(37,99,235,.16); --lsg-text: #2563eb;
    display:inline-flex; align-items:center; justify-content:center; gap:.6rem; min-height:38px; padding:.65rem .9rem;
    border-radius:5px; border:1px solid var(--lsg-border); background:var(--lsg-bg); color:var(--lsg-text);
    font-weight:600; text-decoration:none; transition:all .2s ease;
}
.lsg-action-btn:hover { filter:brightness(.98); transform:translateY(-1px); }
.lsg-action-btn--compact { min-height:34px; padding:.55rem .7rem; }
.lsg-action-btn--primary { --lsg-bg: rgba(37,99,235,.08); --lsg-border: rgba(37,99,235,.16); --lsg-text: #2563eb; }
.lsg-action-btn--success { --lsg-bg: rgba(34,197,94,.08); --lsg-border: rgba(34,197,94,.18); --lsg-text: #15803d; }
.lsg-action-btn--warning { --lsg-bg: rgba(245,158,11,.08); --lsg-border: rgba(245,158,11,.22); --lsg-text: #b45309; }
.lsg-action-btn--danger { --lsg-bg: rgba(239,68,68,.08); --lsg-border: rgba(239,68,68,.2); --lsg-text: #b91c1c; }

.productivity-dashboard-grid { display:grid; grid-template-columns:1.2fr .8fr; gap:1rem; align-items:stretch; }
.productivity-grid { display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:1rem; }
.productivity-grid-1 { grid-column:span 2; }

.productivity-counters { display:grid; grid-template-columns:repeat(4, minmax(0,1fr)); gap:.75rem; }
.productivity-counter { padding:.95rem; display:grid; gap:.3rem; }
.productivity-counter__label { font-size:.76rem; text-transform:uppercase; letter-spacing:.06em; color:#64748b; }
.productivity-counter__value { font-size:1.35rem; line-height:1; color:#18212b; }

.productivity-manager-card,.productivity-manager-toolbar { padding:1rem; }
.productivity-card-title { margin:0 0 .75rem 0; font-size:1rem; color:#18212b; }
.productivity-list { display:grid; gap:.75rem; }
.productivity-item {
    padding:.9rem; border-radius:5px; border:1px solid rgba(226,232,240,.95); background:rgba(255,255,255,.65);
    display:flex; justify-content:space-between; gap:.75rem; align-items:flex-start;
}
.productivity-item__title { margin:0 0 .25rem 0; color:#18212b; font-weight:600; }
.productivity-item__meta { color:#64748b; font-size:.86rem; }
.productivity-item__note { margin-top:.35rem; font-size:.88rem; color:#334155; }
.productivity-badge { display:inline-flex; align-items:center; justify-content:center; padding:.25rem .55rem; border-radius:999px; font-size:.72rem; border:1px solid transparent; }
.productivity-badge--success { color:#166534; background:rgba(34,197,94,.08); border-color:rgba(34,197,94,.18); }
.productivity-badge--warning { color:#9a3412; background:rgba(245,158,11,.08); border-color:rgba(245,158,11,.22); }
.productivity-badge--neutral { color:#475569; background:rgba(148,163,184,.12); border-color:rgba(148,163,184,.18); }
.productivity-progress { width:100%; height:10px; border-radius:999px; background:rgba(148,163,184,.16); overflow:hidden; }
.productivity-progress__bar { height:100%; border-radius:999px; background:#2563eb; }

.productivity-manager-alert { padding:.85rem 1rem; border-radius:5px; border:1px solid rgba(34,197,94,.18); color:#166534; background:rgba(34,197,94,.08); }
.productivity-empty-state { display:grid; gap:.3rem; justify-items:center; padding:1rem; text-align:center; }
.productivity-empty-state strong { color:#18212b; }
.productivity-empty-state span { color:#64748b; }

@media (max-width: 991px) {
    .productivity-dashboard-grid,
    .productivity-grid,
    .productivity-counters { grid-template-columns:1fr; }
    .productivity-grid-1 { grid-column:span 1; }
}

@media (max-width: 768px) {
    .productivity-manager-page { padding:.75rem; }
    .productivity-page-header,
    .productivity-manager-card,
    .productivity-manager-toolbar { padding:.9rem; }
    .productivity-page-header__main { flex-direction:column; }
    .productivity-page-header__identity { width:100%; }
    .lsg-page-actions { width:100%; justify-content:flex-start; }
    .productivity-item { flex-direction:column; }
}
</style>
