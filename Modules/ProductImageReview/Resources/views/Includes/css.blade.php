<style>
.pir-page { --pir-border: rgba(148,163,184,.25); }
.pir-header { display:grid; grid-template-columns:minmax(0,1fr) minmax(280px,420px); gap:2rem; align-items:end; padding:1.35rem; border:1px solid var(--pir-border); border-radius:8px; background:var(--card-bg,#fff); }
.pir-header h1 { margin:.15rem 0 .35rem; font-size:1.55rem; }
.pir-header p { margin:0; opacity:.72; }
.pir-eyebrow { color:var(--bs-primary); font-size:.75rem; font-weight:800; letter-spacing:.1em; text-transform:uppercase; }
.pir-filter label { display:block; margin-bottom:.4rem; font-size:.8rem; font-weight:700; }
.pir-status,.pir-loader { display:flex; justify-content:center; align-items:center; gap:.65rem; min-height:86px; color:var(--bs-secondary-color); }
.pir-list { display:grid; gap:1rem; }
.pir-product { display:grid; grid-template-columns:minmax(230px,320px) minmax(0,1fr); gap:1.1rem; padding:1rem; border:1px solid var(--pir-border); border-radius:8px; background:var(--card-bg,#fff); content-visibility:auto; contain-intrinsic-size:230px; }
.pir-product-meta { border-right:1px solid var(--pir-border); padding-right:1rem; }
.pir-reference { display:inline-flex; align-items:center; gap:.45rem; font-size:1rem; font-weight:800; word-break:break-word; }
.pir-name { margin-top:.55rem; line-height:1.4; }
.pir-id { margin-top:.55rem; font-size:.75rem; opacity:.65; }
.pir-gallery { display:flex; gap:.75rem; overflow-x:auto; padding:.1rem .1rem .55rem; scroll-snap-type:x proximity; }
.pir-image-link { position:relative; flex:0 0 150px; height:150px; display:grid; place-items:center; overflow:hidden; border:1px solid var(--pir-border); border-radius:7px; background:#fff; scroll-snap-align:start; }
.pir-image-link img { width:100%; height:100%; object-fit:contain; }
.pir-cover { position:absolute; top:.4rem; left:.4rem; padding:.18rem .42rem; border-radius:4px; color:#fff; background:rgba(13,110,253,.9); font-size:.68rem; font-weight:800; text-transform:uppercase; }
.pir-image-number { position:absolute; right:.35rem; bottom:.35rem; padding:.12rem .35rem; border-radius:4px; color:#fff; background:rgba(15,23,42,.75); font-size:.68rem; }
.pir-no-images { min-height:150px; display:grid; place-items:center; width:100%; border:1px dashed var(--pir-border); border-radius:7px; color:var(--bs-secondary-color); }
.pir-sentinel { height:2px; }
body.theme-dark .pir-header,body.theme-dark .pir-product,body[data-theme="dark"] .pir-header,body[data-theme="dark"] .pir-product { background:rgba(31,41,55,.92); }
@media(max-width:900px){.pir-header,.pir-product{grid-template-columns:1fr}.pir-product-meta{border-right:0;border-bottom:1px solid var(--pir-border);padding:0 0 1rem}}
</style>
