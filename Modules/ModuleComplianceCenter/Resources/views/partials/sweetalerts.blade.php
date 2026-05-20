<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form.js-confirm').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (form.dataset.confirmed === '1' || typeof Swal === 'undefined') {
                return;
            }
            event.preventDefault();
            Swal.fire({
                title: form.dataset.title || 'Confirm action?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Confirm',
                cancelButtonText: 'Cancel'
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.dataset.confirmed = '1';
                    form.submit();
                }
            });
        });
    });
});
</script>
