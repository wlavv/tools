import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;
    const desktopBreakpoint = window.matchMedia('(min-width: 992px)');
    const themeStorageKey = 'webtools-theme';
    const sidebarStorageKey = 'webtools-sidebar-collapsed';

    const mobileTrigger = document.querySelector('[data-mobile-menu-toggle]');
    const mobileBackdrop = document.querySelector('[data-mobile-menu-close]');
    const desktopToggleButtons = document.querySelectorAll('[data-sidebar-toggle]');
    const themeToggleButtons = document.querySelectorAll('[data-theme-toggle]');

    const applyTheme = (theme) => {
        const safeTheme = theme === 'light' ? 'light' : 'dark';
        body.setAttribute('data-theme', safeTheme);
        body.classList.remove('theme-dark', 'theme-light');
        body.classList.add(`theme-${safeTheme}`);
        localStorage.setItem(themeStorageKey, safeTheme);
    };

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

    applyTheme(localStorage.getItem(themeStorageKey) || body.getAttribute('data-theme') || 'dark');
    applySidebarState(localStorage.getItem(sidebarStorageKey) === '1');
    setMobileMenu(false);

    desktopToggleButtons.forEach((button) => {
        button.addEventListener('click', () => {
            applySidebarState(!body.classList.contains('sidebar-collapsed'));
        });
    });

    themeToggleButtons.forEach((button) => {
        button.addEventListener('click', () => {
            applyTheme(body.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
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
});
