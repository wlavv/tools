<style>
.ai-consensus-page {
    padding: 1rem;
}

.ai-consensus-shell {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
}

.aiConsensus-card,
.ai-card,
.ai-form-card,
.ai-toolbar,
.ai-page-header {
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(247, 249, 252, 0.98) 100%);
    border: 1px solid var(--border-soft, rgba(21, 32, 51, 0.12));
    border-radius: 5px;
    box-shadow: var(--shadow-soft, 0 8px 24px rgba(15, 23, 42, 0.05));
}

.ai-page-header {
    padding: 1rem 1.125rem;
}

.ai-breadcrumbs {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: .45rem;
    margin-bottom: .85rem;
    font-size: .8rem;
}

.ai-breadcrumbs__link,
.ai-breadcrumbs__current,
.ai-breadcrumbs__sep {
    color: var(--text-muted, #64748b);
}

.ai-breadcrumbs__link:hover {
    color: var(--text-primary, #18212b);
}

.ai-page-header__main {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    align-items: flex-start;
    flex-wrap: wrap;
}

.ai-page-header__identity {
    display: flex;
    align-items: flex-start;
    gap: .9rem;
    min-width: 0;
}

.ai-page-header__icon {
    width: 46px;
    height: 46px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 5px;
    border: 1px solid rgba(37,99,235,.15);
    background: rgba(37,99,235,.08);
    color: #2563eb;
    flex: 0 0 auto;
}

.ai-page-header__title {
    margin: 0;
    font-size: 1.2rem;
    line-height: 1.15;
    color: var(--text-primary, #18212b);
}

.ai-page-header__subtitle {
    margin: .35rem 0 0 0;
    max-width: 70ch;
    color: var(--text-muted, #64748b);
    line-height: 1.55;
}

.lsg-page-actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-end;
    gap: .625rem;
}

.lsg-action-form {
    display: inline-flex;
    margin: 0;
}

.lsg-action-btn {
    --lsg-bg: rgba(37,99,235,.08);
    --lsg-border: rgba(37,99,235,.16);
    --lsg-text: #2563eb;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .6rem;
    min-height: 38px;
    padding: .65rem .9rem;
    border-radius: 5px;
    border: 1px solid var(--lsg-border);
    background: var(--lsg-bg);
    color: var(--lsg-text);
    font-weight: 600;
    text-decoration: none;
    transition: all .2s ease;
}

.lsg-action-btn:hover {
    filter: brightness(.98);
    transform: translateY(-1px);
}

.lsg-action-btn--compact {
    min-height: 34px;
    padding: .55rem .7rem;
}

.lsg-action-btn--primary {
    --lsg-bg: rgba(37,99,235,.08);
    --lsg-border: rgba(37,99,235,.16);
    --lsg-text: #2563eb;
}

.lsg-action-btn--success {
    --lsg-bg: rgba(34,197,94,.08);
    --lsg-border: rgba(34,197,94,.18);
    --lsg-text: #15803d;
}

.lsg-action-btn--warning {
    --lsg-bg: rgba(245,158,11,.08);
    --lsg-border: rgba(245,158,11,.22);
    --lsg-text: #b45309;
}

.lsg-action-btn--danger {
    --lsg-bg: rgba(239,68,68,.08);
    --lsg-border: rgba(239,68,68,.2);
    --lsg-text: #b91c1c;
}

.ai-dashboard-grid {
    display: grid;
    grid-template-columns: 1.2fr .8fr;
    gap: 1rem;
    align-items: stretch;
}

.ai-toolbar-grid,
.ai-toolbar {
    display: grid;
    gap: .75rem;
}

.ai-toolbar {
    padding: 1rem;
}

.ai-toolbar-search {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: .75rem;
    align-items: center;
}

.ai-toolbar-search__field {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: .7rem;
    align-items: center;
    border-radius: 5px;
    border: 1px solid rgba(148,163,184,.25);
    padding: 0 0 0 .8rem;
    background: rgba(255,255,255,.85);
}

.ai-toolbar-search__field i {
    color: #64748b;
}

.ai-toolbar-search__field .ai-copy-input {
    border: 0;
    background: transparent;
    padding-left: 0;
    padding-right: 0;
}

.ai-stats {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: .75rem;
}

.ai-stat {
    padding: .95rem;
    display: grid;
    gap: .3rem;
}

.ai-stat__label {
    font-size: .76rem;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #64748b;
}

.ai-stat__value {
    font-size: 1.35rem;
    line-height: 1;
    color: #18212b;
}

.ai-card,
.ai-form-card {
    padding: 1rem;
}

.ai-table-wrap,
.ai-consensus-page .table-responsive {
    overflow-x: auto;
}

.ai-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 760px;
}

.ai-table th,
.ai-table td,
.ai-consensus-page table th,
.ai-consensus-page table td {
    padding: .9rem .75rem;
    border-bottom: 1px solid rgba(226,232,240,.9);
    vertical-align: middle;
}

.ai-table th,
.ai-consensus-page table th {
    font-size: .75rem;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #64748b;
}

.ai-table-title {
    display: grid;
    gap: .15rem;
}

.ai-table-title strong {
    color: #18212b;
}

.ai-table-title span,
.ai-table-url {
    color: #64748b;
}

.ai-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: .25rem .55rem;
    border-radius: 999px;
    font-size: .72rem;
    border: 1px solid transparent;
}

.ai-badge--success {
    color: #166534;
    background: rgba(34,197,94,.08);
    border-color: rgba(34,197,94,.18);
}

.ai-badge--neutral {
    color: #475569;
    background: rgba(148,163,184,.12);
    border-color: rgba(148,163,184,.18);
}

.ai-actions {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
}

.ai-actions--center {
    justify-content: center;
}

.ai-copy-input,
.ai-copy-field,
.ai-textarea {
    border-radius: 5px;
}

.ai-copy-input,
.ai-copy-field {
    width: 100%;
    border: 1px solid rgba(148,163,184,.28);
    padding: .75rem .85rem;
    background: rgba(255,255,255,.92);
}

.ai-textarea,
.ai-copy-field {
    width: 100%;
    border: 1px solid rgba(148,163,184,.28);
    padding: .75rem .85rem;
    background: rgba(255,255,255,.92);
    min-height: 140px;
    resize: vertical;
}

.ai-grid {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 1rem;
}

.ai-grid-1 {
    grid-column: span 2;
}

.ai-field {
    display: block;
}

.ai-label {
    display: block;
    margin-bottom: .45rem;
    font-size: .78rem;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: #64748b;
}

.ai-meta {
    display: grid;
    gap: .35rem;
    padding: .9rem;
    border-radius: 5px;
    border: 1px solid rgba(226,232,240,.95);
    background: rgba(255,255,255,.65);
}

.ai-meta__label {
    font-size: .76rem;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #64748b;
    font-weight: 700;
}

.ai-show-title {
    margin: 0;
    font-size: 1.15rem;
    color: #18212b;
}

.ai-show-subtitle {
    margin-top: .35rem;
    color: #64748b;
}

.ai-show-grid {
    margin-top: 1rem;
}

.ai-alert {
    padding: .85rem 1rem;
    border-radius: 5px;
    border: 1px solid rgba(34,197,94,.18);
    color: #166534;
    background: rgba(34,197,94,.08);
}

.ai-alert--warning {
    border-color: rgba(245,158,11,.22);
    color: #9a3412;
    background: rgba(245,158,11,.08);
}

.ai-empty-state {
    display: grid;
    gap: .3rem;
    justify-items: center;
    padding: 1rem;
    text-align: center;
}

.ai-empty-state strong {
    color: #18212b;
}

.ai-empty-state span,
.ai-mobile-item__sub,
.ai-mobile-item__category {
    color: #64748b;
}

.ai-mobile-list {
    display: none;
}

.ai-mobile-item {
    padding: .95rem;
}

.ai-mobile-item__header {
    display: flex;
    justify-content: space-between;
    gap: .75rem;
    align-items: flex-start;
}

.ai-pagination-wrap {
    margin-top: 1rem;
}

/* Counters */
.ai-counters {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: .75rem;
    margin-bottom: 1rem;
}

.ai-counter {
    padding: .95rem;
    display: grid;
    gap: .3rem;
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(247, 249, 252, 0.98) 100%);
    border: 1px solid var(--border-soft, rgba(21, 32, 51, 0.12));
    border-radius: 5px;
    box-shadow: var(--shadow-soft, 0 8px 24px rgba(15, 23, 42, 0.05));
}

