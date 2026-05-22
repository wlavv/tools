<style>
.calendar-shell{--calendar-radius:5px;--calendar-border:var(--border-soft,rgba(148,163,184,.18));--calendar-surface:var(--bg-card,var(--card-bg,#1f2937));--calendar-soft:var(--bg-card-2,var(--lsg-card-bg-soft,#243142));--calendar-text:var(--text-primary,#f8fafc);--calendar-muted:var(--text-muted,#94a3b8);display:grid;grid-template-columns:260px minmax(0,1fr);gap:16px;align-items:start}
.calendar-content{min-width:0}
.calendar-card{border-radius:var(--calendar-radius);border:1px solid var(--calendar-border)!important;background:var(--calendar-surface);color:var(--calendar-text);box-shadow:var(--lsg-card-shadow,none)}
.calendar-muted{font-size:.82rem;color:var(--calendar-muted)}
.calendar-table td,.calendar-table th{vertical-align:middle}
.calendar-form-compact .form-control,.calendar-form-compact .form-select{margin-bottom:.5rem}
.calendar-module-nav{position:sticky;top:16px;display:flex;flex-direction:column;gap:8px;padding:12px;border:1px solid var(--calendar-border);border-radius:var(--calendar-radius);background:var(--calendar-surface);color:var(--calendar-text)}
.calendar-module-nav__title{padding:4px 4px 10px;border-bottom:1px solid var(--calendar-border);margin-bottom:4px}
.calendar-module-nav__title strong{display:block;line-height:1.1}
.calendar-module-nav__title span{display:block;margin-top:4px;color:var(--calendar-muted);font-size:.78rem;line-height:1.25}
.calendar-module-nav a{display:flex;align-items:center;gap:9px;padding:10px 11px;border:1px solid transparent;border-radius:var(--calendar-radius);background:transparent;color:var(--calendar-text);text-decoration:none;font-weight:800}
.calendar-module-nav a i{width:18px;color:var(--calendar-muted)}
.calendar-module-nav a:hover,.calendar-module-nav a.is-active{border-color:var(--lsg-bo-btn-primary-border,rgba(96,165,250,.76));background:var(--calendar-soft);color:var(--calendar-text);text-decoration:none}
.calendar-module-nav a.is-active i{color:var(--lsg-bo-btn-primary-border,#60a5fa)}
.calendar-context-filter{display:flex;gap:8px;flex-wrap:wrap}
.calendar-context-filter a{display:inline-flex;align-items:center;gap:7px;padding:8px 11px;border:1px solid var(--calendar-border);border-radius:var(--calendar-radius);background:var(--calendar-soft);color:var(--calendar-text);text-decoration:none;font-weight:800}
.calendar-context-filter a.is-active{border-color:var(--lsg-bo-btn-primary-border,rgba(96,165,250,.76));color:var(--calendar-text)}
.calendar-context-dot{width:10px;height:10px;border-radius:50%;display:inline-block;box-shadow:0 0 0 1px var(--calendar-border)}
.calendar-color-field{min-height:38px;padding:4px}
.calendar-icon-choice{display:grid;grid-template-columns:repeat(auto-fit,minmax(96px,1fr));gap:8px}
.calendar-icon-choice label{display:flex;align-items:center;gap:8px;padding:9px;border:1px solid var(--calendar-border);border-radius:var(--calendar-radius);background:var(--calendar-soft);cursor:pointer}
.calendar-icon-choice input{margin:0}
.calendar-context-actions{display:flex;justify-content:flex-end;gap:8px;white-space:nowrap}
.calendar-context-actions .btn{min-width:38px}
.calendar-modal-content{--calendar-modal-bg:var(--bg-card,var(--card-bg,#1f2937));--calendar-modal-soft:var(--bg-card-2,var(--lsg-card-bg-soft,#243142));--calendar-modal-text:var(--text-primary,#f8fafc);--calendar-modal-muted:var(--text-muted,#94a3b8);--calendar-modal-border:var(--border-soft,rgba(148,163,184,.22));border:1px solid var(--calendar-modal-border)!important;border-radius:5px;background:var(--calendar-modal-bg)!important;background-color:var(--calendar-modal-bg)!important;color:var(--calendar-modal-text);box-shadow:var(--lsg-card-shadow,0 24px 70px rgba(0,0,0,.28))}
.calendar-modal-content .modal-header,.calendar-modal-content .modal-footer{border-color:var(--calendar-modal-border);background:var(--calendar-modal-soft)!important;background-color:var(--calendar-modal-soft)!important;color:var(--calendar-modal-text)}
.calendar-modal-content .modal-body{background:var(--calendar-modal-bg)!important;background-color:var(--calendar-modal-bg)!important;color:var(--calendar-modal-text)}
.calendar-modal-content .form-control,.calendar-modal-content .form-select{background-color:var(--calendar-modal-soft)!important;color:var(--calendar-modal-text)!important;border-color:var(--calendar-modal-border)!important}
.calendar-modal-content .calendar-icon-choice label{background:var(--calendar-modal-soft)!important;color:var(--calendar-modal-text)}
.calendar-modal-content .text-muted,.calendar-modal-content .calendar-muted{color:var(--calendar-modal-muted)!important}
.calendar-modal-form .form-label{font-weight:700}
@media (max-width:991.98px){.calendar-shell{grid-template-columns:1fr}.calendar-module-nav{position:relative;top:auto}}
</style>
