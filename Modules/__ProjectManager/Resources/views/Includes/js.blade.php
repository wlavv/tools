<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-group-toggle]').forEach(function (toggle) {
        toggle.addEventListener('click', function (e) {
            if (e.target.closest('a, button, form')) return;
            this.closest('.pm-group').classList.toggle('is-open');
        });
    });
    const tabs = document.querySelectorAll('.pm-tab');
    const panels = document.querySelectorAll('.pm-tab-panel');
    tabs.forEach(tab => {
        tab.addEventListener('click', function () {
            const name = this.dataset.tab;
            tabs.forEach(item => item.classList.remove('is-active'));
            panels.forEach(panel => panel.classList.remove('is-active'));
            this.classList.add('is-active');
            const panel = document.querySelector('.pm-tab-panel[data-panel="' + name + '"]');
            if (panel) panel.classList.add('is-active');
        });
    });
});
</script>
