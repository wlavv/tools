@once
<style>
.tasks-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px}
.tasks-member-nav{display:flex;flex-wrap:wrap;gap:8px;border-bottom:1px solid var(--border-soft, rgba(255,255,255,.08));padding-bottom:2px}
.tasks-member-chip{display:grid;grid-template-columns:1fr auto;grid-template-areas:'name percent' 'progress percent';align-items:center;gap:2px 10px;border:1px solid transparent;border-bottom:0;border-radius:14px 14px 0 0;padding:12px 14px;cursor:pointer;font-weight:700;text-align:left;transition:.2s ease;background:transparent;color:inherit;min-height:64px;min-width:180px;max-width:260px}
.tasks-member-chip.active,.tasks-member-chip:hover{transform:translateY(-1px);border-color:var(--border-soft, rgba(255,255,255,.12));box-shadow:none}
.member-chip-name{grid-area:name;font-size:1rem}
.member-chip-progress{grid-area:progress;font-size:.82rem;opacity:.78;font-weight:600}
.member-chip-percent{grid-area:percent;font-size:1.15rem;font-weight:800;align-self:center}
.task-day-pill{display:inline-flex;align-items:center;gap:8px;border:1px solid var(--border-soft, rgba(255,255,255,.08));border-radius:999px;padding:8px 12px;font-size:.88rem}
.member-summary-grid,.dashboard-member-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px}
.member-summary-card{padding:18px}
.tasks-progress{height:10px;background:rgba(127,127,127,.12)}
.tasks-progress .progress-bar{border-radius:999px}
.member-summary-meta{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
.summary-value{font-size:1.05rem;font-weight:700;line-height:1.2}
.reward-stack{display:grid;gap:12px}
.reward-box{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;padding:12px 14px;border:1px solid var(--border-soft, rgba(255,255,255,.08));border-radius:14px;background:transparent}
.reward-box.is-achieved{border-color:rgba(25,135,84,.35)}
.reward-label{font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;opacity:.72;margin-bottom:4px}
.reward-title{font-weight:700}
.reward-meta,.reward-progress{font-size:.86rem;opacity:.84}
.tasks-mobile-hint{display:none;font-size:.84rem;opacity:.75}
.task-list{display:grid;gap:10px}
.task-list--compact{gap:8px}
.task-card{border:1px solid var(--border-soft, rgba(255,255,255,.08));border-radius:14px;padding:10px 12px;background:transparent;transition:.18s ease}
.task-card.is-done{border-color:rgba(25,135,84,.35)}
.task-card.is-saving{opacity:.65;pointer-events:none}
.task-row{display:grid;grid-template-columns:minmax(0,1fr) 220px;gap:12px;align-items:center}
.task-row-main{display:flex;align-items:center;gap:12px;min-width:0}
.task-card-left{display:flex;align-items:flex-start;gap:14px;min-width:0;width:100%}
.task-copy{min-width:0;flex:1}
.task-title-row{display:flex;justify-content:space-between;gap:12px;align-items:flex-start}
.task-row-top{display:flex;align-items:center;justify-content:space-between;gap:10px}
.task-title{font-size:1rem;font-weight:700;margin:0}
.task-meta{display:flex;flex-wrap:wrap;gap:6px;font-size:.82rem;opacity:.92}
.task-image{width:64px;height:64px;object-fit:cover;border-radius:10px;border:1px solid var(--border-soft, rgba(255,255,255,.12))}
.task-status-pill{display:inline-flex;align-items:center;gap:6px;border-radius:5px;padding:6px 10px;font-size:.78rem;border:1px solid var(--border-soft, rgba(255,255,255,.08));background:transparent}
.task-status-pill--accent{font-weight:700}
.task-state-badge{display:inline-flex;align-items:center;border-radius:999px;padding:6px 10px;font-size:.78rem;font-weight:700;border:1px solid var(--border-soft, rgba(255,255,255,.08))}
.task-state-badge.is-done{color:#198754;border-color:rgba(25,135,84,.35)}
.task-state-badge.is-pending{color:#d39e00;border-color:rgba(211,158,0,.35)}
.task-toggle{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}
.task-toggle--compact{width:100%}
.task-toggle-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;width:100%;min-height:42px;border-radius:10px;border:1px solid var(--border-soft, rgba(255,255,255,.08));background:transparent;color:inherit;font-weight:700;transition:.18s ease;font-size:.88rem}
.task-toggle-btn.active.is-success{border-color:rgba(25,135,84,.45);color:#198754}
.task-toggle-btn.active.is-danger{border-color:rgba(220,53,69,.45);color:#dc3545}
.stats-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px}
.stats-card{height:100%}
.stats-card .card-body{padding:16px 18px}
.stats-value{font-size:2rem;font-weight:800;line-height:1.1;margin-top:6px}
.stats-cards--compact{grid-template-columns:repeat(2,minmax(0,1fr))}
.stats-cards--compact .stats-value{font-size:1.3rem}
.muted-small{font-size:.78rem;opacity:.72;text-transform:uppercase;letter-spacing:.04em}
.table.tasks-table td,.table.tasks-table th{vertical-align:middle;padding:.6rem .65rem}
.weekday-badges,.inline-days{display:flex;flex-wrap:wrap;gap:5px}
.weekday-badges .badge,.inline-days label{font-size:.72rem;padding:.35rem .5rem;border:0px solid var(--border-soft, rgba(255,255,255,.08));border-radius:8px;background:transparent;cursor:pointer;margin:0}
.inline-days input{margin-right:4px}
.selected-days-preview{margin-top:6px;font-size:.72rem;opacity:.78;line-height:1.2}
.days-cell{display:flex;flex-direction:column;justify-content:center}
.calendar-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:16px}
.day-card{border:1px solid var(--border-soft, rgba(255,255,255,.08));border-radius:16px}
.day-card .card-header{background:transparent;border-bottom:1px solid var(--border-soft, rgba(255,255,255,.08));padding:14px 16px}
.day-card .card-body{padding:14px 16px}
.calendar-member-tabs{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px}
.calendar-member-tab{border:1px solid var(--border-soft, rgba(255,255,255,.08));background:transparent;border-radius:999px;padding:7px 12px;font-size:.84rem;font-weight:600;cursor:pointer}
.calendar-member-tab.active{border-color:rgba(13,110,253,.45);box-shadow:0 8px 20px rgba(0,0,0,.12)}
.calendar-member-panel{display:none}
.calendar-member-panel.active{display:block}
.calendar-task-item{display:flex;justify-content:space-between;gap:10px;padding:9px 0;border-bottom:1px solid var(--border-soft, rgba(255,255,255,.08))}
.calendar-task-item:last-child{border-bottom:0}
.inline-create-row td{background:rgba(255,255,255,.02)}
.member-line,.task-line,.reward-line{display:grid;gap:8px;align-items:center;width:100%}
.member-line{grid-template-columns:minmax(280px,2.2fr) 140px 140px 100px 90px minmax(180px,1.2fr) 44px 44px}
.task-line{grid-template-columns:80px minmax(120px,2fr) 100px 90px minmax(400px,1.5fr) 90px 120px 120px 100px 70px 44px;}
.reward-line{grid-template-columns:160px 90px minmax(200px,1.5fr) minmax(260px,2fr) 90px 60px 90px}
.reward-line-override{grid-template-columns:90px 70px 160px 90px minmax(180px,1.4fr) minmax(220px,2fr) 90px 60px 90px}
.compact-input,.compact-select{width:100%;min-width:0}
.line-actions{display:flex;justify-content:flex-end;gap:8px}
.alert.alert-success{background:rgba(25,135,84,.16)!important;border:1px solid rgba(25,135,84,.4)!important;color:#d1ffe5!important}
.alert.alert-success *{color:inherit!important}
.card-subtle{background:rgba(255,255,255,.02);border:1px solid var(--border-soft, rgba(255,255,255,.08));border-radius:14px;padding:12px}.section-header{display:flex;justify-content:space-between;align-items:center;gap:12px}
@media (max-width: 1600px){.task-line{grid-template-columns:70px minmax(200px,1.6fr) 150px 100px minmax(200px,1.4fr) 80px 110px 110px 90px 60px 44px}}
@media (max-width: 1400px){.reward-line,.reward-line-override{min-width:1100px}}
@media (max-width: 1200px){.member-line,.task-line{grid-template-columns:repeat(2,minmax(0,1fr))}.member-line .full-row,.task-line .full-row{grid-column:1/-1}}
@media (max-width: 768px){
    .member-summary-grid,.calendar-grid,.dashboard-member-grid,.member-summary-meta{grid-template-columns:1fr}
    .tasks-member-nav{display:flex;flex-wrap:nowrap;overflow:auto;padding-bottom:6px}
    .tasks-member-chip{min-width:170px;max-width:none}
    .task-row{grid-template-columns:1fr}
    .task-card{padding:10px}
    .task-row-main{gap:10px;align-items:flex-start}
    .task-row-top{align-items:flex-start;flex-direction:column}
    .task-card-left{gap:10px}
    .task-image{width:48px;height:48px}
    .task-toggle{grid-template-columns:1fr 1fr}
    .tasks-mobile-hint{display:block}
    .member-line,.task-line,.reward-line,.reward-line-override{grid-template-columns:1fr;min-width:0}
}

.dashboard-insights-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px}
.tasks-dashboard-toolbar{display:flex;align-items:center;justify-content:flex-end;gap:8px;flex-wrap:wrap}
.tasks-dashboard-toolbar .tasks-icon-btn{width:40px;height:40px;min-width:40px;padding:0;display:inline-flex;align-items:center;justify-content:center;line-height:1}
.tasks-dashboard-toolbar .tasks-icon-btn i{line-height:1}
.tasks-month-selector{display:grid;grid-template-columns:150px 96px 40px;gap:8px;align-items:center;margin:0}
.tasks-month-selector .form-select,.tasks-month-selector .form-control{height:40px;min-height:40px;padding-top:0;padding-bottom:0;line-height:40px}
.gamification-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:10px}
.mini-kpi{border:1px solid var(--border-soft,rgba(255,255,255,.08));border-radius:12px;padding:10px 12px;background:rgba(127,127,127,.04)}
.mini-kpi-value{font-size:.98rem;font-weight:700;display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.medal-pill{display:inline-flex;align-items:center;gap:8px}
.medal-pill.is-diamond{color:#8b5cf6}.medal-pill.is-gold{color:#d4a017}.medal-pill.is-silver{color:#94a3b8}.medal-pill.is-bronze{color:#b45309}.medal-pill.is-base{opacity:.85}
.sparkline-card{border:1px solid var(--border-soft,rgba(255,255,255,.08));border-radius:12px;padding:10px 12px;background:rgba(127,127,127,.04)}
.sparkline-bars{display:grid;grid-template-columns:repeat(7,1fr);gap:8px;align-items:end;height:86px}
.sparkline-bar-wrap{display:flex;flex-direction:column;align-items:center;justify-content:end;gap:6px;min-width:0}
.sparkline-bar{width:100%;max-width:18px;min-height:8px;border-radius:999px;background:rgba(13,110,253,.35);border:1px solid rgba(13,110,253,.18)}
.sparkline-bar.is-perfect{background:rgba(25,135,84,.55);border-color:rgba(25,135,84,.28)}
.sparkline-bar-wrap span{font-size:.72rem;opacity:.75;white-space:nowrap}


@media (max-width: 767.98px){.dashboard-insights-grid,.gamification-grid{grid-template-columns:1fr 1fr}.tasks-dashboard-toolbar{justify-content:flex-start;width:100%}.tasks-month-selector{grid-template-columns:1fr 90px 40px;width:100%}.sparkline-bars{gap:6px}}

@endonce


.tasks-strip{display:flex;flex-direction:column;gap:10px;padding:12px 14px;border:1px solid var(--border-soft,rgba(255,255,255,.08));border-radius:14px;margin-bottom:8px}
.tasks-strip-title{display:flex;align-items:center;justify-content:space-between;gap:12px}
.tasks-strip-badges{display:flex;flex-wrap:wrap;gap:8px}
.tasks-progress--slim{height:8px;margin-bottom:0}
@media (max-width: 767.98px){.tasks-strip{padding:10px 12px}.tasks-strip-badges{gap:6px}.tasks-strip .task-status-pill{font-size:.74rem}}

</style>
