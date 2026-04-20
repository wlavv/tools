<script>
document.addEventListener('click', async function (event) {
    const toggleBtn = event.target.closest('[data-secret-toggle]');
    if (toggleBtn) {
        event.preventDefault();

        const fieldId = toggleBtn.getAttribute('data-secret-toggle');
        const field = document.getElementById(fieldId);

        if (!field) {
            return;
        }

        const icon = toggleBtn.querySelector('i');

        if (field.tagName === 'TEXTAREA') {
            const isHidden = field.dataset.hidden !== 'false';
            field.dataset.hidden = isHidden ? 'false' : 'true';
            field.classList.toggle('pm-secret-textarea--revealed', isHidden);

            const nextTitle = isHidden
                ? (toggleBtn.getAttribute('data-hide-title') || 'Hide')
                : (toggleBtn.getAttribute('data-show-title') || 'Show');

            toggleBtn.setAttribute('title', nextTitle);
            toggleBtn.setAttribute('aria-label', nextTitle);

            if (icon) {
                icon.classList.remove('fa-eye', 'fa-eye-slash');
                icon.classList.add(isHidden ? 'fa-eye-slash' : 'fa-eye');
            }

            return;
        }

        const isHidden = field.type === 'password';
        field.type = isHidden ? 'text' : 'password';

        if (icon) {
            icon.classList.remove('fa-eye', 'fa-eye-slash');
            icon.classList.add(isHidden ? 'fa-eye-slash' : 'fa-eye');
        }

        const nextTitle = isHidden
            ? (toggleBtn.getAttribute('data-hide-title') || 'Hide')
            : (toggleBtn.getAttribute('data-show-title') || 'Show');

        toggleBtn.setAttribute('title', nextTitle);
        toggleBtn.setAttribute('aria-label', nextTitle);

        return;
    }

    const copyBtn = event.target.closest('[data-copy-target]');
    if (!copyBtn) {
        return;
    }

    event.preventDefault();

    const fieldId = copyBtn.getAttribute('data-copy-target');
    const field = document.getElementById(fieldId);

    if (!field) {
        return;
    }

    const value = field.value || '';
    const icon = copyBtn.querySelector('i');
    const originalTitle = copyBtn.getAttribute('data-copy-title') || 'Copy';
    const copiedTitle = copyBtn.getAttribute('data-copied-title') || 'Copied';

    try {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(value);
        } else {
            const temp = document.createElement(field.tagName === 'TEXTAREA' ? 'textarea' : 'input');

            if (temp.tagName === 'INPUT') {
                temp.type = 'text';
            }

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

        if (icon) {
            icon.classList.remove('fa-copy');
            icon.classList.add('fa-check');

            setTimeout(() => {
                icon.classList.remove('fa-check');
                icon.classList.add('fa-copy');
            }, 1200);
        }

        copyBtn.setAttribute('title', copiedTitle);
        copyBtn.setAttribute('aria-label', copiedTitle);

        setTimeout(() => {
            copyBtn.setAttribute('title', originalTitle);
            copyBtn.setAttribute('aria-label', originalTitle);
        }, 1200);
    } catch (error) {
        console.error('Copy failed', error);
    }
});
</script>
