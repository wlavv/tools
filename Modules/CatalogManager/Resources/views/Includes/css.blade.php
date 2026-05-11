<style>
/*
|--------------------------------------------------------------------------
| CatalogManager LSG / WebCatalogue-like inline styles
|--------------------------------------------------------------------------
| Loaded directly by the module layout.
| This avoids relying on the styles stack in the base layout.
*/

.catalog-lsg-shell {
    width: 100%;
    padding: 0;
    color: #111827;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.catalog-lsg-breadcrumbs {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 6px;
    margin-bottom: 12px;
    font-size: 12px;
    color: #64748b;
}

.catalog-lsg-breadcrumbs a {
    color: #64748b;
    text-decoration: none;
    font-weight: 700;
}

.catalog-lsg-breadcrumbs a:hover {
    color: #9a7415;
    text-decoration: none;
}

.catalog-lsg-nav {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(158px, 1fr));
    gap: 8px;
    margin-bottom: 4px;
}

.catalog-lsg-nav a {
    border: 1px solid #dbe3ef;
    border-radius: 5px;
    padding: 13px 14px;
    background: #fff;
    text-decoration: none;
    color: #111827;
    display: flex;
    align-items: center;
    gap: 12px;
    min-height: 52px;
    transition: .16s ease;
    box-shadow: 0 6px 14px rgba(15, 23, 42, .045);
    font-weight: 800;
}

.catalog-lsg-nav a i {
    width: 34px;
    height: 34px;
    border-radius: 5px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #111827, #334155);
    color: #fff;
    box-shadow: 0 8px 18px rgba(15, 23, 42, .18);
}

.catalog-lsg-nav a:hover {
    transform: translateY(-1px);
    box-shadow: 0 10px 22px rgba(15, 23, 42, .08);
    border-color: rgba(212, 175, 55, .42);
    color: #111827;
    text-decoration: none;
}

.catalog-lsg-hero {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    align-items: center;
    margin-bottom: 0;
    border-radius: 5px;
    background: #fff;
    border: 1px solid rgba(15, 23, 42, .08);
    box-shadow: 0 8px 18px rgba(15, 23, 42, .055);
    padding: 12px 14px;
}

.catalog-lsg-hero h1 {
    display: none;
}

.catalog-lsg-hero p {
    margin: 0;
    color: #64748b;
    max-width: 780px;
}

.catalog-lsg-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    text-transform: uppercase;
    font-weight: 900;
    letter-spacing: .04em;
    color: #9a7415;
}

.catalog-lsg-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.catalog-lsg-actions .btn,
.catalog-lsg-shell .btn {
    border-radius: 5px !important;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    gap: 7px;
}

.catalog-lsg-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 10px;
    width: 100%;
    margin-bottom: 0;
}

.catalog-lsg-card {
    border-radius: 5px;
    background: #fff;
    border: 1px solid rgba(15, 23, 42, .08);
    box-shadow: 0 8px 18px rgba(15, 23, 42, .055);
    padding: 14px;
    margin-bottom: 0;
}

.catalog-lsg-card h3 {
    font-size: 15px;
    margin: 0 0 12px;
    font-weight: 850;
}

