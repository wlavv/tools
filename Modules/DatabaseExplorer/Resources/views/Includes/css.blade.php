<style>
.database-explorer-shell{
    --dbx-bg-card:linear-gradient(180deg,rgba(37,47,59,.94) 0%,rgba(32,40,51,.96) 100%);
    --dbx-bg-input:rgba(255,255,255,.035);
    --dbx-bg-input-focus:rgba(255,255,255,.055);
    --dbx-border:rgba(255,255,255,.09);
    --dbx-border-strong:rgba(255,255,255,.14);
    --dbx-text:var(--text-primary,#f8fafc);
    --dbx-muted:var(--text-muted,#94a3b8);
    --dbx-muted-2:#cbd5e1;
    --dbx-shadow:0 8px 20px rgba(0,0,0,.14);
    --dbx-blue:#60a5fa;
    --dbx-danger:#fca5a5;
    --dbx-success:#86efac;
    --dbx-warning:#fcd34d;
    --dbx-purple:#c4b5fd;
    display:grid;
    grid-template-columns:1fr;
    gap:1rem;
}
body.theme-light .database-explorer-shell,
body[data-theme="light"] .database-explorer-shell,
html[data-theme="light"] .database-explorer-shell,
[data-bs-theme="light"] .database-explorer-shell{
    --dbx-bg-card:linear-gradient(180deg,rgba(255,255,255,.98) 0%,rgba(247,249,252,.98) 100%);
    --dbx-bg-input:rgba(255,255,255,.92);
    --dbx-bg-input-focus:#fff;
    --dbx-border:rgba(21,32,51,.10);
    --dbx-border-strong:rgba(21,32,51,.16);
    --dbx-text:#18212b;
    --dbx-muted:#64748b;
    --dbx-muted-2:#475569;
    --dbx-shadow:0 8px 20px rgba(15,23,42,.06);
    --dbx-blue:#2563eb;
    --dbx-danger:#b91c1c;
    --dbx-success:#15803d;
    --dbx-warning:#b45309;
    --dbx-purple:#7c3aed;
}
.database-explorer-card,
.databaseExplorer-card,
.dbx-toolbar,
.dbx-stat,
.dbx-meta,
.dbx-page-header{
    border:1px solid var(--dbx-border);
    border-radius:5px;
    background:var(--dbx-bg-card);
    box-shadow:var(--dbx-shadow);
    color:var(--dbx-text);
}
.database-explorer-card,.dbx-toolbar,.dbx-page-header{padding:1rem}
.dbx-page-header{padding:1rem 1.125rem}
.dbx-page-header__main{display:flex;justify-content:space-between;gap:1rem;align-items:flex-start;flex-wrap:wrap}
.dbx-page-header__identity{display:flex;align-items:flex-start;gap:.9rem;min-width:0}
.dbx-page-header__icon{width:46px;height:46px;display:inline-flex;align-items:center;justify-content:center;border-radius:5px;border:1px solid rgba(37,99,235,.15);background:rgba(37,99,235,.08);color:var(--dbx-blue);flex:0 0 auto}
.dbx-page-header__title{margin:0;font-size:1.2rem;line-height:1.15;color:var(--dbx-text)}
.dbx-page-header__subtitle{margin:.35rem 0 0;max-width:78ch;color:var(--dbx-muted);line-height:1.55}
.dbx-page-actions{display:flex;flex-wrap:wrap;justify-content:flex-end;gap:.625rem}
.lsg-action-form{display:inline-flex;margin:0}
.lsg-action-btn{--lsg-bg:rgba(37,99,235,.14);--lsg-border:rgba(37,99,235,.22);--lsg-text:#bfdbfe;display:inline-flex;align-items:center;justify-content:center;gap:.6rem;min-height:38px;padding:.65rem .9rem;border-radius:5px;border:1px solid var(--lsg-border);background:var(--lsg-bg);color:var(--lsg-text);font-weight:600;text-decoration:none;transition:all .2s ease;cursor:pointer}
button.lsg-action-btn{font-family:inherit}.lsg-action-btn:hover{transform:translateY(-1px);filter:brightness(1.03);color:var(--lsg-text);text-decoration:none}.lsg-action-btn--compact{min-height:34px;padding:.55rem .7rem}.lsg-action-btn--primary{--lsg-bg:rgba(37,99,235,.14);--lsg-border:rgba(37,99,235,.22);--lsg-text:#bfdbfe}.lsg-action-btn--success{--lsg-bg:rgba(34,197,94,.12);--lsg-border:rgba(34,197,94,.18);--lsg-text:#86efac}.lsg-action-btn--warning{--lsg-bg:rgba(245,158,11,.12);--lsg-border:rgba(245,158,11,.18);--lsg-text:#fcd34d}.lsg-action-btn--danger{--lsg-bg:rgba(239,68,68,.12);--lsg-border:rgba(239,68,68,.2);--lsg-text:#fca5a5}
body.theme-light .lsg-action-btn--primary,body[data-theme="light"] .lsg-action-btn--primary,html[data-theme="light"] .lsg-action-btn--primary,[data-bs-theme="light"] .lsg-action-btn--primary{--lsg-bg:rgba(37,99,235,.08);--lsg-border:rgba(37,99,235,.16);--lsg-text:#2563eb}body.theme-light .lsg-action-btn--success,body[data-theme="light"] .lsg-action-btn--success,html[data-theme="light"] .lsg-action-btn--success,[data-bs-theme="light"] .lsg-action-btn--success{--lsg-text:#15803d}body.theme-light .lsg-action-btn--warning,body[data-theme="light"] .lsg-action-btn--warning,html[data-theme="light"] .lsg-action-btn--warning,[data-bs-theme="light"] .lsg-action-btn--warning{--lsg-text:#b45309}body.theme-light .lsg-action-btn--danger,body[data-theme="light"] .lsg-action-btn--danger,html[data-theme="light"] .lsg-action-btn--danger,[data-bs-theme="light"] .lsg-action-btn--danger{--lsg-text:#b91c1c}
.dbx-alert{padding:.85rem 1rem;border:1px solid rgba(34,197,94,.18);border-radius:5px;background:rgba(34,197,94,.08);color:var(--dbx-success)}
.dbx-note{padding:.85rem 1rem;border:1px solid rgba(96,165,250,.18);border-radius:5px;background:rgba(96,165,250,.08);color:var(--dbx-muted-2);line-height:1.5}
.dbx-stats{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:.75rem}
.dbx-stat{padding:.95rem;display:grid;gap:.3rem}.dbx-stat__label{font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:var(--dbx-muted)}.dbx-stat__value{font-size:1.25rem;line-height:1;color:var(--dbx-text)}.dbx-stat__hint{font-size:.74rem;color:var(--dbx-muted)}
.dbx-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}.dbx-grid-3{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:1rem}.dbx-span-2{grid-column:span 2}.dbx-span-3{grid-column:span 3}
.dbx-toolbar{display:grid;gap:.75rem}.dbx-toolbar-form{display:grid;grid-template-columns:1fr 1fr 1fr auto auto;gap:.75rem;align-items:end}.dbx-form-row{display:grid;gap:.35rem}.dbx-label{display:block;font-size:.72rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--dbx-muted)}.dbx-input,.dbx-select{width:100%;border:1px solid var(--dbx-border);border-radius:5px;padding:.72rem .82rem;background:var(--dbx-bg-input);color:var(--dbx-text);outline:none}.dbx-input:focus,.dbx-select:focus{background:var(--dbx-bg-input-focus);border-color:rgba(37,99,235,.36);box-shadow:0 0 0 3px rgba(37,99,235,.10)}
.database-explorer-table-wrap{overflow-x:auto}.database-explorer-table{width:100%;border-collapse:collapse;min-width:900px;color:var(--dbx-text)}.database-explorer-table th,.database-explorer-table td{padding:.82rem .72rem;border-bottom:1px solid var(--dbx-border);text-align:left;vertical-align:middle}.database-explorer-table th{font-size:.72rem;text-transform:uppercase;letter-spacing:.08em;color:var(--dbx-muted)}.database-explorer-table code{color:var(--dbx-muted-2)}.dbx-table-title{display:grid;gap:.15rem}.dbx-table-title strong{color:var(--dbx-text)}.dbx-table-title span,.dbx-muted{color:var(--dbx-muted)}.dbx-nowrap{white-space:nowrap}.dbx-actions{display:flex;flex-wrap:wrap;gap:.5rem}.dbx-actions--center{justify-content:center}.dbx-definition{max-width:520px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:var(--dbx-muted)}
.dbx-badge{display:inline-flex;align-items:center;justify-content:center;gap:.35rem;padding:.25rem .55rem;border-radius:999px;font-size:.72rem;border:1px solid rgba(148,163,184,.18);background:rgba(148,163,184,.12);color:var(--dbx-muted-2);white-space:nowrap}.dbx-badge--healthy{color:var(--dbx-success);background:rgba(34,197,94,.08);border-color:rgba(34,197,94,.18)}.dbx-badge--warning{color:var(--dbx-warning);background:rgba(245,158,11,.10);border-color:rgba(245,158,11,.20)}.dbx-badge--degraded{color:var(--dbx-purple);background:rgba(124,58,237,.10);border-color:rgba(124,58,237,.20)}.dbx-badge--critical{color:var(--dbx-danger);background:rgba(239,68,68,.10);border-color:rgba(239,68,68,.20)}.dbx-badge--info{color:var(--dbx-blue);background:rgba(96,165,250,.08);border-color:rgba(96,165,250,.18)}
.dbx-health-meter{display:grid;gap:.35rem;min-width:120px}.dbx-health-meter__bar{height:7px;border-radius:999px;background:rgba(148,163,184,.18);overflow:hidden}.dbx-health-meter__fill{height:100%;border-radius:999px;background:var(--dbx-success)}.dbx-health-meter__fill--warning{background:var(--dbx-warning)}.dbx-health-meter__fill--degraded{background:var(--dbx-purple)}.dbx-health-meter__fill--critical{background:var(--dbx-danger)}.dbx-health-meter__label{font-size:.74rem;color:var(--dbx-muted)}
.dbx-meta{padding:.9rem;display:grid;gap:.35rem}.dbx-meta strong{font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:var(--dbx-muted)}.dbx-meta div{color:var(--dbx-text);word-break:break-word}.dbx-section-title{margin:0 0 .85rem;font-size:1rem;color:var(--dbx-text)}
.dbx-mobile-list{display:none;gap:.75rem}.dbx-mobile-item{padding:.95rem}.dbx-mobile-item__header{display:flex;justify-content:space-between;gap:.85rem;align-items:flex-start}.dbx-mobile-item__sub{margin-top:.25rem;color:var(--dbx-muted);font-size:.82rem}.dbx-mobile-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.5rem;margin-top:.75rem}.dbx-mobile-metric{padding:.55rem;border:1px solid var(--dbx-border);border-radius:5px}.dbx-mobile-metric span{display:block;color:var(--dbx-muted);font-size:.72rem;text-transform:uppercase}.dbx-mobile-metric strong{display:block;margin-top:.15rem;color:var(--dbx-text)}
.dbx-tabs{display:flex;flex-wrap:wrap;gap:.5rem;border-bottom:1px solid var(--dbx-border);padding-bottom:.75rem;margin-bottom:1rem}.dbx-tab{border:1px solid var(--dbx-border);border-radius:5px;background:var(--dbx-bg-input);color:var(--dbx-muted-2);padding:.5rem .75rem;cursor:pointer}.dbx-tab.is-active{border-color:rgba(37,99,235,.36);color:var(--dbx-blue);background:rgba(37,99,235,.08)}.dbx-tab-panel{display:none}.dbx-tab-panel.is-active{display:block}
@media (max-width:1200px){.dbx-stats{grid-template-columns:repeat(3,minmax(0,1fr))}.dbx-toolbar-form{grid-template-columns:1fr 1fr}.dbx-grid-3{grid-template-columns:1fr}.dbx-span-3{grid-column:auto}}
@media (max-width:760px){.dbx-stats,.dbx-grid{grid-template-columns:1fr}.dbx-span-2{grid-column:auto}.dbx-toolbar-form{grid-template-columns:1fr}.database-explorer-table-wrap{display:none}.dbx-mobile-list{display:grid}.dbx-page-actions{justify-content:flex-start}.dbx-mobile-grid{grid-template-columns:1fr}}
</style>
