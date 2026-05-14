<script>
document.addEventListener('DOMContentLoaded', function () {
    syncStreamDeckSections();
});

document.addEventListener('change', function (event) {
    if (event.target.matches('[data-sda-type]')) {
        syncStreamDeckSections();
    }
});

document.addEventListener('click', async function (event) {
    const copyTargetButton = event.target.closest('[data-copy-target]');
    if (copyTargetButton) {
        event.preventDefault();
        const targetId = copyTargetButton.getAttribute('data-copy-target');
        const target = document.getElementById(targetId);
        if (!target) return;
        await copyTextToClipboard(target.value || target.textContent || '');
        setCopiedState(copyTargetButton);
        return;
    }

    const copyValueButton = event.target.closest('[data-copy-value]');
    if (copyValueButton) {
        event.preventDefault();
        await copyTextToClipboard(copyValueButton.getAttribute('data-copy-value') || '');
        setCopiedState(copyValueButton);
    }
});

function syncStreamDeckSections() {
    const typeField = document.querySelector('[data-sda-type]');
    if (!typeField) return;

    const selectedType = typeField.value;

    document.querySelectorAll('[data-sda-section]').forEach(function (section) {
        const sectionType = section.getAttribute('data-sda-section');
        section.style.display = sectionType === selectedType ? '' : 'none';
    });
}

async function copyTextToClipboard(value) {
    if (navigator.clipboard && window.isSecureContext) {
        await navigator.clipboard.writeText(value);
        return;
    }

    const temp = document.createElement('textarea');
    temp.value = value;
    temp.setAttribute('readonly', '');
    temp.style.position = 'fixed';
    temp.style.left = '-9999px';
    temp.style.top = '0';
    temp.style.opacity = '0';

    document.body.appendChild(temp);
    temp.focus();
    temp.select();
    temp.setSelectionRange(0, value.length);

    const copied = document.execCommand('copy');
    document.body.removeChild(temp);

    if (!copied) {
        throw new Error('Fallback copy failed');
    }
}

function setCopiedState(button) {
    const icon = button.querySelector('i');
    const originalTitle = button.getAttribute('data-copy-title') || button.getAttribute('title') || 'Copiar';
    const copiedTitle = button.getAttribute('data-copied-title') || 'Copiado';

    if (icon) {
        const previousClasses = Array.from(icon.classList).filter((className) => className.startsWith('fa-'));
        previousClasses.forEach((className) => icon.classList.remove(className));
        icon.classList.add('fa-check');

        setTimeout(() => {
            icon.classList.remove('fa-check');
            previousClasses.forEach((className) => icon.classList.add(className));
        }, 1200);
    }

    button.setAttribute('title', copiedTitle);
    button.setAttribute('aria-label', copiedTitle);

    setTimeout(() => {
        button.setAttribute('title', originalTitle);
        button.setAttribute('aria-label', originalTitle);
    }, 1200);
}
</script>
