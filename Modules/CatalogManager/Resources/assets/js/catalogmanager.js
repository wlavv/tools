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

            if (jQuery.fn.DataTable.isDataTable(this)) {
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

            var uploadUrl = el.dataset.url || el.getAttribute('action') || window.location.href;

            new Dropzone(el, {
                url: uploadUrl,
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
                },
                success: function () {
                    window.CatalogManagerToast('success', 'Upload concluído.');
                },
                error: function (file, response) {
                    var message = typeof response === 'string' ? response : 'Erro no upload.';
                    window.CatalogManagerToast('error', message);
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
