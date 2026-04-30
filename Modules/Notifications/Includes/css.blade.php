<style>
.notifications-dropdown .dropdown-menu {
    border-radius: 5px;
    overflow: hidden;
    border: 1px solid var(--border-soft, rgba(120, 130, 150, .22));
    box-shadow: var(--shadow-soft, 0 14px 40px rgba(0,0,0,.16));
}
.notifications-dropdown .list-group-item {
    padding: .85rem 1rem;
    border-left: 0;
    border-right: 0;
}
.notifications-dropdown .is-unread {
    background: rgba(13,110,253,.06);
}
.lsg-notifications-page .lsg-card {
    border-radius: 5px;
    border: 1px solid var(--border-soft, rgba(120,130,150,.22)) !important;
    box-shadow: var(--shadow-soft, 0 10px 28px rgba(0,0,0,.08));
    overflow: hidden;
}
.lsg-notifications-page .form-control,
.lsg-notifications-page .form-select,
.lsg-notifications-page .btn,
.lsg-notifications-page .badge {
    border-radius: 5px;
}
.lsg-notifications-page .lsg-table thead th {
    font-size: .72rem;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: var(--text-muted, #6c757d);
    background: rgba(120,130,150,.08);
    border-bottom: 1px solid var(--border-soft, rgba(120,130,150,.18));
}
.lsg-notifications-page .lsg-row-unread {
    background: rgba(13,110,253,.045);
}
.lsg-notifications-page .lsg-notification-icon {
    width: 34px;
    height: 34px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 5px;
    background: linear-gradient(135deg, rgba(13,110,253,.16), rgba(218,165,32,.12));
    border: 1px solid rgba(120,130,150,.18);
    flex: 0 0 34px;
}
.lsg-notifications-page .lsg-notification-icon-lg {
    width: 46px;
    height: 46px;
    flex-basis: 46px;
    font-size: 1.15rem;
}
.lsg-notifications-page .lsg-message-box {
    border: 1px solid var(--border-soft, rgba(120,130,150,.18));
    border-radius: 5px;
    padding: 1rem;
    background: rgba(120,130,150,.06);
}
.lsg-notifications-page .lsg-definition-list dt {
    color: var(--text-muted, #6c757d);
    font-weight: 600;
}
</style>


<style>
.lsg-choice-stack {
    display: grid;
    gap: 10px;
}

.lsg-choice,
.lsg-channel {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 12px;
    border: 1px solid rgba(120, 130, 150, 0.25);
    border-radius: 5px;
    background: linear-gradient(180deg, rgba(255,255,255,0.06), rgba(255,255,255,0.02));
    cursor: pointer;
    transition: all .18s ease;
}

.lsg-choice:hover,
.lsg-channel:hover {
    border-color: rgba(13, 110, 253, 0.45);
    transform: translateY(-1px);
}

.lsg-choice input,
.lsg-channel input {
    margin-top: 3px;
}

.lsg-choice span {
    display: flex;
    flex-direction: column;
    line-height: 1.25;
}

.lsg-choice small {
    color: var(--bs-secondary-color, #6c757d);
    margin-top: 3px;
}

.lsg-channel-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
}

.lsg-channel {
    align-items: center;
    text-transform: uppercase;
    font-size: .78rem;
    font-weight: 700;
    letter-spacing: .04em;
}

@media (max-width: 575.98px) {
    .lsg-channel-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<style>
.lsg-notifications-page .lsg-clickable-table tbody tr[data-href] {
    cursor: pointer;
}

.lsg-notifications-page .lsg-row-read,
.lsg-notifications-page .lsg-row-read > td {
    background: rgba(120, 130, 150, .085) !important;
    color: var(--bs-secondary-color, #6c757d);
}

.lsg-notifications-page .lsg-row-read .lsg-notification-icon {
    opacity: .62;
    filter: grayscale(1);
}

.lsg-notifications-page .lsg-row-read .fw-semibold,
.lsg-notifications-page .lsg-row-read .small {
    opacity: .75;
}

.lsg-notifications-page .lsg-row-unread,
.lsg-notifications-page .lsg-row-unread > td {
    background: rgba(13,110,253,.045) !important;
}
</style>

<style>
.lsg-notifications-page .lsg-row-actions .btn {
    border-radius: 5px;
    width: 34px;
    height: 34px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.lsg-notifications-page .lsg-row-actions form {
    margin: 0;
}
</style>