.catalog-lsg-kpi {
    position: relative;
    overflow: hidden;
    min-height: 104px;
    display: flex;
    align-items: flex-end;
    color: #fff;
    background: linear-gradient(135deg, #111827, #374151);
}

.catalog-lsg-kpi:nth-child(2) { background: linear-gradient(135deg, #92400e, #d97706); }
.catalog-lsg-kpi:nth-child(3) { background: linear-gradient(135deg, #075985, #0284c7); }
.catalog-lsg-kpi:nth-child(4) { background: linear-gradient(135deg, #581c87, #9333ea); }
.catalog-lsg-kpi:nth-child(5) { background: linear-gradient(135deg, #047857, #34d399); }
.catalog-lsg-kpi:nth-child(6) { background: linear-gradient(135deg, #b91c1c, #fb7185); }

.catalog-lsg-kpi span {
    color: rgba(255, 255, 255, .86);
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: .04em;
    font-weight: 850;
}

.catalog-lsg-kpi strong {
    display: block;
    font-size: 28px;
    font-weight: 950;
    color: #fff;
    line-height: 1;
    margin-top: 6px;
}

.catalog-lsg-table {
    width: 100%;
    border-collapse: separate !important;
    border-spacing: 0 8px !important;
}

.catalog-lsg-table th {
    font-size: 12px;
    text-transform: uppercase;
    color: #64748b;
    border: 0 !important;
    padding: 7px 10px !important;
    background: transparent !important;
}

.catalog-lsg-table td {
    background: #fff;
    border-top: 1px solid #e5e7eb;
    border-bottom: 1px solid #e5e7eb;
    padding: 10px !important;
    vertical-align: middle;
}

.catalog-lsg-table td:first-child {
    border-left: 1px solid #e5e7eb;
    border-radius: 5px 0 0 5px;
}

.catalog-lsg-table td:last-child {
    border-right: 1px solid #e5e7eb;
    border-radius: 0 5px 5px 0;
}

.catalog-lsg-table tbody tr {
    transition: .16s ease;
}

.catalog-lsg-table tbody tr:hover td {
    background: #fffbeb;
    border-color: rgba(212, 175, 55, .38);
}

.catalog-lsg-badge {
    display: inline-flex;
    border-radius: 999px;
    padding: 3px 8px;
    background: #f1f5f9;
    color: #475569;
    font-size: 12px;
    font-weight: 850;
}

.catalog-lsg-form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 12px;
}

.catalog-lsg-card:has(.catalog-lsg-form-grid) {
    max-width: 860px;
}

.catalog-lsg-card form {
    display: grid;
    gap: 14px;
}

.catalog-lsg-form-grid {
    padding: 4px 0;
}

.catalog-lsg-form-group {
    display: grid;
    gap: 5px;
}

.catalog-lsg-form-group--full {
    grid-column: 1 / -1;
}

.catalog-lsg-form-group label {
    font-size: 12px;
    font-weight: 850;
    text-transform: uppercase;
    color: #64748b;
    display: block;
    margin-bottom: 0;
}

.catalog-lsg-form-group input,
.catalog-lsg-form-group select,
.catalog-lsg-form-group textarea,
.catalog-lsg-card input,
.catalog-lsg-card select,
.catalog-lsg-card textarea {
    width: 100%;
    border: 1px solid #dbe3ef;
    border-radius: 5px;
    padding: 10px 12px;
    background: rgba(255,255,255,.86);
    color: #111827;
    outline: none;
    transition: .16s ease;
}

.catalog-lsg-form-group input[type="checkbox"],
.catalog-lsg-card input[type="checkbox"] {
    width: auto;
    min-width: 16px;
    height: 16px;
    margin: 0;
}

.catalog-lsg-form-group label:has(input[type="checkbox"]) {
    min-height: 42px;
    display: inline-flex;
    align-items: center;
    gap: 9px;
    padding: 10px 12px;
    border: 1px solid #dbe3ef;
    border-radius: 5px;
    background: rgba(255,255,255,.70);
    color: #111827;
    text-transform: none;
    letter-spacing: 0;
    font-size: 14px;
}

.catalog-lsg-form-group input:focus,
.catalog-lsg-form-group select:focus,
.catalog-lsg-form-group textarea:focus,
.catalog-lsg-card input:focus,
.catalog-lsg-card select:focus,
.catalog-lsg-card textarea:focus {
    border-color: rgba(212, 175, 55, .75);
    box-shadow: 0 0 0 3px rgba(212, 175, 55, .16);
}

.catalog-lsg-form-actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-end;
    gap: 8px;
    padding-top: 12px;
    border-top: 1px solid rgba(15, 23, 42, .08);
}

.catalog-lsg-panel-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(245px, 1fr));
    gap: 10px;
    width: 100%;
}

.catalog-lsg-panel-card {
    position: relative;
    min-height: 176px;
    border-radius: 5px;
    background: #fff;
    color: #111827;
    border: 1px solid rgba(15, 23, 42, .08);
    box-shadow: 0 10px 26px rgba(15, 23, 42, .07);
    overflow: hidden;
    transition: .18s ease;
}

.catalog-lsg-panel-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 16px 34px rgba(15, 23, 42, .12);
    border-color: rgba(212, 175, 55, .35);
}

.catalog-store-ribbon {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 8px;
    width: 100%;
}

.catalog-store-ribbon__item {
    min-width: 0;
    display: grid;
    grid-template-columns: 38px minmax(0, 1fr) auto;
    gap: 10px;
    align-items: center;
    padding: 10px;
    border: 1px solid rgba(15, 23, 42, .08);
    border-radius: 5px;
    background: #fff;
    color: #111827;
    text-decoration: none;
    box-shadow: 0 6px 14px rgba(15, 23, 42, .045);
}

.catalog-store-ribbon__item:hover {
    color: #111827;
    text-decoration: none;
    border-color: rgba(212, 175, 55, .42);
    box-shadow: 0 10px 22px rgba(15, 23, 42, .08);
}

.catalog-store-ribbon__icon {
    width: 38px;
    height: 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 5px;
    background: rgba(212, 175, 55, .12);
    color: #9a7415;
}

.catalog-store-ribbon__main,
.catalog-store-ribbon__metrics {
    min-width: 0;
}

.catalog-store-ribbon__main strong,
.catalog-store-ribbon__main small,
.catalog-store-ribbon__metrics small {
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.catalog-store-ribbon__main strong {
    font-size: 13px;
    font-weight: 900;
}

.catalog-store-ribbon__main small,
.catalog-store-ribbon__metrics small {
    color: #64748b;
    font-size: 11px;
    font-weight: 750;
}

.catalog-store-ribbon__metrics {
    display: grid;
    justify-items: end;
    gap: 3px;
}

.catalog-store-score {
    min-width: 34px;
    height: 28px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 5px;
    background: #f1f5f9;
    color: #64748b;
    font-size: 13px;
    font-weight: 950;
}

.catalog-store-score.is-good {
    background: rgba(34, 197, 94, .12);
    color: #15803d;
}

.catalog-store-score.is-medium {
    background: rgba(245, 158, 11, .14);
    color: #92400e;
}

.catalog-store-score.is-poor {
    background: rgba(239, 68, 68, .12);
    color: #b91c1c;
}

.catalog-lsg-panel-top {
    display: grid;
    grid-template-columns: 42px 1fr auto;
    gap: 12px;
    align-items: center;
    padding: 14px;
    border-bottom: 1px solid #e5e7eb;
    background: linear-gradient(135deg, #fff, #f8fafc);
}

.catalog-lsg-panel-icon {
    width: 42px;
    height: 42px;
    border-radius: 5px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #111827, #334155);
    color: #fff;
    font-size: 18px;
    box-shadow: 0 10px 24px rgba(15, 23, 42, .16);
}

.catalog-lsg-panel-title strong {
    display: block;
    font-size: 14px;
    font-weight: 900;
}

.catalog-lsg-panel-title span {
    display: block;
    color: #64748b;
    font-size: 12px;
    line-height: 1.3;
}

.catalog-lsg-panel-count {
    min-width: 34px;
    height: 34px;
    border-radius: 5px;
    background: #111827;
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 950;
}

.catalog-lsg-panel-items {
    padding: 8px;
}

.catalog-lsg-panel-item {
    display: flex;
    justify-content: space-between;
    gap: 8px;
    padding: 8px;
    text-decoration: none;
    color: #111827;
    border-radius: 5px;
}

.catalog-lsg-panel-item:hover {
    background: #f8fafc;
    color: #111827;
    text-decoration: none;
}

.catalog-lsg-panel-item strong {
    display: block;
    font-size: 13px;
}

.catalog-lsg-panel-item small {
    display: block;
    color: #64748b;
    font-size: 12px;
}

.catalog-lsg-panel-item em {
    font-style: normal;
    display: inline-flex;
    border-radius: 999px;
    padding: 3px 8px;
    background: #f1f5f9;
    color: #475569;
    font-size: 11px;
    font-weight: 850;
    height: max-content;
}

.catalog-lsg-dropzone,
.dropzone {
    margin-top: 14px;
    border: 2px dashed #cbd5e1 !important;
    border-radius: 5px !important;
    background: linear-gradient(135deg, #fff, #f8fafc) !important;
    padding: 24px !important;
    min-height: 140px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    flex-direction: column !important;
    text-align: center !important;
    gap: 8px !important;
    cursor: pointer !important;
}

.dropzone .dz-message {
    color: #64748b;
    font-weight: 800;
}

.catalog-builder-layout {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
    width: 100%;
}

.catalog-builder-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 14px;
}

.catalog-builder-head h3 {
    margin-bottom: 4px;
}

.catalog-builder-head p {
    margin: 0;
    color: #64748b;
    font-size: 13px;
}

.catalog-builder-checks {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.catalog-builder-check {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    min-height: 34px;
    padding: 7px 10px;
    border-radius: 5px;
    border: 1px solid #e5e7eb;
    background: #f8fafc;
    color: #64748b;
    font-size: 12px;
    font-weight: 850;
}

.catalog-builder-check.is-ok {
    border-color: rgba(34, 197, 94, .26);
    background: rgba(34, 197, 94, .10);
    color: #15803d;
}

.catalog-builder-check.is-missing {
    border-color: rgba(245, 158, 11, .28);
    background: rgba(245, 158, 11, .10);
    color: #92400e;
}

.catalog-builder-kv {
    display: grid;
    grid-template-columns: 130px minmax(0, 1fr);
    gap: 10px;
    padding: 9px 0;
    border-bottom: 1px solid rgba(15, 23, 42, .08);
}

.catalog-builder-kv span {
    color: #64748b;
    font-size: 12px;
    font-weight: 850;
    text-transform: uppercase;
}

.catalog-builder-kv strong {
    min-width: 0;
    color: #111827;
    font-weight: 850;
    overflow-wrap: anywhere;
}

.catalog-builder-notes {
    margin-top: 12px;
    min-height: 94px;
    border-radius: 5px;
    border: 1px solid #e5e7eb;
    background: #f8fafc;
    color: #475569;
    padding: 12px;
    line-height: 1.6;
    white-space: pre-line;
}

.catalog-builder-muted {
    display: block;
    color: #64748b;
    font-size: 12px;
    line-height: 1.35;
}

.catalog-builder-dot {
    display: inline-flex;
    width: 9px;
    height: 9px;
    border-radius: 999px;
    margin-right: 6px;
    background: #f59e0b;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, .14);
}

.catalog-builder-dot.is-ok {
    background: #22c55e;
    box-shadow: 0 0 0 3px rgba(34, 197, 94, .14);
}

.catalog-builder-dot.is-missing {
    background: #f59e0b;
}

.catalog-builder-flags {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
}

.catalog-builder-flags span {
    display: inline-flex;
    align-items: center;
    min-height: 24px;
    padding: 4px 7px;
    border-radius: 5px;
    background: #f1f5f9;
    color: #64748b;
    font-size: 11px;
    font-weight: 850;
    text-transform: uppercase;
}

.catalog-builder-flags span.is-ok {
    background: rgba(34, 197, 94, .12);
    color: #15803d;
}

.catalog-lsg-badge--danger {
    background: rgba(239, 68, 68, .10);
    color: #b91c1c;
}

/* DataTables */
.catalog-lsg-card .dataTables_wrapper {
    width: 100%;
}

.catalog-dt-top,
.catalog-dt-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    flex-wrap: wrap;
    margin-bottom: .75rem;
}

.catalog-dt-bottom {
    margin-top: .75rem;
    margin-bottom: 0;
}

.catalog-dt-search input,
.catalog-dt-length select,
.dataTables_filter input,
.dataTables_length select {
    border: 1px solid #dbe3ef !important;
    border-radius: 5px !important;
    padding: 7px 9px !important;
    background: #fff !important;
}

.catalog-dt-table {
    overflow-x: auto;
}

.dataTables_info {
    font-size: 12px;
    color: #64748b;
}

.pagination .page-link,
.page-link {
    border-radius: 5px !important;
    margin: 0 2px;
    font-weight: 800;
    color: #111827;
}

.pagination .active .page-link {
    background: #111827 !important;
    border-color: #111827 !important;
}

.swal2-popup {
    border-radius: 5px !important;
}

@media(max-width: 768px) {
    .catalog-lsg-hero {
        align-items: flex-start;
        flex-direction: column;
    }

    .catalog-lsg-actions {
        justify-content: flex-start;
    }

    .catalog-lsg-nav {
        grid-template-columns: 1fr;
    }

    .catalog-dt-top,
    .catalog-dt-bottom {
        align-items: stretch;
        flex-direction: column;
    }

    .catalog-builder-layout {
        grid-template-columns: 1fr;
    }

    .catalog-builder-head {
        flex-direction: column;
    }

    .catalog-builder-kv {
        grid-template-columns: 1fr;
    }
}
</style>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>

<script>
(function () {
    'use strict';

    window.CatalogManagerToast = function (type, message) {
        if (typeof Swal === 'undefined') {
            alert(message);
            return;
        }

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: type || 'info',
            title: message,
            showConfirmButton: false,
            timer: 3200,
            timerProgressBar: true
        });
    };

    window.CatalogManagerConfirm = function (message, callback) {
        if (typeof Swal === 'undefined') {
            if (confirm(message)) {
                callback();
            }
            return;
        }

        Swal.fire({
            title: 'Confirmar ação',
            text: message || 'Tens a certeza?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sim, continuar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then(function (result) {
            if (result.isConfirmed) {
                callback();
            }
        });
    };

    function initDataTables() {
        if (typeof jQuery === 'undefined' || typeof jQuery.fn.DataTable === 'undefined') {
            return;
        }

        jQuery('.catalog-lsg-datatable').each(function () {
            var table = jQuery(this);
            var columnCount = table.find('thead th').length;
            var hasInvalidBodyRows = false;

            if (jQuery.fn.DataTable.isDataTable(this)) {
                return;
            }

            table.find('tbody tr').each(function () {
                var cells = jQuery(this).children('td, th');

                if (cells.filter('[colspan]').length || cells.length !== columnCount) {
                    hasInvalidBodyRows = true;
                }
            });

            if (!columnCount || hasInvalidBodyRows) {
                return;
            }

            table.DataTable({
                pageLength: parseInt(table.data('page-length') || '25', 10),
                order: [],
                responsive: true,
                autoWidth: false,
                language: {
                    search: 'Pesquisar:',
                    lengthMenu: 'Mostrar _MENU_ registos',
                    info: 'A mostrar _START_ a _END_ de _TOTAL_ registos',
                    infoEmpty: 'Sem registos',
                    infoFiltered: '(filtrado de _MAX_ registos)',
                    zeroRecords: 'Nenhum registo encontrado',
                    emptyTable: 'Sem dados disponíveis',
                    paginate: {
                        first: 'Primeiro',
                        previous: 'Anterior',
                        next: 'Seguinte',
                        last: 'Último'
                    }
                },
                dom:
                    "<'catalog-dt-top'<'catalog-dt-length'l><'catalog-dt-search'f>>" +
                    "<'catalog-dt-table'tr>" +
                    "<'catalog-dt-bottom'<'catalog-dt-info'i><'catalog-dt-paging'p>>"
            });
        });
    }

    function initDropzones() {
        if (typeof Dropzone === 'undefined') {
            return;
        }

        Dropzone.autoDiscover = false;

        document.querySelectorAll('.catalog-lsg-dropzone').forEach(function (el) {
            if (el.dropzone) {
                return;
            }

            new Dropzone(el, {
                url: el.dataset.url || el.getAttribute('action') || window.location.href,
                paramName: el.dataset.param || 'file',
                maxFilesize: parseInt(el.dataset.maxFilesize || '8', 10),
                acceptedFiles: el.dataset.acceptedFiles || null,
                addRemoveLinks: true,
                dictDefaultMessage: 'Arrasta ficheiros para aqui ou clica para selecionar',
                dictRemoveFile: 'Remover',
                dictCancelUpload: 'Cancelar',
                dictUploadCanceled: 'Upload cancelado',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            });
        });
    }

    function initConfirmButtons() {
        document.querySelectorAll('[data-catalog-confirm]').forEach(function (el) {
            el.addEventListener('click', function (event) {
                event.preventDefault();

                var href = el.getAttribute('href');
                var form = el.closest('form');
                var message = el.dataset.catalogConfirm || 'Tens a certeza?';

                window.CatalogManagerConfirm(message, function () {
                    if (form) {
                        form.submit();
                        return;
                    }

                    if (href) {
                        window.location.href = href;
                    }
                });
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initDataTables();
        initDropzones();
        initConfirmButtons();
    });
})();
</script>

@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.CatalogManagerToast('success', @json(session('success')));
        });
    </script>
@endif

@if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.CatalogManagerToast('error', @json(session('error')));
        });
    </script>
@endif

@if($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.CatalogManagerToast('error', @json($errors->first()));
        });
    </script>
@endif
