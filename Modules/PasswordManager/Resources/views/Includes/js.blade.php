<script>
document.addEventListener('click', function (event) {
    var target = event.target;

    if (target.matches('[data-password-toggle]')) {
        var inputId = target.getAttribute('data-password-toggle');
        var input = document.getElementById(inputId);

        if (!input) {
            return;
        }

        input.type = input.type === 'password' ? 'text' : 'password';
        target.innerText = input.type === 'password' ? 'Mostrar' : 'Ocultar';
    }
});
</script>
