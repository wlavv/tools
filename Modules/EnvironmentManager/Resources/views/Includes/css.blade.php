<style>
    .environment-manager-shell {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        width: 100%;
    }

    .environment-manager-card {
        background: #fff;
        border: 1px solid rgba(15, 23, 42, .08);
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, .06);
        padding: 1rem;
    }

    .environment-manager-hero {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        align-items: flex-start;
    }

    .environment-manager-title {
        margin: 0;
        font-size: 1.45rem;
        font-weight: 800;
        color: #111827;
    }

    .environment-manager-subtitle {
        margin: .35rem 0 0;
        color: #64748b;
        max-width: 960px;
    }

    .environment-manager-badge {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border-radius: 999px;
        padding: .25rem .65rem;
        font-size: .78rem;
        font-weight: 700;
        background: #eef2ff;
        color: #3730a3;
        white-space: nowrap;
    }

    .environment-manager-badge--success {
        background: #ecfdf5;
        color: #047857;
    }

    .environment-manager-badge--warning {
        background: #fff7ed;
        color: #c2410c;
    }

    .environment-manager-badge--danger {
        background: #fef2f2;
        color: #b91c1c;
    }

    .environment-manager-badge--muted {
        background: #f1f5f9;
        color: #475569;
    }

    .environment-manager-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
    }

    .environment-manager-grid--two {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .environment-manager-nav-card {
        display: block;
        color: inherit;
        text-decoration: none;
        transition: transform .15s ease, box-shadow .15s ease;
    }

    .environment-manager-nav-card:hover {
        color: inherit;
        text-decoration: none;
        transform: translateY(-1px);
        box-shadow: 0 16px 36px rgba(15, 23, 42, .08);
    }

    .environment-manager-nav-card__icon {
        display: inline-flex;
        width: 42px;
        height: 42px;
        border-radius: 12px;
        align-items: center;
        justify-content: center;
        background: #f8fafc;
        color: #334155;
        margin-bottom: .75rem;
    }

    .environment-manager-nav-card__title {
        font-weight: 800;
        margin-bottom: .25rem;
        color: #111827;
    }

    .environment-manager-nav-card__text {
        color: #64748b;
        font-size: .9rem;
        line-height: 1.35;
    }

    .environment-manager-summary-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: .75rem;
    }

    .environment-manager-stat {
        padding: .9rem;
        border-radius: 14px;
        background: #f8fafc;
    }

    .environment-manager-stat__label {
        display: block;
        color: #64748b;
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .environment-manager-stat__value {
        display: block;
        margin-top: .25rem;
        color: #0f172a;
        font-size: 1.1rem;
        font-weight: 800;
        word-break: break-word;
    }

    .environment-manager-toolbar {
        display: flex;
        gap: .75rem;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
    }

    .environment-manager-search {
        display: flex;
        gap: .5rem;
        flex: 1 1 420px;
    }

    .environment-manager-input {
        width: 100%;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        min-height: 42px;
        padding: .55rem .8rem;
        background: #fff;
        color: #0f172a;
    }

    .environment-manager-btn {
        display: inline-flex;
        min-height: 42px;
        align-items: center;
        justify-content: center;
        gap: .4rem;
        border: 0;
        border-radius: 12px;
        padding: .55rem .9rem;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
        background: #0f172a;
        color: #fff;
        white-space: nowrap;
    }

    .environment-manager-btn:hover {
        color: #fff;
        text-decoration: none;
    }

    .environment-manager-btn--muted {
        background: #f1f5f9;
        color: #334155;
    }

    .environment-manager-btn--muted:hover {
        color: #334155;
    }

    .environment-manager-section-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: .75rem;
    }

    .environment-manager-section-title h2,
    .environment-manager-section-title h3 {
        margin: 0;
        font-size: 1rem;
        font-weight: 800;
        color: #0f172a;
    }

    .environment-manager-table-wrap {
        overflow-x: auto;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
    }

    .environment-manager-table {
        width: 100%;
        min-width: 900px;
        border-collapse: collapse;
        background: #fff;
    }

    .environment-manager-table th,
    .environment-manager-table td {
        text-align: left;
        padding: .7rem .75rem;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: top;
    }

    .environment-manager-table th {
        font-size: .78rem;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: .04em;
        background: #f8fafc;
    }

    .environment-manager-table tr:last-child td {
        border-bottom: 0;
    }

    .environment-manager-key {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
        font-size: .86rem;
        font-weight: 800;
        color: #0f172a;
        word-break: break-word;
    }

    .environment-manager-value {
        display: block;
        max-width: 520px;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
        font-size: .83rem;
        color: #334155;
        white-space: pre-wrap;
        word-break: break-word;
    }

    .environment-manager-muted {
        color: #94a3b8;
    }

    .environment-manager-module-list {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
    }

    .environment-manager-module-card {
        display: flex;
        flex-direction: column;
        gap: .65rem;
        color: inherit;
        text-decoration: none;
    }

    .environment-manager-module-card:hover {
        color: inherit;
        text-decoration: none;
    }

    .environment-manager-module-card__header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: .75rem;
    }

    .environment-manager-module-card__title {
        margin: 0;
        font-size: 1rem;
        font-weight: 800;
        color: #0f172a;
    }

    .environment-manager-module-card__meta {
        color: #64748b;
        font-size: .86rem;
    }

    .environment-manager-alert {
        border-radius: 14px;
        padding: .9rem 1rem;
        background: #ecfdf5;
        color: #047857;
        font-weight: 700;
    }

    .environment-manager-alert--warning {
        background: #fff7ed;
        color: #c2410c;
    }

    @media (max-width: 1200px) {
        .environment-manager-grid,
        .environment-manager-summary-grid,
        .environment-manager-module-list {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px) {
        .environment-manager-grid,
        .environment-manager-grid--two,
        .environment-manager-summary-grid,
        .environment-manager-module-list {
            grid-template-columns: 1fr;
        }

        .environment-manager-hero {
            flex-direction: column;
        }
    }
</style>
