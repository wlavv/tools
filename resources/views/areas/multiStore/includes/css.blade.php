<style>
    .multistore-dashboard-panel {
        display: grid;
        gap: 12px;
        border-radius: 5px;
        padding: 14px;
    }

    .page-content-stack:has(> .multistore-dashboard-panel) {
        gap: 8px;
    }

    .quick-access-panel + .multistore-dashboard-panel {
        margin-top: 0;
    }

    .multistore-dashboard-panel__head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .multistore-dashboard-panel__head h3 {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 900;
    }

    .multistore-dashboard-panel__eyebrow {
        display: block;
        margin-bottom: 4px;
        color: #d4a017;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .multistore-dashboard-panel .btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        border-radius: 5px !important;
        font-weight: 800;
    }

    .quick-access-panel .quick-access-grid {
        align-items: stretch;
    }

    .quick-access-panel .quick-access-item {
        display: flex;
        min-width: 0;
    }

    .quick-access-panel .quick-access-link {
        width: 100%;
        height: 100%;
    }

    .quick-access-panel .quick-access-title {
        overflow-wrap: anywhere;
    }

    .quick-access-panel .quick-access-icon img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 6px;
    }

    .multistore-store-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 10px;
    }

    .multistore-master-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .multistore-master-card {
        min-width: 0;
        display: grid;
        grid-template-columns: 42px minmax(0, 1fr);
        gap: 10px 12px;
        align-items: center;
        padding: 12px;
        border: 1px solid var(--border-soft, rgba(148, 163, 184, .22));
        border-radius: 5px;
        background: var(--bg-panel-soft, rgba(148, 163, 184, .06));
        color: var(--text-primary, #f8fafc);
        text-decoration: none;
    }

    .multistore-master-card:hover {
        border-color: rgba(37, 99, 235, .42);
        text-decoration: none;
    }

    .multistore-master-card__icon {
        grid-row: span 2;
        width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px;
        background: rgba(37, 99, 235, .12);
        color: #2563eb;
    }

    .multistore-master-card__icon--suppliers {
        background: rgba(245, 158, 11, .14);
        color: #d97706;
    }

    .multistore-master-card__icon--categories {
        background: rgba(34, 197, 94, .12);
        color: #15803d;
    }

    .multistore-master-card strong,
    .multistore-master-card small {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .multistore-master-card strong {
        font-weight: 900;
        white-space: nowrap;
    }

    .multistore-master-card small {
        color: var(--text-muted, #94a3b8);
        font-size: .82rem;
        line-height: 1.25;
    }

    .multistore-store-card {
        min-width: 0;
        display: grid;
        gap: 4px;
        padding: 12px;
        border: 1px solid var(--border-soft, rgba(148, 163, 184, .22));
        background: var(--bg-panel-soft, rgba(148, 163, 184, .06));
        color: var(--text-primary, #f8fafc);
        text-decoration: none;
    }

    .multistore-store-card:hover {
        border-color: rgba(37, 99, 235, .42);
        text-decoration: none;
    }

    .multistore-store-card i {
        color: #d4a017;
    }

    .multistore-store-card strong,
    .multistore-store-card span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .multistore-store-card span {
        color: var(--text-muted, #94a3b8);
        font-size: .82rem;
    }

    @media(max-width: 992px) {
        .multistore-master-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
