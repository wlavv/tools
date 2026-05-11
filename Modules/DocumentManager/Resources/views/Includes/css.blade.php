<style>
    .dms-shell{
        --dms-radius:5px;
        --dms-bg:var(--lsg-card-bg,rgba(17,24,39,.88));
        --dms-bg-soft:var(--lsg-card-bg-soft,rgba(30,41,59,.72));
        --dms-input:var(--bs-body-bg,rgba(15,23,42,.78));
        --dms-text:var(--bs-body-color,#f8fafc);
        --dms-muted:var(--bs-secondary-color,#a7b3c2);
        --dms-border:var(--bs-border-color,rgba(226,232,240,.14));
        --dms-accent:#d4a017;
        --dms-blue:#60a5fa;
        --dms-green:#22c55e;
        --dms-red:#ef4444;
        --dms-shadow:0 14px 32px rgba(0,0,0,.18);
        display:flex;
        flex-direction:column;
        gap:14px;
        color:var(--dms-text);
    }

    body.theme-light .dms-shell,
    body[data-theme="light"] .dms-shell,
    html[data-theme="light"] .dms-shell{
        --dms-bg:#ffffff;
        --dms-bg-soft:#f8fafc;
        --dms-input:#ffffff;
        --dms-text:#18212b;
        --dms-muted:#64748b;
        --dms-border:rgba(15,23,42,.12);
        --dms-shadow:0 14px 32px rgba(15,23,42,.08);
    }

    .dms-card,
    .dms-hero,
    .dms-document-hero,
    .dms-breadcrumbs,
    .dms-nav,
    .dms-toolbar,
    .dms-panel,
    .dms-upload-zone,
    .dms-preview,
    .dms-note{
        border:1px solid var(--dms-border);
        border-radius:var(--dms-radius);
        background:linear-gradient(180deg,var(--dms-bg) 0%,var(--dms-bg-soft) 100%);
        box-shadow:var(--dms-shadow);
        color:var(--dms-text);
    }

    .dms-card{padding:16px;}
    .dms-subsection{margin-top:16px;padding-top:16px;border-top:1px solid var(--dms-border)}
    .dms-hero,.dms-document-hero,.dms-breadcrumbs,.dms-toolbar{padding:18px;display:flex;justify-content:space-between;gap:16px;align-items:flex-start}
    .dms-hero--compact{padding:14px 16px}
    .dms-hero h2,.dms-document-hero h2,.dms-breadcrumbs h1,.dms-card h3{margin:0;color:var(--dms-text);font-weight:900;letter-spacing:0;line-height:1.1}
    .dms-hero h2,.dms-document-hero h2{font-size:28px}
    .dms-breadcrumbs h1{font-size:22px}
    .dms-hero p,.dms-document-hero p{margin:8px 0 0;color:var(--dms-muted);max-width:820px;line-height:1.6}
    .dms-eyebrow{display:block;margin-bottom:6px;font-size:11px;text-transform:uppercase;letter-spacing:.08em;font-weight:900;color:var(--dms-accent)}
    .dms-muted{display:block;color:var(--dms-muted);font-size:.78rem}
    .dms-side-description{margin:0 0 10px;color:var(--dms-muted);line-height:1.55}

    .dms-breadcrumbs ol{display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin:0;padding:0;list-style:none;color:var(--dms-muted);font-size:12px;font-weight:700}
    .dms-breadcrumbs li:not(:last-child)::after{content:'/';margin-left:8px;color:var(--dms-accent)}
    .dms-breadcrumbs a{color:var(--dms-muted);text-decoration:none}
    .dms-breadcrumbs a:hover{color:var(--dms-text)}

    .dms-nav{display:flex;gap:7px;padding:7px;overflow-x:auto}
    .dms-nav__item{display:inline-flex;align-items:center;gap:7px;padding:9px 11px;border-radius:var(--dms-radius);border:1px solid transparent;color:var(--dms-muted);font-weight:800;text-decoration:none;white-space:nowrap}
    .dms-nav__item:hover,.dms-nav__item.is-active{background:rgba(212,160,23,.12);border-color:rgba(212,160,23,.28);color:var(--dms-text);text-decoration:none}

    .dms-actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
    .dms-actions--right{justify-content:flex-end;margin-top:14px}
    .dms-actions .btn{border-radius:var(--dms-radius)!important;display:inline-flex;align-items:center;gap:7px;font-weight:800}
    .dms-form-actions{display:flex;gap:8px;align-items:center;justify-content:flex-end;flex-wrap:wrap}
    .dms-form-actions .btn{border-radius:var(--dms-radius)!important;display:inline-flex;align-items:center;gap:7px;font-weight:800}
    .dms-document-ops{display:flex;gap:7px;align-items:center;flex-wrap:wrap;margin-top:12px}
    .dms-document-ops form{margin:0}
    .dms-document-ops .btn{border-radius:var(--dms-radius)!important;display:inline-flex;align-items:center;gap:6px;font-weight:850}
    .dms-workflow-form select{border:1px solid var(--dms-border);border-radius:var(--dms-radius);background:var(--dms-input);color:var(--dms-text);min-height:31px;padding:4px 8px;font-size:.78rem;font-weight:850;text-transform:capitalize}

    .dms-grid{display:grid;gap:14px}
    .dms-grid--2{grid-template-columns:repeat(2,minmax(0,1fr))}
    .dms-grid--3{grid-template-columns:repeat(3,minmax(0,1fr))}
    .dms-grid--4{grid-template-columns:repeat(4,minmax(0,1fr))}
    .dms-grid--5{grid-template-columns:repeat(5,minmax(0,1fr))}
    .dms-grid--6{grid-template-columns:repeat(6,minmax(0,1fr))}
    .dms-detail-layout{display:grid;grid-template-columns:minmax(0,1.4fr) minmax(320px,.6fr);gap:14px}
    .dms-document-workspace{display:grid;grid-template-columns:minmax(0,1.72fr) minmax(340px,.38fr);gap:14px;align-items:start}
    .dms-document-edit-workspace{display:grid;grid-template-columns:minmax(0,1.35fr) minmax(420px,.65fr);gap:14px;align-items:start}
    .dms-document-side{display:flex;flex-direction:column;gap:14px}
    .dms-document-sheet-card,.dms-document-edit-preview,.dms-document-edit-form{min-width:0}
    .dms-document-edit-preview,.dms-document-edit-form{position:sticky;top:96px}
    .dms-document-edit-form{max-height:calc(100vh - 118px);overflow:auto}

    .dms-kpi span,.dms-panel span{display:block;font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:var(--dms-muted);font-weight:900}
    .dms-kpi strong{display:block;margin-top:6px;font-size:28px;line-height:1;color:var(--dms-text);font-weight:900}
    .dms-dashboard-kpi-line{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}
    .dms-dashboard-kpi-line .dms-kpi{padding:12px 14px}
    .dms-dashboard-kpi-line .dms-kpi strong{font-size:24px}
    .dms-dashboard-panel-line,.dms-counter-line{display:grid;gap:8px;align-items:stretch}
    .dms-dashboard-panel-line{grid-template-columns:repeat(8,minmax(135px,1fr))}
    .dms-counter-line--workflow{grid-template-columns:repeat(8,minmax(118px,1fr))}
    .dms-counter-line--ai{grid-template-columns:repeat(5,minmax(140px,1fr))}
    .dms-dashboard-panel-line .dms-panel,.dms-counter-line .dms-panel{min-width:0;padding:10px;gap:9px}
    .dms-dashboard-panel-line .dms-panel__icon,.dms-counter-line .dms-panel__icon{width:34px;height:34px;flex:0 0 34px}
    .dms-dashboard-panel-line .dms-panel strong,.dms-counter-line .dms-panel strong{font-size:21px;line-height:1}
    .dms-dashboard-panel-line .dms-panel span,.dms-counter-line .dms-panel span{font-size:10px;line-height:1.15}
    .dms-panel{padding:14px;display:flex;gap:12px;align-items:center}
    .dms-panel__icon{width:40px;height:40px;border-radius:var(--dms-radius);display:flex;align-items:center;justify-content:center;background:rgba(96,165,250,.12);color:var(--dms-blue)}
    .dms-panel strong{font-size:24px;color:var(--dms-text)}
    .dms-panel--warning .dms-panel__icon{background:rgba(245,158,11,.14);color:#f59e0b}
    .dms-panel--danger .dms-panel__icon{background:rgba(239,68,68,.12);color:var(--dms-red)}
    .dms-panel--primary .dms-panel__icon{background:rgba(96,165,250,.12);color:var(--dms-blue)}

    .dms-card__head{display:flex;justify-content:space-between;gap:12px;align-items:flex-start;margin-bottom:14px}
    .dms-pipeline{display:flex;gap:7px;flex-wrap:wrap}
    .dms-pipeline span,.dms-badge,.dms-chip-row span{display:inline-flex;align-items:center;gap:5px;border-radius:var(--dms-radius);border:1px solid rgba(212,160,23,.28);background:rgba(212,160,23,.12);color:var(--dms-text);padding:5px 8px;font-size:.74rem;font-weight:850;text-transform:capitalize}
    .dms-badge--soft{border-color:var(--dms-border);background:var(--dms-input);color:var(--dms-muted)}
    .dms-chip-row{display:flex;gap:7px;flex-wrap:wrap;margin-top:12px}
    .dms-tag-list{display:flex;gap:6px;flex-wrap:wrap;margin-top:12px}
    .dms-tag-list span{display:inline-flex;align-items:center;gap:5px;border:1px solid color-mix(in srgb,var(--tag-color,#60a5fa) 42%,transparent);border-radius:var(--dms-radius);background:color-mix(in srgb,var(--tag-color,#60a5fa) 14%,transparent);color:var(--dms-text);padding:5px 8px;font-size:.74rem;font-weight:850}
    .dms-tag-list i{color:var(--tag-color,#60a5fa)}

    .dms-table{width:100%;border-collapse:separate;border-spacing:0;color:var(--dms-text)}
    .dms-table th{font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:var(--dms-muted);background:var(--dms-bg-soft);border-bottom:1px solid var(--dms-border);padding:10px}
    .dms-table td{padding:10px;border-bottom:1px solid var(--dms-border);vertical-align:middle;color:var(--dms-text)}
    .dms-table a{color:var(--dms-text);font-weight:800;text-decoration:none}
    .dms-table a:hover{color:var(--dms-accent)}

    .dms-toolbar{align-items:center}
    .dms-filter-form,.dms-searchbar{display:flex;gap:8px;align-items:center;flex-wrap:wrap;width:100%}
    .dms-filter-form input,.dms-filter-form select,.dms-searchbar input,.dms-field input,.dms-field select,.dms-field textarea{
        border:1px solid var(--dms-border)!important;
        border-radius:var(--dms-radius)!important;
        background:var(--dms-input)!important;
        color:var(--dms-text)!important;
        min-height:40px;
        padding:8px 10px;
        width:100%;
    }
    .dms-filter-form input{max-width:420px}
    .dms-filter-form select{max-width:220px}
    .dms-searchbar input{flex:1 1 420px}

    .dms-form-grid{display:grid;grid-template-columns:minmax(0,1fr) 360px;gap:14px;align-items:start}
    .dms-form-grid--single{grid-template-columns:minmax(0,1fr)}
    .dms-quick-upload{display:flex;flex-direction:column;gap:12px}
    .dms-quick-upload__grid{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:12px;align-items:end}
    .dms-collapsible-upload{padding:0;overflow:hidden}
    .dms-collapsible-upload summary{list-style:none;display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 16px;cursor:pointer;color:var(--dms-text)}
    .dms-collapsible-upload summary::-webkit-details-marker{display:none}
    .dms-collapsible-upload summary strong{display:block;color:var(--dms-text);font-size:1rem}
    .dms-collapsible-upload summary>i{width:36px;height:36px;border-radius:var(--dms-radius);display:flex;align-items:center;justify-content:center;background:rgba(212,160,23,.12);color:var(--dms-accent)}
    .dms-collapsible-upload[open]{padding-bottom:16px}
    .dms-collapsible-upload[open] summary{border-bottom:1px solid var(--dms-border);margin-bottom:16px}
    .dms-collapsible-upload .dms-quick-upload,.dms-collapsible-upload .dms-upload-zone{margin:0 16px}
    .dms-form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    .dms-field{display:flex;flex-direction:column;gap:5px;margin-bottom:12px}
    .dms-field label{font-size:.78rem;font-weight:900;color:var(--dms-text)}
    .dms-field select[multiple]{min-height:112px}
    .dms-error{color:var(--dms-red);font-size:.78rem;font-weight:800}
    .dms-form-section{border:1px solid var(--dms-border);border-radius:var(--dms-radius);background:var(--dms-input);margin-top:12px;overflow:hidden}
    .dms-form-section summary{list-style:none;display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 12px;cursor:pointer;color:var(--dms-text);font-weight:900}
    .dms-form-section summary::-webkit-details-marker{display:none}
    .dms-form-section summary i{color:var(--dms-accent);font-size:.78rem;transition:transform .15s ease}
    .dms-form-section[open] summary{border-bottom:1px solid var(--dms-border);margin-bottom:12px}
    .dms-form-section[open] summary i{transform:rotate(180deg)}
    .dms-form-section>.dms-form-row,.dms-form-section>.dms-field{margin-left:12px;margin-right:12px}
    .dms-sticky{position:sticky;top:110px}
    .dms-check{display:flex;align-items:center;gap:8px;color:var(--dms-text);font-weight:800}
    .dms-check input{width:auto!important;min-height:auto}
    .dms-color-swatch{width:28px;height:20px;border-radius:var(--dms-radius);display:inline-block;border:1px solid var(--dms-border)}

    .dms-upload-zone{min-height:220px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:22px;cursor:pointer;border-style:dashed}
    .dms-upload-zone--compact{min-height:112px;padding:16px}
    .dms-upload-zone--compact i{font-size:26px;margin-bottom:6px}
    .dms-upload-zone i{font-size:34px;color:var(--dms-accent);margin-bottom:10px}
    .dms-upload-zone strong{font-size:1rem;color:var(--dms-text)}
    .dms-upload-zone span{color:var(--dms-muted);font-size:.84rem;max-width:280px}
    .dms-upload-zone input{display:none}
    .dms-upload-zone.is-dragover{border-color:var(--dms-accent);background:rgba(212,160,23,.12)}

    .dms-preview{min-height:260px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;gap:8px;overflow:hidden}
    .dms-preview--large{min-height:72vh}
    .dms-document-sheet{min-height:calc(100vh - 220px);align-items:stretch;justify-content:flex-start;background:var(--dms-input)}
    .dms-document-sheet--edit{min-height:calc(100vh - 210px)}
    .dms-preview i{font-size:42px;color:var(--dms-accent)}
    .dms-preview strong{color:var(--dms-text)}
    .dms-preview span{color:var(--dms-muted)}
    .dms-preview-link{display:flex;align-items:center;justify-content:center;width:100%;height:100%;text-decoration:none}
    .dms-preview-image{display:block;max-width:100%;max-height:520px;object-fit:contain;border-radius:var(--dms-radius);box-shadow:0 22px 42px rgba(0,0,0,.22)}
    .dms-preview-image--large{max-height:72vh}
    .dms-preview-image--sheet{width:100%;max-height:none;object-fit:contain}
    .dms-preview-video{width:100%;max-height:520px;border-radius:var(--dms-radius);background:#000}
    .dms-preview-video--large{max-height:72vh}
    .dms-preview-video--sheet{max-height:none;min-height:calc(100vh - 250px)}
    .dms-preview-frame{width:100%;min-height:520px;border:0;border-radius:var(--dms-radius);background:#fff}
    .dms-preview-frame--large{min-height:72vh}
    .dms-preview-frame--sheet{min-height:calc(140vh - 250px)}
    .dms-readiness{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-top:12px}
    .dms-readiness div,.dms-intel span,.dms-check-list div{border:1px solid var(--dms-border);border-radius:var(--dms-radius);background:var(--dms-input);color:var(--dms-muted);padding:8px;font-size:.78rem;font-weight:850}
    .dms-readiness .is-ok,.dms-intel .is-ok,.dms-check-list .is-ok{border-color:rgba(34,197,94,.35);background:rgba(34,197,94,.12);color:var(--dms-green)}
    .dms-check-list .is-fail{border-color:rgba(239,68,68,.35);background:rgba(239,68,68,.10);color:var(--dms-red)}
    .dms-intel{display:flex;gap:5px;flex-wrap:wrap}

    .dms-kv-list{display:flex;flex-direction:column;gap:8px}
    .dms-kv-list div{display:flex;justify-content:space-between;gap:10px;border-bottom:1px solid var(--dms-border);padding-bottom:8px}
    .dms-kv-list span{color:var(--dms-muted);font-size:.8rem;font-weight:800}
    .dms-kv-list strong{color:var(--dms-text);font-size:.86rem;text-align:right;word-break:break-word}
    .dms-kv-tile{border:1px solid var(--dms-border);border-radius:var(--dms-radius);background:var(--dms-input);padding:12px}
    .dms-kv-tile span{display:block;color:var(--dms-muted);font-size:.72rem;text-transform:uppercase;font-weight:900}
    .dms-kv-tile strong{display:block;margin-top:5px;color:var(--dms-text);font-size:1rem;word-break:break-word}
    .dms-payment-status{border-width:2px}
    .dms-payment-status strong{text-transform:capitalize;font-size:1.15rem}
    .dms-payment-status--paid{border-color:rgba(34,197,94,.55);background:rgba(34,197,94,.13)}
    .dms-payment-status--pending,.dms-payment-status--partial{border-color:rgba(245,158,11,.6);background:rgba(245,158,11,.14)}
    .dms-payment-status--overdue{border-color:rgba(239,68,68,.62);background:rgba(239,68,68,.13)}
    .dms-payment-status--cancelled{border-color:rgba(148,163,184,.45);background:rgba(148,163,184,.12)}

    .dms-category-tree{display:flex;flex-direction:column;gap:8px}
    .dms-tree-node{border:1px solid var(--dms-border);border-radius:var(--dms-radius);background:var(--dms-input);overflow:hidden}
    .dms-tree-node summary{list-style:none}
    .dms-tree-node summary::-webkit-details-marker{display:none}
    .dms-tree-row{display:grid;grid-template-columns:28px 34px minmax(0,1fr) minmax(120px,.35fr) auto;gap:10px;align-items:center;padding:10px 12px 10px calc(12px + (var(--tree-level,0) * 18px));cursor:pointer}
    .dms-tree-row__toggle,.dms-tree-row__icon{display:flex;align-items:center;justify-content:center}
    .dms-tree-row__toggle{color:var(--dms-muted);font-size:.75rem}
    .dms-tree-row__main strong{display:block;color:var(--dms-text)}
    .dms-tree-row__main small,.dms-tree-row__meta{color:var(--dms-muted);font-size:.78rem;font-weight:800}
    .dms-tree-node[open]>.dms-tree-row .dms-tree-row__toggle i.fa-chevron-right{transform:rotate(90deg)}
    .dms-tree-children{display:flex;flex-direction:column;gap:7px;padding:0 8px 8px 8px}

    .dms-timeline{display:flex;flex-direction:column;gap:8px}
    .dms-timeline div,.dms-note,.dms-empty{padding:10px;border-radius:var(--dms-radius);border:1px solid var(--dms-border);background:var(--dms-input)}
    .dms-timeline span{display:block;color:var(--dms-muted);font-size:.75rem}
    .dms-note p{margin:6px 0 0;color:var(--dms-muted)}
    .dms-empty{color:var(--dms-muted);text-align:center}
    .dms-collapsible-panel{padding:0;overflow:hidden}
    .dms-collapsible-panel summary{list-style:none;display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 16px;cursor:pointer;color:var(--dms-text)}
    .dms-collapsible-panel summary::-webkit-details-marker{display:none}
    .dms-collapsible-panel summary span{display:inline-flex;align-items:center;gap:8px;font-weight:900}
    .dms-collapsible-panel summary i{color:var(--dms-accent)}
    .dms-collapsible-panel summary strong{min-width:34px;text-align:center;border:1px solid var(--dms-border);border-radius:var(--dms-radius);background:var(--dms-input);padding:4px 8px;color:var(--dms-text)}
    .dms-collapsible-panel[open] summary{border-bottom:1px solid var(--dms-border);margin-bottom:12px}
    .dms-collapsible-panel>.dms-table,.dms-collapsible-panel>.dms-timeline,.dms-collapsible-panel>.dms-note,.dms-collapsible-panel>.dms-empty{margin:0 16px 16px}
    .dms-collapsible-panel>.dms-kv-list{margin:0 16px 16px}
    .dms-alert{border-radius:var(--dms-radius);border:1px solid var(--dms-border);padding:10px 12px;font-weight:800}
    .dms-alert--success{background:rgba(34,197,94,.12);border-color:rgba(34,197,94,.28);color:var(--dms-green)}
    .dms-alert--danger{background:rgba(239,68,68,.12);border-color:rgba(239,68,68,.28);color:#fca5a5}
    .dms-alert--warning{background:rgba(245,158,11,.12);border-color:rgba(245,158,11,.28);color:#f59e0b}
    .dms-log{max-height:420px;overflow:auto;border-radius:var(--dms-radius);background:#0f172a;color:#d1e7ff;padding:12px;font-size:12px}
    .dms-pagination{margin-top:12px}

    .dms-workspace-list{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}
    .dms-workspace-card{display:grid;grid-template-columns:42px minmax(0,1fr) auto;gap:12px;align-items:center;padding:12px}
    .dms-workspace-card__icon{width:42px;height:42px;border:1px solid rgba(212,160,23,.28);border-radius:var(--dms-radius);display:flex;align-items:center;justify-content:center;background:rgba(212,160,23,.12);color:var(--dms-accent)}
    .dms-workspace-card__icon i{font-size:18px}
    .dms-workspace-card h3{font-size:1rem;margin:0 0 3px}
    .dms-workspace-card p{color:var(--dms-muted);margin:0;font-size:.82rem;line-height:1.35;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
    .dms-workspace-card__edit{justify-self:end}

    .dataTables_wrapper{color:var(--dms-text)}
    .dataTables_filter input,.dataTables_length select{border:1px solid var(--dms-border)!important;border-radius:var(--dms-radius)!important;background:var(--dms-input)!important;color:var(--dms-text)!important}

    @media(max-width:1200px){
        .dms-grid--5,.dms-grid--6{grid-template-columns:repeat(3,minmax(0,1fr))}
        .dms-grid--4{grid-template-columns:repeat(2,minmax(0,1fr))}
        .dms-workspace-list{grid-template-columns:repeat(2,minmax(0,1fr))}
        .dms-form-grid,.dms-detail-layout,.dms-document-workspace,.dms-document-edit-workspace,.dms-quick-upload__grid{grid-template-columns:1fr}
        .dms-sticky,.dms-document-edit-preview,.dms-document-edit-form{position:relative;top:auto}
        .dms-document-edit-form{max-height:none;overflow:visible}
    }
    @media(max-width:760px){
        .dms-hero,.dms-document-hero,.dms-breadcrumbs,.dms-toolbar{flex-direction:column}
        .dms-grid--2,.dms-grid--3,.dms-grid--4,.dms-grid--5,.dms-grid--6,.dms-form-row,.dms-readiness{grid-template-columns:1fr}
        .dms-dashboard-kpi-line{display:flex;overflow-x:auto;padding-bottom:4px}
        .dms-dashboard-kpi-line .dms-kpi{min-width:160px}
        .dms-dashboard-panel-line{display:flex;overflow-x:auto;padding-bottom:4px}
        .dms-dashboard-panel-line .dms-panel{min-width:165px}
        .dms-counter-line{display:flex;overflow-x:auto;padding-bottom:4px}
        .dms-counter-line .dms-panel{min-width:165px}
        .dms-workspace-list{grid-template-columns:1fr}
        .dms-filter-form input,.dms-filter-form select{max-width:none}
    }
</style>