.ai-counter-label {
    font-size: .76rem;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #64748b;
}

.ai-counter-value {
    font-size: 1.1rem;
    line-height: 1.2;
    color: #18212b;
    font-weight: 700;
    word-break: break-word;
}

.ai-counter--highlight .ai-counter-value {
    color: #b45309;
}

/* Collapse */
.ai-collapse-card {
    margin-bottom: 1rem;
}

.ai-collapse-toggle {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    padding: .95rem 1rem;
    border-radius: 5px;
    border: 1px solid rgba(148,163,184,.22);
    background: rgba(255,255,255,.85);
    color: #18212b;
    cursor: pointer;
    text-align: left;
    transition: all .2s ease;
}

.ai-collapse-toggle:hover {
    filter: brightness(.98);
    transform: translateY(-1px);
}

.ai-collapse-title {
    font-size: .92rem;
    font-weight: 700;
    color: #18212b;
}

.ai-collapse-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: transform .18s ease;
    color: #64748b;
}

.ai-collapse-toggle[aria-expanded="true"] .ai-collapse-icon {
    transform: rotate(180deg);
}

.ai-collapse-body {
    padding-top: .75rem;
}

.ai-collapse-body[hidden] {
    display: none !important;
}

/* Table actions */
.ai-table-actions {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: .5rem;
    flex-wrap: wrap;
}

