<script>
document.addEventListener('click', function(event) {
    const tab = event.target.closest('[data-dbx-tab]');
    if (!tab) {
        return;
    }

    const target = tab.getAttribute('data-dbx-tab');
    const container = tab.closest('[data-dbx-tabs-root]') || document;

    container.querySelectorAll('[data-dbx-tab]').forEach(function(button) {
        button.classList.toggle('is-active', button === tab);
    });

    container.querySelectorAll('[data-dbx-panel]').forEach(function(panel) {
        panel.classList.toggle('is-active', panel.getAttribute('data-dbx-panel') === target);
    });
});
</script>
