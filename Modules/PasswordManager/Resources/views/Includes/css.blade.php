<style>
.password-manager-page,
.password-manager-shell{
    --pm-bg-card: linear-gradient(180deg,rgba(37,47,59,.94) 0%,rgba(32,40,51,.96) 100%);
    --pm-bg-input: rgba(255,255,255,.035);
    --pm-bg-input-focus: rgba(255,255,255,.055);
    --pm-border: rgba(255,255,255,.09);
    --pm-border-strong: rgba(255,255,255,.14);
    --pm-text: var(--text-primary,#f8fafc);
    --pm-muted: var(--text-muted,#94a3b8);
    --pm-muted-2: #cbd5e1;
    --pm-shadow: 0 8px 20px rgba(0,0,0,.14);
    --pm-blue: #60a5fa;
    --pm-danger: #fca5a5;
    --pm-success: #86efac;
    --pm-warning: #fcd34d;
}

body.theme-light .password-manager-page,
body.theme-light .password-manager-shell,
body[data-theme="light"] .password-manager-page,
body[data-theme="light"] .password-manager-shell,
html[data-theme="light"] .password-manager-page,
html[data-theme="light"] .password-manager-shell,
[data-bs-theme="light"] .password-manager-page,
[data-bs-theme="light"] .password-manager-shell{
    --pm-bg-card: linear-gradient(180deg,rgba(255,255,255,.98) 0%,rgba(247,249,252,.98) 100%);
    --pm-bg-input: rgba(255,255,255,.92);
    --pm-bg-input-focus: #ffffff;
    --pm-border: rgba(21,32,51,.10);
    --pm-border-strong: rgba(21,32,51,.16);
    --pm-text: #18212b;
    --pm-muted: #64748b;
    --pm-muted-2: #475569;
    --pm-shadow: 0 8px 20px rgba(15,23,42,.06);
    --pm-blue: #2563eb;
    --pm-danger: #b91c1c;
    --pm-success: #15803d;
    --pm-warning: #b45309;
}

.password-manager-page{padding:1rem}
.password-manager-shell{display:grid;grid-template-columns:1fr;gap:1rem}

.passwordManager-card,
.password-manager-card,
.password-manager-form-card,
.pm-page-header,
.password-manager-meta{
    border:1px solid var(--pm-border);
    border-radius:5px;
    background:var(--pm-bg-card);
    box-shadow:var(--pm-shadow);
    color:var(--pm-text);
}

.pm-page-header,.password-manager-card,.password-manager-form-card{padding:1rem}
.pm-page-header{padding:1rem 1.125rem}
.pm-breadcrumbs{display:flex;align-items:center;flex-wrap:wrap;gap:.45rem;margin-bottom:.85rem;font-size:.8rem}
.pm-breadcrumbs__link,.pm-breadcrumbs__current,.pm-breadcrumbs__sep{color:var(--pm-muted)}
.pm-breadcrumbs__link:hover{color:var(--pm-text)}
.pm-page-header__main{display:flex;justify-content:space-between;gap:1rem;align-items:flex-start;flex-wrap:wrap}
.pm-page-header__identity{display:flex;align-items:flex-start;gap:.9rem;min-width:0}
.pm-page-header__icon{width:46px;height:46px;display:inline-flex;align-items:center;justify-content:center;border-radius:5px;border:1px solid rgba(37,99,235,.15);background:rgba(37,99,235,.08);color:var(--pm-blue);flex:0 0 auto}
.pm-page-header__title{margin:0;font-size:1.2rem;line-height:1.15;color:var(--pm-text)}
.pm-page-header__subtitle{margin:.35rem 0 0 0;max-width:70ch;color:var(--pm-muted);line-height:1.55}

.lsg-page-actions{display:flex;flex-wrap:wrap;justify-content:flex-end;gap:.625rem}
.lsg-action-form{display:inline-flex;margin:0}
.lsg-action-btn{--lsg-bg:rgba(37,99,235,.14);--lsg-border:rgba(37,99,235,.22);--lsg-text:#bfdbfe;display:inline-flex;align-items:center;justify-content:center;gap:.6rem;min-height:38px;padding:.65rem .9rem;border-radius:5px;border:1px solid var(--lsg-border);background:var(--lsg-bg);color:var(--lsg-text);font-weight:600;text-decoration:none;transition:all .2s ease;cursor:pointer}
button.lsg-action-btn{font-family:inherit}
.lsg-action-btn:hover{transform:translateY(-1px);filter:brightness(1.03);color:var(--lsg-text);text-decoration:none}
.lsg-action-btn--compact{min-height:34px;padding:.55rem .7rem}
.lsg-action-btn--primary{--lsg-bg:rgba(37,99,235,.14);--lsg-border:rgba(37,99,235,.22);--lsg-text:#bfdbfe}
.lsg-action-btn--success{--lsg-bg:rgba(34,197,94,.12);--lsg-border:rgba(34,197,94,.18);--lsg-text:#86efac}
.lsg-action-btn--warning{--lsg-bg:rgba(245,158,11,.12);--lsg-border:rgba(245,158,11,.18);--lsg-text:#fcd34d}
.lsg-action-btn--danger{--lsg-bg:rgba(239,68,68,.12);--lsg-border:rgba(239,68,68,.2);--lsg-text:#fca5a5}

body.theme-light .lsg-action-btn--primary,body[data-theme="light"] .lsg-action-btn--primary,html[data-theme="light"] .lsg-action-btn--primary,[data-bs-theme="light"] .lsg-action-btn--primary{--lsg-bg:rgba(37,99,235,.08);--lsg-border:rgba(37,99,235,.16);--lsg-text:#2563eb}
body.theme-light .lsg-action-btn--success,body[data-theme="light"] .lsg-action-btn--success,html[data-theme="light"] .lsg-action-btn--success,[data-bs-theme="light"] .lsg-action-btn--success{--lsg-text:#15803d}
body.theme-light .lsg-action-btn--warning,body[data-theme="light"] .lsg-action-btn--warning,html[data-theme="light"] .lsg-action-btn--warning,[data-bs-theme="light"] .lsg-action-btn--warning{--lsg-text:#b45309}
body.theme-light .lsg-action-btn--danger,body[data-theme="light"] .lsg-action-btn--danger,html[data-theme="light"] .lsg-action-btn--danger,[data-bs-theme="light"] .lsg-action-btn--danger{--lsg-text:#b91c1c}

.password-manager-table-wrap{overflow-x:visible;max-width:100%}
.password-manager-table-wrap .dataTables_wrapper,
.password-manager-table-wrap .dataTables_scroll,
.password-manager-table-wrap .dataTables_scrollHead,
.password-manager-table-wrap .dataTables_scrollBody{
    width:100%!important;
    max-width:100%!important;
    overflow-x:visible!important;
}
.password-manager-table-wrap .dataTables_scrollBody{
    overflow-y:visible!important;
}
.password-manager-table{width:100%;border-collapse:collapse;table-layout:fixed;color:var(--pm-text)}
.password-manager-table th,.password-manager-table td{padding:.85rem .75rem;border-bottom:1px solid var(--pm-border);text-align:left;vertical-align:middle}
.password-manager-table th{font-size:.75rem;text-transform:uppercase;letter-spacing:.08em;color:var(--pm-muted)}
.pm-table-title{display:grid;gap:.15rem}
.pm-table-title strong{color:var(--pm-text)}
.pm-table-title span,.pm-table-url{color:var(--pm-muted)}

.password-manager-badge{display:inline-flex;align-items:center;justify-content:center;padding:.25rem .55rem;border-radius:999px;font-size:.72rem;border:1px solid transparent}
.password-manager-badge--favorite{color:var(--pm-success);background:rgba(34,197,94,.08);border-color:rgba(34,197,94,.18)}
.password-manager-badge--neutral{color:var(--pm-muted-2);background:rgba(148,163,184,.12);border-color:rgba(148,163,184,.18)}

.password-manager-actions{display:flex;flex-wrap:wrap;gap:.5rem}
.password-manager-actions--center{justify-content:center}
.password-manager-btn,.password-manager-input,.password-manager-textarea{border-radius:5px}
.password-manager-btn{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:.65rem .95rem;text-decoration:none;cursor:pointer;border:1px solid var(--pm-border);background:var(--pm-bg-input);color:var(--pm-text)}
.password-manager-btn-primary{background:#2563eb;color:#fff;border-color:#2563eb}
.password-manager-input,.password-manager-textarea{width:100%;border:1px solid var(--pm-border);padding:.72rem .82rem;background:var(--pm-bg-input);color:var(--pm-text);outline:none;transition:border-color .2s ease, background .2s ease, box-shadow .2s ease}
.password-manager-input:focus,.password-manager-textarea:focus{background:var(--pm-bg-input-focus);border-color:rgba(37,99,235,.36);box-shadow:0 0 0 3px rgba(37,99,235,.10)}
.password-manager-input::placeholder,.password-manager-textarea::placeholder{color:var(--pm-muted)}
.password-manager-textarea{min-height:120px;resize:vertical}

.password-manager-form{display:grid;gap:.85rem}
.pm-form-section{display:grid;gap:.85rem;padding:.9rem;border:1px solid var(--pm-border);border-radius:5px;background:rgba(255,255,255,.025)}
.pm-form-section__header{display:flex;align-items:flex-start;gap:.75rem;padding-bottom:.75rem;border-bottom:1px solid var(--pm-border)}
.pm-form-section__header strong{display:block;color:var(--pm-text);font-size:.95rem}
.pm-form-section__header span:not(.pm-form-section__icon){display:block;color:var(--pm-muted);font-size:.82rem;margin-top:.15rem}
.pm-form-section__icon{width:34px;height:34px;border-radius:5px;display:inline-flex;align-items:center;justify-content:center;border:1px solid rgba(37,99,235,.16);background:rgba(37,99,235,.08);color:var(--pm-blue);flex:0 0 auto}
.password-manager-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}
.password-manager-grid--compact{gap:.8rem}
.password-manager-grid-1{grid-column:span 2}
.pm-password-field{display:grid;grid-template-columns:minmax(0,1fr) auto auto;gap:.6rem;align-items:start}
.pm-password-field--meta{margin-top:.45rem}
.pm-password-field--textarea{align-items:start}
.pm-password-field--textarea .lsg-action-btn{align-self:start}

.password-manager-label{display:block;margin-bottom:.4rem;font-size:.76rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--pm-muted)}
.pm-field-error{margin-top:.35rem;font-size:.78rem;color:var(--pm-danger)}
.password-manager-meta{display:grid;gap:.35rem;padding:.9rem}
.password-manager-meta strong,.password-manager-meta__label{font-size:.76rem;text-transform:uppercase;letter-spacing:.06em;color:var(--pm-muted);font-weight:700}
.password-manager-meta div{color:var(--pm-text)}
.pm-show-header{display:flex;justify-content:space-between;gap:1rem;flex-wrap:wrap}
.pm-show-title{margin:0;font-size:1.15rem;color:var(--pm-text)}
.pm-show-category{margin-top:.35rem;color:var(--pm-muted)}
.pm-show-grid{margin-top:1rem}

.password-manager-alert{padding:.85rem 1rem;border-radius:5px;border:1px solid rgba(34,197,94,.24);color:var(--pm-success);background:rgba(34,197,94,.08)}
.password-manager-alert--warning{border-color:rgba(245,158,11,.28);color:var(--pm-warning);background:rgba(245,158,11,.09)}

.pm-empty-state{display:grid;gap:.3rem;justify-items:center;padding:1rem;text-align:center}
.pm-empty-state strong{color:var(--pm-text)}
.pm-empty-state span,.pm-mobile-item__sub,.pm-mobile-item__category{color:var(--pm-muted)}
.password-manager-mobile-list{display:none}
.password-manager-mobile-item{padding:.95rem}
.pm-mobile-item__header{display:flex;justify-content:space-between;gap:.75rem;align-items:flex-start}
.pm-mobile-item__header strong{color:var(--pm-text)}
.pm-pagination-wrap{margin-top:1rem}

.pm-secret-textarea{color:transparent;text-shadow:0 0 8px rgba(100,116,139,.85);caret-color:transparent;user-select:none}
.pm-secret-textarea--revealed{color:var(--pm-text);text-shadow:none;caret-color:auto;user-select:text}


.password-manager-shell--form{
    max-width:560px;
}
.password-manager-alert--narrow{
    max-width:560px;
    width:100%;
}
.password-manager-form-card--narrow{
    width:100%;
    padding:1rem;
}
.password-manager-form--vertical{
    display:grid;
    gap:.75rem;
}
.pm-form-header-compact{
    display:flex;
    align-items:flex-start;
    gap:.75rem;
    padding-bottom:.85rem;
    border-bottom:1px solid var(--pm-border);
    margin-bottom:.1rem;
}
.pm-form-header-compact strong{
    display:block;
    color:var(--pm-text);
    font-size:1rem;
    line-height:1.2;
}
.pm-form-header-compact span:not(.pm-form-section__icon){
    display:block;
    color:var(--pm-muted);
    font-size:.82rem;
    margin-top:.15rem;
}
.pm-form-row{
    display:grid;
    gap:.35rem;
}
.pm-password-field--single-action{
    grid-template-columns:minmax(0,1fr) auto;
}
.password-manager-table--lean{min-width:0}
.password-manager-table--lean th,
.password-manager-table--lean td{
    padding:.68rem .55rem;
}
.password-manager-table th,
.password-manager-table td{
    min-width:0;
    white-space:normal;
}
.password-manager-table td{
    overflow:hidden;
    text-overflow:ellipsis;
}
.pm-col-title{width:21%}
.pm-col-category{width:12%}
.pm-col-url{width:25%}
.pm-col-username{width:16%}
.pm-col-password{width:9%}
.pm-col-date{width:11%}
.password-manager-table th:last-child{width:76px}
.password-manager-table .password-manager-badge{
    max-width:100%;
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;
}
.pm-table-link,
.pm-copy-inline,
.pm-mobile-action{
    color:var(--pm-text);
    border:0px solid var(--pm-border);
    background: transparent;
    border-radius:5px;
    text-decoration:none;
    display:inline-flex;
    align-items:center;
    gap:.45rem;
    max-width:100%;
    min-height:32px;
    padding:.42rem .6rem;
    font-size:.86rem;
    cursor:pointer;
    transition:all .2s ease;
}
.pm-table-link span,
.pm-copy-inline span{
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;
}
.pm-table-link{
    max-width:100%;
    color:var(--pm-blue);
}
.pm-copy-inline{
    font-family:inherit;
}
.pm-copy-inline--secret{
    color:var(--pm-muted-2);
    letter-spacing:.05em;
    padding-left:.35rem;
    padding-right:.35rem;
}
.pm-table-link:hover,
.pm-copy-inline:hover,
.pm-mobile-action:hover,
.pm-copy-success{
    border-color:rgba(37,99,235,.28);
    background:var(--pm-bg-input-focus);
    color:var(--pm-blue);
    text-decoration:none;
}
.pm-muted{
    color:var(--pm-muted);
}
.pm-mobile-quick-actions{
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:.5rem;
    margin-top:.85rem;
}
.pm-mobile-action{
    justify-content:center;
    width:100%;
}
.pm-mobile-action span{
    white-space:nowrap;
}
@media (max-width:768px){
    .password-manager-shell--form{
        max-width:none;
        margin:0;
    }
    .pm-mobile-quick-actions{
        grid-template-columns:1fr;
    }
}

@media (max-width:991px){.password-manager-grid{grid-template-columns:1fr}.password-manager-grid-1{grid-column:span 1}}
@media (max-width:768px){.password-manager-page{padding:.75rem}.pm-page-header,.password-manager-card,.password-manager-form-card{padding:.9rem}.pm-page-header__main{flex-direction:column}.pm-page-header__identity{width:100%}.pm-page-header__actions,.lsg-page-actions{width:100%;justify-content:flex-start}.password-manager-table-wrap{display:none}.password-manager-mobile-list{display:grid;gap:.75rem}.pm-password-field{grid-template-columns:minmax(0,1fr) auto auto}.pm-form-section{padding:.8rem}}

/* Select options contrast fix */
.pm-wrap select,
.pm-wrap .form-select {
    background-color: var(--pm-input-bg) !important;
    color: var(--pm-text) !important;
}

.pm-wrap select option,
.pm-wrap .form-select option {
    background-color: #ffffff !important;
    color: #111827 !important;
}

html[data-theme="dark"] .pm-wrap select option,
html[data-theme="dark"] .pm-wrap .form-select option,
html[data-bs-theme="dark"] .pm-wrap select option,
html[data-bs-theme="dark"] .pm-wrap .form-select option,
body.dark .pm-wrap select option,
body.dark-mode .pm-wrap select option {
    background-color: #1f2937 !important;
    color: #f9fafb !important;
}

.pm-wrap select option:checked,
.pm-wrap .form-select option:checked {
    background-color: #2563eb !important;
    color: #ffffff !important;
}

/* =========================
   SELECTS
========================= */

.pm-wrap select,
.pm-wrap .form-select {
    background: #273142 !important;
    color: #f3f4f6 !important;
    border-color: rgba(255,255,255,.15) !important;
}

/* Dropdown options */
.pm-wrap select option,
.pm-wrap .form-select option {
    background-color: #1f2937 !important;
    color: #f9fafb !important;
}

/* Selected option */
.pm-wrap select option:checked,
.pm-wrap .form-select option:checked {
    background: #2563eb !important;
    color: #ffffff !important;
}

/* Hover */
.pm-wrap select option:hover,
.pm-wrap .form-select option:hover {
    background: #374151 !important;
    color: #ffffff !important;
}

/* LIGHT MODE */
html[data-theme="light"] .pm-wrap select,
html[data-bs-theme="light"] .pm-wrap select,
body.light .pm-wrap select {
    background: #ffffff !important;
    color: #111827 !important;
}

html[data-theme="light"] .pm-wrap select option,
html[data-bs-theme="light"] .pm-wrap select option,
body.light .pm-wrap select option {
    background: #ffffff !important;
    color: #111827 !important;
}
</style>