.ai-table-actions .lsg-action-form {
    display: inline-flex;
    margin: 0;
}

.ai-table-actions .lsg-action-btn {
    min-width: 40px;
    min-height: 40px;
    padding: 0 .8rem;
}

.ai-table-actions .lsg-action-btn--compact {
    min-width: 34px;
    min-height: 34px;
    padding: .55rem .7rem;
}

.ai-table-actions .lsg-action-btn__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

/* Secret / copy fields */
.ai-secret-field {
    display: grid;
    grid-template-columns: minmax(0,1fr) auto auto;
    gap: .6rem;
    align-items: start;
}

.ai-secret-field--meta {
    margin-top: .45rem;
}

.ai-secret-field--textarea {
    align-items: start;
}

.ai-secret-field--textarea .lsg-action-btn {
    align-self: start;
}

.ai-secret-textarea {
    color: transparent;
    text-shadow: 0 0 8px rgba(100,116,139,.85);
    caret-color: transparent;
    user-select: none;
}

.ai-secret-textarea--revealed {
    color: var(--text-primary, #18212b);
    text-shadow: none;
    caret-color: auto;
    user-select: text;
}

.ai-field-error {
    margin-top: 6px;
    font-size: 12px;
    color: #dc3545;
    font-weight: 500;
}

.ai-copy-input.is-invalid,
.ai-copy-field.is-invalid,
.ai-textarea.is-invalid {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 3px rgba(220,53,69,.12);
}

@media (max-width: 991px) {
    .ai-dashboard-grid,
    .ai-stats,
    .ai-grid,
    .ai-counters {
        grid-template-columns: 1fr;
    }

    .ai-grid-1 {
        grid-column: span 1;
    }
}

@media (max-width: 768px) {
    .ai-consensus-page {
        padding: .75rem;
    }

    .ai-page-header,
    .ai-card,
    .ai-form-card,
    .ai-toolbar {
        padding: .9rem;
    }

    .ai-page-header__main {
        flex-direction: column;
    }

    .ai-page-header__identity {
        width: 100%;
    }

    .ai-page-header__actions,
    .lsg-page-actions {
        width: 100%;
        justify-content: flex-start;
    }

    .ai-toolbar-search {
        grid-template-columns: 1fr;
    }

    .ai-table-wrap,
    .ai-consensus-page .table-responsive {
        display: none;
    }

    .ai-mobile-list {
        display: grid;
        gap: .75rem;
    }
}
</style>
