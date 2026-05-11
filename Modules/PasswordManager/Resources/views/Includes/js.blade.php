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

    const inlineCopyBtn = event.target.closest('[data-copy-value], [data-copy-remote]');
    if (inlineCopyBtn) {
        event.preventDefault();

        let value = inlineCopyBtn.getAttribute('data-copy-value') || '';
        const remoteUrl = inlineCopyBtn.getAttribute('data-copy-remote');
        const field = inlineCopyBtn.getAttribute('data-copy-field') || '';

        try {
            if (remoteUrl) {
                const token = inlineCopyBtn.getAttribute('data-csrf') || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const response = await fetch(remoteUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token,
                    },
                    body: JSON.stringify({ field }),
                });

                const payload = await response.json();

                if (!response.ok || !payload.success) {
                    throw new Error(payload.message || 'Remote copy failed');
                }

                value = payload.value || '';
            }

            await copyTextToClipboard(value);
            setCopiedState(inlineCopyBtn);
        } catch (error) {
            console.error('Copy failed', error);
        }

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

    try {
        await copyTextToClipboard(field.value || '');
        setCopiedState(copyBtn);
    } catch (error) {
        console.error('Copy failed', error);
    }
});

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
    const originalTitle = button.getAttribute('data-copy-title') || 'Copy';
    const copiedTitle = button.getAttribute('data-copied-title') || 'Copied';

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
    button.classList.add('pm-copy-success');

    setTimeout(() => {
        button.setAttribute('title', originalTitle);
        button.setAttribute('aria-label', originalTitle);
        button.classList.remove('pm-copy-success');
    }, 1200);
}

</script>
