<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.Dropzone) {
            Dropzone.autoDiscover = false;
        }

        document.querySelectorAll('.dms-upload-zone').forEach(function (zone) {
            var input = zone.querySelector('input[type="file"]');

            ['dragenter', 'dragover'].forEach(function (eventName) {
                zone.addEventListener(eventName, function (event) {
                    event.preventDefault();
                    zone.classList.add('is-dragover');
                });
            });

            ['dragleave', 'drop'].forEach(function (eventName) {
                zone.addEventListener(eventName, function (event) {
                    event.preventDefault();
                    zone.classList.remove('is-dragover');
                });
            });

            zone.addEventListener('drop', function (event) {
                if (!input || !event.dataTransfer || !event.dataTransfer.files.length) {
                    return;
                }

                input.files = event.dataTransfer.files;
                zone.querySelector('strong').textContent = event.dataTransfer.files[0].name;
            });

            if (input) {
                input.addEventListener('change', function () {
                    if (input.files.length) {
                        zone.querySelector('strong').textContent = input.files[0].name;
                    }
                });
            }
        });

        document.querySelectorAll('.dms-quick-upload select[name="workspace_id"]').forEach(function (workspaceSelect) {
            var form = workspaceSelect.closest('form');
            var folderSelect = form ? form.querySelector('select[name="folder_id"]') : null;

            if (!folderSelect) {
                return;
            }

            var syncFolders = function () {
                var workspaceId = workspaceSelect.value;
                var firstVisible = null;

                Array.prototype.forEach.call(folderSelect.options, function (option) {
                    var optionWorkspace = option.getAttribute('data-workspace-id');
                    var visible = !option.value || !workspaceId || !optionWorkspace || optionWorkspace === workspaceId;
                    option.hidden = !visible;

                    if (visible && option.value && !firstVisible) {
                        firstVisible = option;
                    }
                });

                if (folderSelect.selectedOptions.length && folderSelect.selectedOptions[0].hidden) {
                    folderSelect.value = firstVisible ? firstVisible.value : '';
                }
            };

            workspaceSelect.addEventListener('change', syncFolders);
            syncFolders();
        });

        document.querySelectorAll('.dms-delete-form').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                var title = form.getAttribute('data-confirm-title') || 'Remover?';
                var text = form.getAttribute('data-confirm-text') || 'Esta acao nao deve ser feita sem confirmacao.';

                if (!window.Swal) {
                    if (!window.confirm(title)) {
                        event.preventDefault();
                    }
                    return;
                }

                event.preventDefault();
                Swal.fire({
                    title: title,
                    text: text,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Remover',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#dc3545'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

        document.addEventListener('click', function (event) {
            var trigger = event.target.closest('[data-lsg-open-modal]');

            if (!trigger) {
                return;
            }

            event.preventDefault();
            openDmsModal(trigger.getAttribute('data-lsg-open-modal'));
        });

        document.querySelectorAll('.dms-modal').forEach(function (modal) {
            modal.addEventListener('shown.bs.modal', removeDmsBackdrop);
            modal.addEventListener('show.bs.modal', function () {
                setTimeout(removeDmsBackdrop, 0);
            });
            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeDmsModal(modal);
                }
            });
        });

        if (window.jQuery && typeof jQuery.fn.modal === 'function') {
            jQuery('.dms-modal').on('show.bs.modal shown.bs.modal', function () {
                setTimeout(removeDmsBackdrop, 0);
            });
        }

        if (window.jQuery && jQuery.fn.DataTable) {
            jQuery('.document-lsg-datatable').each(function () {
                var table = jQuery(this);
                var columnCount = table.find('thead th').length;
                var invalid = false;

                if (jQuery.fn.DataTable.isDataTable(this)) {
                    return;
                }

                table.find('tbody tr').each(function () {
                    var cells = jQuery(this).children('td, th');
                    if (cells.filter('[colspan]').length || cells.length !== columnCount) {
                        invalid = true;
                    }
                });

                if (!columnCount || invalid) {
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
    });

    function openDmsModal(modalId) {
        var modal = document.getElementById(modalId);

        if (!modal) {
            return;
        }

        if (window.bootstrap && bootstrap.Modal && typeof bootstrap.Modal.getOrCreateInstance === 'function') {
            bootstrap.Modal.getOrCreateInstance(modal, { backdrop: false }).show();
            setTimeout(removeDmsBackdrop, 0);
            return;
        }

        if (window.bootstrap && typeof bootstrap.Modal === 'function') {
            new bootstrap.Modal(modal, { backdrop: false }).show();
            setTimeout(removeDmsBackdrop, 0);
            return;
        }

        if (window.jQuery && typeof jQuery.fn.modal === 'function') {
            jQuery(modal).modal({ backdrop: false, show: true });
            setTimeout(removeDmsBackdrop, 0);
            return;
        }

        modal.style.display = 'block';
        modal.removeAttribute('aria-hidden');
        modal.setAttribute('aria-modal', 'true');
        modal.classList.add('show');
        document.body.classList.add('modal-open');
        removeDmsBackdrop();
    }

    function removeDmsBackdrop() {
        document.querySelectorAll('.modal-backdrop').forEach(function (backdrop) {
            backdrop.remove();
        });
    }

    function closeDmsModal(modal) {
        if (!modal) {
            return;
        }

        if (window.bootstrap && bootstrap.Modal && typeof bootstrap.Modal.getInstance === 'function') {
            var instance = bootstrap.Modal.getInstance(modal);
            if (instance) {
                instance.hide();
                return;
            }
        }

        if (window.jQuery && typeof jQuery.fn.modal === 'function') {
            jQuery(modal).modal('hide');
            return;
        }

        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        modal.removeAttribute('aria-modal');
        modal.classList.remove('show');
        document.body.classList.remove('modal-open');
        removeDmsBackdrop();
    }
</script>
