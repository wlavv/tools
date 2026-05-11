import './bootstrap';

const themeStorageKey = 'webtools-theme';

const normalizeTheme = (theme) => (theme === 'light' ? 'light' : 'dark');

const readStoredTheme = () => {
    try {
        return localStorage.getItem(themeStorageKey);
    } catch (e) {
        return null;
    }
};

const writeStoredTheme = (theme) => {
    try {
        localStorage.setItem(themeStorageKey, theme);
    } catch (e) {
        // localStorage can be unavailable in hardened/private browser contexts.
    }
};

const syncThemeTarget = (target, theme) => {
    if (!target) {
        return;
    }

    target.setAttribute('data-theme', theme);
    target.setAttribute('data-bs-theme', theme);
    target.classList.remove('theme-dark', 'theme-light');
    target.classList.add(`theme-${theme}`);
    target.style.colorScheme = theme;
};

const applyTheme = (theme, persist = true, announce = true) => {
    const safeTheme = normalizeTheme(theme);

    syncThemeTarget(document.documentElement, safeTheme);
    syncThemeTarget(document.body, safeTheme);

    if (persist) {
        writeStoredTheme(safeTheme);
    }

    if (announce) {
        window.dispatchEvent(new CustomEvent('lsg:theme-changed', {
            detail: { theme: safeTheme },
        }));
    }

    return safeTheme;
};

const currentTheme = () => normalizeTheme(
    document.body?.getAttribute('data-theme')
    || document.documentElement.getAttribute('data-theme')
    || readStoredTheme()
    || window.__LSG_INITIAL_THEME__
    || 'dark'
);

window.LSGTheme = Object.assign(window.LSGTheme || {}, {
    apply: applyTheme,
    current: currentTheme,
    storageKey: themeStorageKey,
});

applyTheme(
    readStoredTheme()
    || window.__LSG_INITIAL_THEME__
    || document.documentElement.getAttribute('data-theme')
    || document.body?.getAttribute('data-theme')
    || 'dark',
    false,
    false
);

document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;
    const desktopBreakpoint = window.matchMedia('(min-width: 992px)');
    const sidebarStorageKey = 'webtools-sidebar-collapsed';

    const mobileTrigger = document.querySelector('[data-mobile-menu-toggle]');
    const mobileBackdrop = document.querySelector('[data-mobile-menu-close]');
    const desktopToggleButtons = document.querySelectorAll('[data-sidebar-toggle]');
    const themeToggleButtons = document.querySelectorAll('[data-theme-toggle]');

    const applySidebarState = (collapsed) => {
        if (collapsed) {
            body.classList.add('sidebar-collapsed');
            localStorage.setItem(sidebarStorageKey, '1');
        } else {
            body.classList.remove('sidebar-collapsed');
            localStorage.setItem(sidebarStorageKey, '0');
        }
    };

    const setMobileMenu = (open) => {
        body.classList.toggle('mobile-menu-open', !!open);
        if (mobileTrigger) {
            const icon = mobileTrigger.querySelector('i');
            if (icon) {
                icon.className = open ? 'fa-solid fa-xmark' : 'fa-solid fa-bars';
            }
        }
    };

    applyTheme(readStoredTheme() || body.getAttribute('data-theme') || 'dark');
    applySidebarState(localStorage.getItem(sidebarStorageKey) === '1');
    setMobileMenu(false);

    desktopToggleButtons.forEach((button) => {
        button.addEventListener('click', () => {
            applySidebarState(!body.classList.contains('sidebar-collapsed'));
        });
    });

    themeToggleButtons.forEach((button) => {
        button.addEventListener('click', () => {
            applyTheme(currentTheme() === 'dark' ? 'light' : 'dark');
        });
    });

    if (mobileTrigger) {
        mobileTrigger.addEventListener('click', () => {
            setMobileMenu(!body.classList.contains('mobile-menu-open'));
        });
    }

    if (mobileBackdrop) {
        mobileBackdrop.addEventListener('click', () => setMobileMenu(false));
    }

    window.addEventListener('resize', () => {
        if (desktopBreakpoint.matches) {
            setMobileMenu(false);
        }
    });

    window.addEventListener('storage', (event) => {
        if (event.key === themeStorageKey) {
            applyTheme(event.newValue || 'dark', false);
        }
    });
});
