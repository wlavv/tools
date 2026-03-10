<style>
.password-manager-page {
    padding: 1rem;
}

.password-manager-shell {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
}

.password-manager-toolbar,
.password-manager-card,
.password-manager-form-card {
    border-radius: 16px;
    padding: 1rem;
    box-shadow: 0 6px 20px rgba(15, 23, 42, 0.04);
}

.password-manager-toolbar-grid,
.password-manager-stats {
    display: grid;
    gap: 0.75rem;
}

.password-manager-toolbar-grid {
    grid-template-columns: 1fr;
}

.password-manager-stats {
    grid-template-columns: repeat(3, 1fr);
}

.password-manager-stat {
    border-radius: 12px;
    padding: 0.85rem;
    border: 1px solid #e2e8f0;
    color: #fff;
}

.password-manager-table-wrap {
    overflow-x: auto;
}

.password-manager-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 760px;
}

.password-manager-table th,
.password-manager-table td {
    padding: 0.85rem 0.75rem;
    border-bottom: 1px solid #eef2f7;
    text-align: center;
    vertical-align: top;
}

.password-manager-table th {
    font-size: 0.82rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #64748b;
}

.password-manager-badge {
    display: inline-flex;
    padding: 0.2rem 0.55rem;
    border-radius: 999px;
    font-size: 0.75rem;
    border: 1px solid #dbeafe;
    background: #eff6ff;
}

.password-manager-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    text-align: center;
}

.password-manager-btn,
.password-manager-input,
.password-manager-textarea {
    border-radius: 12px;
}

.password-manager-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #d1d5db;
    padding: 0.6rem 0.9rem;
    text-decoration: none;
    color: #fff;
    cursor: pointer;
}

.password-manager-btn-primary {
    background: #111827;
    color: #fff;
    border-color: #111827;
}

.password-manager-btn-danger {
    color: #991b1b;
    border-color: #fecaca;
}

.password-manager-input,
.password-manager-textarea {
    width: 100%;
    border: 1px solid #d1d5db;
    padding: 0.7rem 0.8rem;
}

.password-manager-textarea {
    min-height: 120px;
    resize: vertical;
}

.password-manager-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
}

.password-manager-grid-1 {
    grid-column: span 2;
}

.password-manager-label {
    display: block;
    margin-bottom: 0.35rem;
    font-weight: 600;
}

.password-manager-meta {
    display: grid;
    gap: 0.5rem;
}

.password-manager-alert {
    padding: 0.8rem 1rem;
    border-radius: 12px;
    border: 1px solid #bbf7d0;
    color: #166534;
}

.password-manager-mobile-list {
    display: none;
}

@media (max-width: 768px) {
    .password-manager-page {
        padding: 0.75rem;
    }

    .password-manager-stats,
    .password-manager-grid {
        grid-template-columns: 1fr;
    }

    .password-manager-grid-1 {
        grid-column: span 1;
    }

    .password-manager-table-wrap {
        display: none;
    }

    .password-manager-mobile-list {
        display: grid;
        gap: 0.75rem;
    }

    .password-manager-mobile-item {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 0.9rem;
    }
}

.passwordManager-card{
  background: linear-gradient(180deg,rgba(37,47,59,.94) 0%,rgba(32,40,51,.96) 100%);
  border: 1px solid var(--border-soft);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-soft);
}
</style>
