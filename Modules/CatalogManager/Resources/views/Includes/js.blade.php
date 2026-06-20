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
                title: 'Confirmar acao',
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
                    autoWidth: false,
                    language: {
                        search: 'Pesquisar:',
                        lengthMenu: 'Mostrar _MENU_ registos',
                        info: 'A mostrar _START_ a _END_ de _TOTAL_ registos',
                        infoEmpty: 'Sem registos',
                        infoFiltered: '(filtrado de _MAX_ registos)',
                        zeroRecords: 'Nenhum registo encontrado',
                        emptyTable: 'Sem dados disponiveis',
                        paginate: {
                            first: 'Primeiro',
                            previous: 'Anterior',
                            next: 'Seguinte',
                            last: 'Ultimo'
                        }
                    }
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
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    success: function () {
                        window.CatalogManagerToast('success', 'Upload concluido.');
                    },
                    error: function (file, response) {
                        window.CatalogManagerToast('error', typeof response === 'string' ? response : 'Erro no upload.');
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

        function initPanelCollapses() {
        document.querySelectorAll('.catalog-lsg-panel-card .catalog-lsg-panel-top').forEach(function (trigger) {
                if (trigger.dataset.catalogCollapseReady === '1') {
                    return;
                }

                trigger.dataset.catalogCollapseReady = '1';

                trigger.addEventListener('click', function () {
                    var card = trigger.closest('.catalog-lsg-panel-card');
                    var isExpanded = card.classList.toggle('is-expanded');

                    card.classList.toggle('is-collapsed', !isExpanded);
                    trigger.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
                });
            });
        }

        function initCategoryTree() {
            document.querySelectorAll('.catalog-category-tree__item.has-children > .catalog-category-tree__node').forEach(function (node) {
                if (node.dataset.catalogTreeReady === '1') {
                    return;
                }

                node.dataset.catalogTreeReady = '1';

                var toggle = function () {
                    var item = node.closest('.catalog-category-tree__item');
                    var isExpanded = item.classList.toggle('is-expanded');

                    item.classList.toggle('is-collapsed', !isExpanded);
                    node.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
                };

                node.addEventListener('click', function (event) {
                    if (event.target.closest('a, button')) {
                        return;
                    }

                    toggle();
                });

                node.addEventListener('keydown', function (event) {
                    if (event.key !== 'Enter' && event.key !== ' ') {
                        return;
                    }

                    event.preventDefault();
                    toggle();
                });
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            initDataTables();
            initDropzones();
            initConfirmButtons();
            initPanelCollapses();
            initCategoryTree();
        });

        document.querySelectorAll('[data-catalog-category-rule-toggle]').forEach(function (trigger) {
            trigger.addEventListener('click', function () {
                var card = trigger.closest('[data-catalog-category-rule]');
                if (card) {
                    card.classList.toggle('is-collapsed');
                }
            });
        });
    })();
</script>
