<style>
.password-manager-page {
    padding: 1rem;
}

.password-manager-shell {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
}

.passwordManager-card,
.password-manager-card,
.password-manager-form-card,
.password-manager-toolbar,
.pm-page-header {
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(247, 249, 252, 0.98) 100%);
    border: 1px solid var(--border-soft, rgba(21, 32, 51, 0.12));
    border-radius: 5px;
    box-shadow: var(--shadow-soft, 0 8px 24px rgba(15, 23, 42, 0.05));
}
.pm-page-header { padding: 1rem 1.125rem; }
.pm-breadcrumbs { display:flex; align-items:center; flex-wrap:wrap; gap:.45rem; margin-bottom:.85rem; font-size:.8rem; }
.pm-breadcrumbs__link,.pm-breadcrumbs__current,.pm-breadcrumbs__sep { color: var(--text-muted, #64748b); }
.pm-breadcrumbs__link:hover { color: var(--text-primary, #18212b); }
.pm-page-header__main { display:flex; justify-content:space-between; gap:1rem; align-items:flex-start; flex-wrap:wrap; }
.pm-page-header__identity { display:flex; align-items:flex-start; gap:.9rem; min-width:0; }
.pm-page-header__icon { width:46px; height:46px; display:inline-flex; align-items:center; justify-content:center; border-radius:5px; border:1px solid rgba(37,99,235,.15); background:rgba(37,99,235,.08); color:#2563eb; flex:0 0 auto; }
.pm-page-header__title { margin:0; font-size:1.2rem; line-height:1.15; color:var(--text-primary, #18212b); }
.pm-page-header__subtitle { margin:.35rem 0 0 0; max-width:70ch; color:var(--text-muted, #64748b); line-height:1.55; }
.lsg-page-actions { display:flex; flex-wrap:wrap; justify-content:flex-end; gap:.625rem; }
.lsg-action-btn { --lsg-bg: rgba(37,99,235,.08); --lsg-border: rgba(37,99,235,.16); --lsg-text: #2563eb; display:inline-flex; align-items:center; justify-content:center; gap:.6rem; min-height:38px; padding:.65rem .9rem; border-radius:5px; border:1px solid var(--lsg-border); background:var(--lsg-bg); color:var(--lsg-text); font-weight:600; text-decoration:none; transition:all .2s ease; }
.lsg-action-btn:hover { filter:brightness(.98); transform:translateY(-1px); }
.lsg-action-btn--compact { min-height:34px; padding:.55rem .7rem; }
.lsg-action-btn--primary { --lsg-bg: rgba(37,99,235,.08); --lsg-border: rgba(37,99,235,.16); --lsg-text: #2563eb; }
.lsg-action-btn--success { --lsg-bg: rgba(34,197,94,.08); --lsg-border: rgba(34,197,94,.18); --lsg-text: #15803d; }
.lsg-action-btn--warning { --lsg-bg: rgba(245,158,11,.08); --lsg-border: rgba(245,158,11,.22); --lsg-text: #b45309; }
.lsg-action-btn--danger { --lsg-bg: rgba(239,68,68,.08); --lsg-border: rgba(239,68,68,.2); --lsg-text: #b91c1c; }
.pm-dashboard-grid { display:grid; grid-template-columns:1.2fr .8fr; gap:1rem; align-items:stretch; }
.pm-toolbar-grid,.password-manager-toolbar { display:grid; gap:.75rem; }
.password-manager-toolbar { padding:1rem; }
.pm-toolbar-search { display:grid; grid-template-columns:1fr auto; gap:.75rem; align-items:center; }
.pm-toolbar-search__field { display:grid; grid-template-columns:auto 1fr; gap:.7rem; align-items:center; border-radius:5px; border:1px solid rgba(148,163,184,.25); padding:0 0 0 .8rem; background:rgba(255,255,255,.85); }
.pm-toolbar-search__field i { color:#64748b; }
.pm-toolbar-search__field .password-manager-input { border:0; background:transparent; padding-left:0; padding-right:0; }
.password-manager-stats { display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:.75rem; }
.password-manager-stat { padding:.95rem; display:grid; gap:.3rem; }
.password-manager-stat__label { font-size:.76rem; text-transform:uppercase; letter-spacing:.06em; color:#64748b; }
.password-manager-stat__value { font-size:1.35rem; line-height:1; color:#18212b; }
.password-manager-card,.password-manager-form-card { padding:1rem; }
.password-manager-table-wrap { overflow-x:auto; }
.password-manager-table { width:100%; border-collapse:collapse; min-width:760px; }
.password-manager-table th,.password-manager-table td { padding:.9rem .75rem; border-bottom:1px solid rgba(226,232,240,.9); text-align:left; vertical-align:middle; }
.password-manager-table th { font-size:.75rem; text-transform:uppercase; letter-spacing:.08em; color:#64748b; }
.pm-table-title { display:grid; gap:.15rem; }
.pm-table-title strong { color:#18212b; }
.pm-table-title span,.pm-table-url { color:#64748b; }
.password-manager-badge { display:inline-flex; align-items:center; justify-content:center; padding:.25rem .55rem; border-radius:999px; font-size:.72rem; border:1px solid transparent; }
.password-manager-badge--favorite { color:#166534; background:rgba(34,197,94,.08); border-color:rgba(34,197,94,.18); }
.password-manager-badge--neutral { color:#475569; background:rgba(148,163,184,.12); border-color:rgba(148,163,184,.18); }
.password-manager-actions { display:flex; flex-wrap:wrap; gap:.5rem; }
.password-manager-actions--center { justify-content:center; }
.password-manager-btn,.password-manager-input,.password-manager-textarea { border-radius:5px; }
.password-manager-btn { display:inline-flex; align-items:center; justify-content:center; min-height:42px; padding:.65rem .95rem; text-decoration:none; cursor:pointer; border:1px solid rgba(148,163,184,.22); background:rgba(255,255,255,.85); color:#18212b; }
.password-manager-btn-primary { background:#111827; color:#fff; border-color:#111827; }
.password-manager-input,.password-manager-textarea { width:100%; border:1px solid rgba(148,163,184,.28); padding:.75rem .85rem; background:rgba(255,255,255,.92); }
.password-manager-textarea { min-height:140px; resize:vertical; }
.password-manager-grid { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:1rem; }
.password-manager-grid-1 { grid-column:span 2; }
.pm-password-field { display:grid; grid-template-columns:1fr auto; gap:.6rem; align-items:center; }
.password-manager-label { display:block; margin-bottom:.45rem; font-size:.78rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:#64748b; }
.password-manager-meta { display:grid; gap:.35rem; padding:.9rem; border-radius:5px; border:1px solid rgba(226,232,240,.95); background:rgba(255,255,255,.65); }
.password-manager-meta__label { font-size:.76rem; text-transform:uppercase; letter-spacing:.06em; color:#64748b; font-weight:700; }
.pm-show-title { margin:0; font-size:1.15rem; color:#18212b; }
.pm-show-category { margin-top:.35rem; color:#64748b; }
.pm-show-grid { margin-top:1rem; }
.password-manager-alert { padding:.85rem 1rem; border-radius:5px; border:1px solid rgba(34,197,94,.18); color:#166534; background:rgba(34,197,94,.08); }
.password-manager-alert--warning { border-color:rgba(245,158,11,.22); color:#9a3412; background:rgba(245,158,11,.08); }
.pm-empty-state { display:grid; gap:.3rem; justify-items:center; padding:1rem; text-align:center; }
.pm-empty-state strong { color:#18212b; }
.pm-empty-state span,.pm-mobile-item__sub,.pm-mobile-item__category { color:#64748b; }
.password-manager-mobile-list { display:none; }
.password-manager-mobile-item { padding:.95rem; }
.pm-mobile-item__header { display:flex; justify-content:space-between; gap:.75rem; align-items:flex-start; }
.pm-pagination-wrap { margin-top:1rem; }
@media (max-width: 991px) { .pm-dashboard-grid,.password-manager-stats,.password-manager-grid { grid-template-columns:1fr; } .password-manager-grid-1 { grid-column:span 1; } }
@media (max-width: 768px) { .password-manager-page { padding:.75rem; } .pm-page-header,.password-manager-card,.password-manager-form-card,.password-manager-toolbar { padding:.9rem; } .pm-page-header__main { flex-direction:column; } .pm-page-header__identity { width:100%; } .pm-page-header__actions,.lsg-page-actions { width:100%; justify-content:flex-start; } .pm-toolbar-search { grid-template-columns:1fr; } .password-manager-table-wrap { display:none; } .password-manager-mobile-list { display:grid; gap:.75rem; } }
</style>
