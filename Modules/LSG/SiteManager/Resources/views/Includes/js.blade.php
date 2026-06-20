<script>
document.addEventListener('submit', function(event){
    const form = event.target.closest('[data-confirm]');
    if (!form) return;
    const message = form.getAttribute('data-confirm') || 'Confirmar acao?';
    if (window.Swal) {
        event.preventDefault();
        Swal.fire({title: message, icon: 'warning', showCancelButton: true, confirmButtonText: 'Sim', cancelButtonText: 'Cancelar'})
            .then(result => { if (result.isConfirmed) form.submit(); });
    } else if (!confirm(message)) {
        event.preventDefault();
    }
});
</script>
