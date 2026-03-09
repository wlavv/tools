<script>
document.addEventListener('click', function (e) {
    const trigger = e.target.closest('[data-ai-open-provider]');
    if (trigger) {
        const provider = trigger.getAttribute('data-ai-open-provider');
        const providerField = document.querySelector('#credential_provider');
        if (providerField) providerField.value = provider;
    }
});
</script>
