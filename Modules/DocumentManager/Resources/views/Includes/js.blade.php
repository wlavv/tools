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
</script>
