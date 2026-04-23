<style>
.roadmap-manager-page{padding:1rem;color:var(--text-primary,inherit)}
.roadmap-manager-shell{display:grid;grid-template-columns:1fr;gap:1rem}
.rm-page-header,.rm-panel,.rm-form-card,.rm-kpi,.rm-kanban-column,.rm-gantt-wrap,.rm-tree-wrap{background:var(--card-bg,transparent);color:var(--text-primary,inherit);border:1px solid var(--border-soft,rgba(21,32,51,.12));border-radius:5px;box-shadow:var(--shadow-soft,0 8px 24px rgba(15,23,42,.05))}
.rm-page-header{padding:1rem 1.125rem}.rm-panel,.rm-form-card{padding:1rem}
.rm-breadcrumbs{display:flex;align-items:center;flex-wrap:wrap;gap:.45rem;margin-bottom:.85rem;font-size:.8rem}
.rm-breadcrumbs__link,.rm-breadcrumbs__current,.rm-breadcrumbs__sep{color:var(--text-muted,#64748b)}
.rm-breadcrumbs__link:hover{color:var(--text-primary,inherit)}
.rm-page-header__main{display:flex;justify-content:space-between;gap:1rem;align-items:flex-start;flex-wrap:wrap}.rm-page-header__identity{display:flex;align-items:flex-start;gap:.9rem;min-width:0}
.rm-page-header__icon{width:46px;height:46px;display:inline-flex;align-items:center;justify-content:center;border-radius:5px;border:1px solid rgba(37,99,235,.15);background:rgba(37,99,235,.08);color:#2563eb;flex:0 0 auto}
.rm-page-header__title{margin:0;font-size:1.2rem;line-height:1.15;color:var(--text-primary,inherit)}
.rm-page-header__subtitle{margin:.35rem 0 0 0;max-width:70ch;color:var(--text-muted,#64748b);line-height:1.55}
.lsg-page-actions{display:flex;flex-wrap:wrap;justify-content:flex-end;gap:.625rem}.lsg-action-form{display:inline-flex;margin:0}
.lsg-action-btn{--lsg-bg:rgba(37,99,235,.08);--lsg-border:rgba(37,99,235,.16);--lsg-text:#2563eb;display:inline-flex;align-items:center;justify-content:center;gap:.6rem;min-height:38px;padding:.65rem .9rem;border-radius:5px;border:1px solid var(--lsg-border);background:var(--lsg-bg);color:var(--lsg-text);font-weight:600;text-decoration:none;transition:all .2s ease;cursor:pointer}
.lsg-action-btn:hover{filter:brightness(.98);transform:translateY(-1px)}.lsg-action-btn--compact{min-height:34px;padding:.55rem .7rem}
.lsg-action-btn--primary{--lsg-bg:rgba(37,99,235,.08);--lsg-border:rgba(37,99,235,.16);--lsg-text:#2563eb}.lsg-action-btn--success{--lsg-bg:rgba(34,197,94,.08);--lsg-border:rgba(34,197,94,.18);--lsg-text:#15803d}.lsg-action-btn--warning{--lsg-bg:rgba(245,158,11,.08);--lsg-border:rgba(245,158,11,.22);--lsg-text:#b45309}.lsg-action-btn--danger{--lsg-bg:rgba(239,68,68,.08);--lsg-border:rgba(239,68,68,.2);--lsg-text:#b91c1c}
.rm-counters{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.75rem}.rm-kpi{padding:.95rem;display:grid;gap:.3rem}.rm-kpi__label{font-size:.76rem;text-transform:uppercase;letter-spacing:.06em;color:#64748b}.rm-kpi__value{font-size:1.35rem;line-height:1;color:var(--text-primary,inherit)}
.rm-title-row{display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;margin-bottom:1rem}.rm-title-row__title{margin:0;font-size:1.15rem;color:var(--text-primary,inherit)}
.rm-table-wrap{overflow-x:auto}.rm-table{width:100%;border-collapse:collapse;min-width:760px}.rm-table th,.rm-table td{padding:.9rem .75rem;border-bottom:1px solid rgba(226,232,240,.9);text-align:left;vertical-align:middle}.rm-table th{font-size:.75rem;text-transform:uppercase;letter-spacing:.08em;color:#64748b}.rm-table td.text-end{text-align:right}
.rm-table-title{display:grid;gap:.15rem}.rm-table-title strong{color:var(--text-primary,inherit)}.rm-table-title span,.rm-table-url,.rm-muted,.text-muted{color:#64748b}
.rm-table-actions{display:flex;justify-content:flex-end;align-items:center;gap:.5rem;flex-wrap:wrap}.rm-table-actions .lsg-action-form{display:inline-flex}
.rm-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}.rm-form-grid__full{grid-column:span 2}
.rm-label{display:block;margin-bottom:.45rem;font-size:.78rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#64748b}
.rm-input,.rm-select,.rm-textarea{width:100%;border:1px solid rgba(148,163,184,.28);padding:.75rem .85rem;background:var(--input-bg,transparent);color:var(--text-primary,inherit);border-radius:5px}.rm-textarea{min-height:140px;resize:vertical}
.rm-form-actions{display:flex;flex-wrap:wrap;gap:.5rem;justify-content:flex-start;padding-top:1rem;border-top:1px solid rgba(226,232,240,.9);margin-top:1rem}
.rm-meta-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}.rm-meta{display:grid;gap:.35rem;padding:.9rem;border-radius:5px;border:1px solid rgba(226,232,240,.95);background:var(--card-bg-soft,transparent)}.rm-meta__label{font-size:.76rem;text-transform:uppercase;letter-spacing:.06em;color:#64748b;font-weight:700}.rm-meta__value{color:var(--text-primary,inherit)}
.rm-stack{display:grid;gap:1rem}.rm-list{margin:0;padding-left:1rem}.rm-list li+li{margin-top:.35rem}.rm-item-box{padding:.85rem;border:1px solid rgba(226,232,240,.95);border-radius:5px;background:var(--card-bg-soft,transparent)}
.rm-board{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:1rem}.rm-kanban-column{padding:0}.rm-kanban-column__header{padding:.85rem 1rem;border-bottom:1px solid rgba(226,232,240,.9);font-weight:700;color:var(--text-primary,inherit);text-transform:capitalize}.rm-kanban-column__body{padding:1rem}.rm-kanban-item{padding:.75rem;border:1px solid rgba(226,232,240,.95);border-radius:5px;background:var(--card-bg-soft,transparent)}.rm-kanban-item+.rm-kanban-item{margin-top:.75rem}
.rm-gantt-bar-wrap{position:relative;background:rgba(148,163,184,.14);height:28px;border-radius:6px;overflow:hidden}.rm-gantt-bar{position:absolute;top:4px;bottom:4px;border-radius:4px;background:rgba(37,99,235,.72)}
.rm-alert{padding:.85rem 1rem;border-radius:5px;border:1px solid rgba(34,197,94,.18);color:#166534;background:rgba(34,197,94,.08)}
@media (max-width:991px){.rm-counters,.rm-form-grid,.rm-meta-grid,.rm-board{grid-template-columns:1fr}.rm-form-grid__full{grid-column:span 1}}
@media (max-width:768px){.roadmap-manager-page{padding:.75rem}.rm-page-header,.rm-panel,.rm-form-card,.rm-kpi{padding:.9rem}.rm-page-header__main,.rm-title-row{flex-direction:column;align-items:stretch}.lsg-page-actions{justify-content:flex-start}.rm-table-wrap{display:none}}
</style>