<style>
    .al-page { display: grid; gap: 1rem; }
    .al-panel {
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 16px;
        padding: 1rem;
        background: rgba(255,255,255,0.02);
    }
    .al-toolbar-form { display:grid; grid-template-columns:1.4fr 180px 180px auto auto; gap:.75rem; align-items:end; }
    .al-stats { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:.75rem; }
    .al-stat-value { font-size:1.6rem; font-weight:700; line-height:1; }
    .al-stat-label { opacity:.72; margin-top:.35rem; font-size:.9rem; }
    .al-grid-list { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:1rem; }
    .al-card {
        display:grid; gap:.75rem; border:1px solid rgba(255,255,255,0.08); border-radius:16px;
        padding:1rem; background:rgba(255,255,255,0.02); min-height:240px;
    }
    .al-card-preview {
        border-radius:12px; overflow:hidden; min-height:140px; display:flex; align-items:center; justify-content:center;
        background:rgba(255,255,255,0.03);
    }
    .al-card-preview img, .al-show-media img, .al-show-media video { width:100%; height:auto; display:block; object-fit:cover; max-height:360px; }
    .al-card-meta, .al-show-meta { display:grid; gap:.3rem; }
    .al-card-title { font-weight:600; margin:0; }
    .al-muted { opacity:.68; font-size:.92rem; }
    .al-badge { display:inline-flex; align-items:center; border:1px solid rgba(255,255,255,0.12); padding:.2rem .55rem; border-radius:999px; font-size:.8rem; }
    .al-actions { display:flex; flex-wrap:wrap; gap:.5rem; }
    .al-empty { text-align:center; opacity:.72; padding:2rem 1rem; }
    .al-form-grid, .al-show-grid { display:grid; grid-template-columns:1.1fr .9fr; gap:1rem; }
    .al-field-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.75rem; }
    .al-field { display:grid; gap:.4rem; }
    .al-field-full { grid-column:1 / -1; }
    .al-input, .al-select, .al-textarea {
        width:100%; border-radius:12px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04);
        color:inherit; padding:.8rem .9rem;
    }
    .al-textarea { min-height:140px; resize:vertical; }
    .al-checkline { display:flex; align-items:center; gap:.6rem; }
    .al-kv { display:grid; grid-template-columns:150px 1fr; gap:.75rem; padding:.5rem 0; border-bottom:1px solid rgba(255,255,255,0.06); }
    .al-kv:last-child { border-bottom:0; }
    .al-code { display:inline-block; padding:.2rem .45rem; border-radius:8px; background:rgba(255,255,255,0.06); font-family:monospace; font-size:.88rem; }

    @media (max-width: 1200px) {
        .al-grid-list { grid-template-columns:repeat(3,minmax(0,1fr)); }
        .al-toolbar-form { grid-template-columns:1fr 1fr 1fr; }
    }
    @media (max-width: 992px) {
        .al-stats, .al-grid-list, .al-form-grid, .al-show-grid, .al-field-grid, .al-toolbar-form { grid-template-columns:1fr 1fr; }
    }
    @media (max-width: 640px) {
        .al-stats, .al-grid-list, .al-form-grid, .al-show-grid, .al-field-grid, .al-toolbar-form { grid-template-columns:1fr; }
        .al-kv { grid-template-columns:1fr; }
        .al-actions { flex-direction:column; }
    }
</style>
