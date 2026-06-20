<style>
    .lsg-section-panel {
        display: grid;
        gap: 14px;
        border: 1px solid var(--border-soft, rgba(148, 163, 184, .22));
        background: var(--bg-panel, var(--card-bg, #fff));
        color: var(--text-primary, #111827);
        padding: 16px;
    }

    .lsg-section-head {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: flex-start;
        flex-wrap: wrap;
    }

    .lsg-section-head span {
        display: block;
        color: #d4a017;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .06em;
        text-transform: uppercase;
        margin-bottom: 4px;
    }

    .lsg-section-head h3 {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 900;
    }

    .lsg-section-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
        gap: 10px;
    }

    .lsg-section-card {
        display: grid;
        gap: 5px;
        min-width: 0;
        border: 1px solid var(--border-soft, rgba(148, 163, 184, .18));
        background: var(--bg-panel-soft, rgba(148, 163, 184, .06));
        padding: 12px;
    }

    .lsg-section-card__head {
        display: flex;
        gap: 9px;
        align-items: center;
        min-width: 0;
    }

    .lsg-section-card__head i {
        color: #d4a017;
    }

    .lsg-section-card strong,
    .lsg-section-card span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .lsg-section-card span {
        color: var(--text-muted, #64748b);
        font-size: .82rem;
    }

    .lsg-section-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 6px;
    }
</style>
