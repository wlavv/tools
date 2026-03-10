<style>
.pm-page{padding:1rem;}
.pm-header{display:flex;justify-content:space-between;align-items:flex-end;gap:1rem;margin-bottom:1rem;flex-wrap:wrap;}
.pm-title h1{margin:0;font-size:1.5rem;}
.pm-subtitle{opacity:.8;}
.pm-card{border:1px solid rgba(128,128,128,.25);border-radius:16px;padding:1rem;background:transparent;margin-bottom:1rem;}
.pm-grid{display:grid;gap:1rem;}
.pm-grid-2{grid-template-columns:repeat(2,minmax(0,1fr));}
.pm-grid-3{grid-template-columns:repeat(3,minmax(0,1fr));}
.pm-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1rem;margin-bottom:1rem;}
.pm-stat{border:1px solid rgba(128,128,128,.25);border-radius:14px;padding:1rem;background:transparent;}
.pm-filters{margin-bottom:1rem;}
.pm-project-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1rem;}
.pm-project-card{border:1px solid rgba(128,128,128,.25);border-radius:16px;padding:1rem;background:transparent;}
.pm-project-card h3{font-size:1.1rem;margin-bottom:.5rem;}
.pm-progress{height:10px;border-radius:999px;overflow:hidden;background:rgba(128,128,128,.2);}
.pm-progress-bar{height:100%;background:rgba(13,110,253,.8);}
.pm-table-wrap{overflow:auto;}
.pm-table{width:100%;border-collapse:collapse;}
.pm-table th,.pm-table td{padding:.75rem;border-bottom:1px solid rgba(128,128,128,.15);}
.pm-info-box{border:1px solid rgba(128,128,128,.2);border-radius:14px;padding:1rem;background:transparent;}
.pm-tabs{display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1rem;}
.pm-tab{border:1px solid rgba(128,128,128,.3);background:transparent;padding:.55rem .9rem;border-radius:999px;}
.pm-tab.is-active{outline:2px solid rgba(13,110,253,.4);}
.pm-tab-panel{display:none;}
.pm-tab-panel.is-active{display:block;}
.pm-top-actions{display:flex;gap:.75rem;align-items:center;margin-bottom:1rem;flex-wrap:wrap;}
.pm-top-actions form{margin:0;}
.pm-task{border:1px solid rgba(128,128,128,.2);border-radius:12px;padding:.75rem;margin-bottom:.75rem;}
.pm-task-head{display:flex;justify-content:space-between;gap:1rem;align-items:flex-start;flex-wrap:wrap;}
.pm-task-meta{font-size:.9rem;opacity:.8;}
.pm-task-children{margin-top:.75rem;padding-left:1rem;border-left:2px solid rgba(128,128,128,.2);}
.pm-pre{white-space:pre-wrap;word-break:break-word;margin:0;}
@media (max-width: 992px){
  .pm-grid-2,.pm-grid-3,.pm-stats{grid-template-columns:1fr;}
}
</style>
