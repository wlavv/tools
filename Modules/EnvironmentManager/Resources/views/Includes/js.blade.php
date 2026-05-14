<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-environment-manager-autofocus]').forEach(function (input) {
            if (input.value) {
                input.focus();
            }
        });
    });
</script>
