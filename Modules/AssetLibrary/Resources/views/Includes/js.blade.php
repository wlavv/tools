<script>
document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.querySelector('[data-al-file-input]');
    const previewTarget = document.querySelector('[data-al-file-preview-name]');
    if (fileInput && previewTarget) {
        fileInput.addEventListener('change', function () {
            const file = this.files && this.files[0] ? this.files[0] : null;
            previewTarget.textContent = file ? file.name : previewTarget.dataset.emptyText;
        });
    }
});
</script>
