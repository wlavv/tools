<script>
document.addEventListener('click', async function (event) {
    const providerTrigger = event.target.closest('[data-ai-open-provider]');
    if (providerTrigger) {
        const provider = providerTrigger.getAttribute('data-ai-open-provider');
        const providerField = document.querySelector('#credential_provider');
        if (providerField) {
            providerField.value = provider;
        }
    }

    const toggleBtn = event.target.closest('[data-secret-toggle]');
    if (toggleBtn) {
        event.preventDefault();

        const fieldId = toggleBtn.getAttribute('data-secret-toggle');
        const field = document.getElementById(fieldId);
        if (!field) return;

        const icon = toggleBtn.querySelector('i');
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
    if (!copyBtn) return;

    event.preventDefault();

    const fieldId = copyBtn.getAttribute('data-copy-target');
    const field = document.getElementById(fieldId);
    if (!field) return;

    const value = field.value || field.textContent || '';
    const icon = copyBtn.querySelector('i');
    const originalTitle = copyBtn.getAttribute('data-copy-title') || '{{ __('ai-consensus::actions.copy') }}';
    const copiedTitle = copyBtn.getAttribute('data-copied-title') || '{{ __('ai-consensus::actions.copied') }}';

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
            if (!copied) throw new Error('Fallback copy failed');
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

document.addEventListener('submit', function (event) {
    const form = event.target.closest('[data-ai-loading-form]');
    if (!form) return;

    form.classList.add('ai-loading');

    form.querySelectorAll('button[type="submit"], .lsg-action-btn[type="submit"], input[type="submit"]').forEach(function (button) {
        if (button.dataset.loadingApplied === '1') {
            return;
        }

        button.dataset.loadingApplied = '1';
        button.disabled = true;

        const label = button.getAttribute('data-loading-label') || '{{ __('ai-consensus::actions.loading') }}';
        const labelNode = button.querySelector('.lsg-action-btn__label');
        const iconNode = button.querySelector('.lsg-action-btn__icon');

        if (labelNode) {
            labelNode.dataset.originalText = labelNode.textContent;
            labelNode.textContent = label;
        } else {
            button.dataset.originalText = button.innerHTML;
            button.innerHTML = '<span class="ai-loading-spinner"></span> ' + label;
        }

        if (iconNode) {
            iconNode.innerHTML = '<span class="ai-loading-spinner"></span>';
        }
    });
});
</script>
