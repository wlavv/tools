<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-investments-confirm]').forEach(function (button) {
            button.addEventListener('click', function (event) {
                if (!confirm(button.getAttribute('data-investments-confirm'))) {
                    event.preventDefault();
                }
            });
        });
    });
</script>
