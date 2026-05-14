<style>
:root{
    --sda-bg:#f8fafc;
    --sda-card:#ffffff;
    --sda-border:#e2e8f0;
    --sda-text:#0f172a;
    --sda-muted:#64748b;
    --sda-blue:#2563eb;
    --sda-green:#16a34a;
    --sda-red:#dc2626;
    --sda-yellow:#d97706;
    --sda-soft-blue:rgba(37,99,235,.08);
    --sda-soft-green:rgba(22,163,74,.08);
    --sda-soft-red:rgba(220,38,38,.08);
    --sda-radius:0;
}
.streamdeck-access-shell{display:grid;gap:1rem;width:100%;max-width:none;margin:0;padding:0;color:var(--sda-text)}
.streamdeck-access-shell--form{max-width:860px}
.streamdeck-access-card{background:var(--sda-card);border:1px solid var(--sda-border);border-radius:var(--sda-radius);box-shadow:0 1px 2px rgba(15,23,42,.04)}
.streamdeck-access-alert{display:grid;gap:.35rem;padding:.85rem 1rem;border-radius:var(--sda-radius);border:1px solid rgba(22,163,74,.25);background:var(--sda-soft-green);color:var(--sda-green)}
.streamdeck-access-alert--warning{border-color:rgba(217,119,6,.28);background:rgba(217,119,6,.08);color:var(--sda-yellow)}
.streamdeck-access-alert--token{border-color:rgba(37,99,235,.24);background:var(--sda-soft-blue);color:var(--sda-text)}
.sda-dashboard-grid{display:grid;grid-template-columns:minmax(0,1fr) 360px;gap:1rem;align-items:stretch}
.sda-toolbar-card{padding:1rem;display:grid;gap:1rem}
.sda-toolbar-header,.sda-show-header{display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap}
.sda-title-wrap{display:flex;gap:.8rem;align-items:flex-start}.sda-title-icon,.sda-form-icon{width:38px;height:38px;border-radius:var(--sda-radius);display:inline-flex;align-items:center;justify-content:center;background:var(--sda-soft-blue);color:var(--sda-blue);border:1px solid rgba(37,99,235,.16)}
.sda-title,.sda-show-title{margin:0;color:var(--sda-text);font-size:1.2rem}.sda-subtitle,.sda-show-subtitle,.sda-help-text{color:var(--sda-muted);font-size:.85rem}.sda-subtitle{margin:.25rem 0 0}
.sda-filters{display:grid;grid-template-columns:minmax(0,1fr) 190px 150px auto;gap:.75rem;align-items:end}.sda-filter-actions{display:flex;gap:.4rem}.streamdeck-access-label{display:block;margin-bottom:.35rem;font-size:.74rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--sda-muted)}
.streamdeck-access-input,.streamdeck-access-select,.streamdeck-access-textarea{width:100%;border:1px solid var(--sda-border);border-radius:var(--sda-radius);background:#fff;color:var(--sda-text);padding:.62rem .7rem;font:inherit;outline:none;transition:border-color .2s ease,box-shadow .2s ease}.streamdeck-access-input:focus,.streamdeck-access-select:focus,.streamdeck-access-textarea:focus{border-color:rgba(37,99,235,.55);box-shadow:0 0 0 3px rgba(37,99,235,.1)}
.streamdeck-access-textarea{resize:vertical}.sda-code-textarea,pre{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;font-size:.82rem}
.streamdeck-access-shell .prm-dashboard-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:0}
.streamdeck-access-shell .prm-dashboard-metric{position:relative;overflow:hidden;border-radius:0;padding:16px;min-height:104px;border:1px solid rgba(148,163,184,.25);background:linear-gradient(135deg,rgba(255,255,255,.96),rgba(248,250,252,.86));box-shadow:0 8px 24px rgba(15,23,42,.08);display:flex;justify-content:space-between;gap:14px;align-items:center}
.streamdeck-access-shell .prm-dashboard-metric__label{font-size:12px;text-transform:uppercase;color:#64748b;font-weight:800;letter-spacing:.04em}
.streamdeck-access-shell .prm-dashboard-metric__value{font-size:30px;line-height:1;font-weight:900;color:#0f172a;margin-top:6px}
.streamdeck-access-shell .prm-dashboard-metric__icon{width:46px;height:46px;border-radius:0;display:flex;align-items:center;justify-content:center;background:color-mix(in srgb,var(--metric-color,#2563eb) 16%,transparent);color:var(--metric-color,#2563eb);font-size:20px;border:1px solid color-mix(in srgb,var(--metric-color,#2563eb) 28%,transparent);flex:0 0 46px}
.streamdeck-access-shell .prm-dashboard-metric.roles{--metric-color:#2563eb}
.streamdeck-access-shell .prm-dashboard-metric.permissions{--metric-color:#7c3aed}
.streamdeck-access-shell .prm-dashboard-metric.critical{--metric-color:#dc2626}
.streamdeck-access-shell .prm-dashboard-metric.users{--metric-color:#16a34a}
.streamdeck-access-table-wrap{width:100%;overflow-x:auto}.streamdeck-access-table{width:100%;border-collapse:separate;border-spacing:0;min-width:980px}.streamdeck-access-table th{padding:.8rem .75rem;text-align:left;font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:var(--sda-muted);border-bottom:1px solid var(--sda-border);background:#f8fafc}.streamdeck-access-table td{padding:.8rem .75rem;border-bottom:1px solid var(--sda-border);vertical-align:top}.streamdeck-access-table tr:last-child td{border-bottom:0}.sda-table-title{display:grid;gap:.15rem}.sda-table-title strong{color:var(--sda-text)}.sda-table-title span,.sda-table-target small{color:var(--sda-muted);font-size:.82rem}.sda-table-target{max-width:360px}.sda-table-target span{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.sda-badge,.sda-status,.sda-log-status{display:inline-flex;align-items:center;border-radius:999px;padding:.2rem .5rem;font-size:.72rem;font-weight:700;border:1px solid var(--sda-border);margin:.1rem .2rem .1rem 0}.sda-badge--task{color:var(--sda-blue);background:var(--sda-soft-blue);border-color:rgba(37,99,235,.18)}.sda-badge--redirect{color:var(--sda-yellow);background:rgba(217,119,6,.08);border-color:rgba(217,119,6,.18)}.sda-status--enabled,.sda-log-status--completed{color:var(--sda-green);background:var(--sda-soft-green);border-color:rgba(22,163,74,.18)}.sda-status--disabled,.sda-log-status--failed,.sda-log-status--rejected,.sda-log-status--error{color:var(--sda-red);background:var(--sda-soft-red);border-color:rgba(220,38,38,.18)}
.sda-token-hint{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;color:var(--sda-muted)}.streamdeck-access-actions{display:flex;gap:.35rem;align-items:center;flex-wrap:wrap}.streamdeck-access-actions--center{justify-content:center}.lsg-action-form{display:inline-flex;margin:0}.sda-empty-state{display:grid;gap:.25rem;justify-items:center;text-align:center;padding:1.2rem;color:var(--sda-muted)}.sda-empty-state strong{color:var(--sda-text)}
.sda-form-card{padding:1rem;display:grid;gap:1rem}.sda-form-header{display:flex;gap:.75rem;align-items:flex-start;padding-bottom:.85rem;border-bottom:1px solid var(--sda-border)}.sda-form-header strong{display:block;color:var(--sda-text)}.sda-form-header span:not(.sda-form-icon){display:block;color:var(--sda-muted);font-size:.84rem;margin-top:.1rem}.streamdeck-access-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}.streamdeck-access-grid-1{grid-column:span 2}.sda-field-error{margin-top:.35rem;color:var(--sda-red);font-size:.8rem}.sda-checkbox-field{display:flex;align-items:end}.sda-checkbox{display:flex;gap:.5rem;align-items:center;color:var(--sda-text);font-weight:600}.sda-form-actions{display:flex;justify-content:flex-end;gap:.5rem;border-top:1px solid var(--sda-border);padding-top:1rem}
.sda-show-card{padding:1rem}.sda-show-grid{margin-top:1rem}.streamdeck-access-meta{display:grid;gap:.35rem;padding:.9rem;border:1px solid var(--sda-border);border-radius:var(--sda-radius);background:#fff}.streamdeck-access-meta strong{font-size:.74rem;text-transform:uppercase;letter-spacing:.06em;color:var(--sda-muted)}.streamdeck-access-meta pre,.sda-log-summary pre{margin:0;white-space:pre-wrap;word-break:break-word;background:#f8fafc;border:1px solid var(--sda-border);border-radius:var(--sda-radius);padding:.65rem;max-height:260px;overflow:auto}.sda-copy-row{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:.5rem;align-items:center;margin-top:.5rem}.sda-copy-row--compact{margin-top:0}.sda-card-header{display:flex;justify-content:space-between;gap:1rem;padding:.9rem 1rem;border-bottom:1px solid var(--sda-border)}.sda-card-header span{color:var(--sda-muted)}.sda-log-summary{max-width:480px}
@media (max-width:1000px){.sda-dashboard-grid{grid-template-columns:1fr}.sda-filters{grid-template-columns:1fr 1fr}.sda-filter-actions{grid-column:span 2}.streamdeck-access-grid{grid-template-columns:1fr}.streamdeck-access-grid-1{grid-column:span 1}}
@media (max-width:640px){.sda-filters{grid-template-columns:1fr}.sda-filter-actions{grid-column:span 1}.sda-copy-row{grid-template-columns:1fr}.sda-form-actions{justify-content:stretch;flex-direction:column}.sda-form-actions .lsg-action-btn{justify-content:center}}
</style>
