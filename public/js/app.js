// Novel Writing Platform - Main JavaScript

// Language Toggle
function toggleLang() {
    const current = localStorage.getItem('miwriter-lang') || 'en';
    const next = current === 'en' ? 'id' : 'en';
    localStorage.setItem('miwriter-lang', next);
    document.body.setAttribute('data-lang', next);
    updateLangToggle(next);
}

function updateLangToggle(lang) {
    const btn = document.getElementById('lang-toggle-sidebar');
    if (btn) btn.textContent = lang.toUpperCase();
}

// Dark Mode
function toggleDarkMode() {
    const html = document.documentElement;
    const isDark = html.getAttribute('data-theme') === 'dark';

    if (isDark) {
        html.removeAttribute('data-theme');
        localStorage.setItem('theme', 'light');
        updateThemeToggle(false);
    } else {
        html.setAttribute('data-theme', 'dark');
        localStorage.setItem('theme', 'dark');
        updateThemeToggle(true);
    }
}

function updateThemeToggle(isDark) {
    const btn = document.getElementById('theme-toggle');
    const label = document.getElementById('theme-label');
    if (btn) btn.textContent = isDark ? '☀️' : '🌙';
    if (label) label.textContent = isDark ? 'Light Mode' : 'Dark Mode';
}

// Apply saved theme on page load
(function() {
    const saved = localStorage.getItem('theme');
    if (saved === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
    }
})();

// Sidebar Toggle
document.addEventListener('DOMContentLoaded', function() {
    // Update toggle button state
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    updateThemeToggle(isDark);

    // Update lang toggle
    const lang = localStorage.getItem('miwriter-lang') || 'en';
    updateLangToggle(lang);

    const toggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');

    if (toggle && sidebar) {
        toggle.addEventListener('click', function() {
            sidebar.classList.toggle('nwp-sidebar--open');
        });

        if (overlay) {
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('nwp-sidebar--open');
            });
        }
    }
});

// Toast Notifications
const NotificationModule = {
    container: null,

    init() {
        this.container = document.getElementById('toast-container');
    },

    show(message, type = 'info', duration = 3000) {
        if (!this.container) this.init();
        if (!this.container) return;

        const toast = document.createElement('div');
        toast.className = `nwp-toast nwp-toast--${type}`;
        toast.textContent = message;
        this.container.appendChild(toast);

        if (type !== 'error') {
            setTimeout(() => {
                toast.remove();
            }, duration);
        } else {
            toast.style.cursor = 'pointer';
            toast.addEventListener('click', () => toast.remove());
        }
    },

    success(message) { this.show(message, 'success'); },
    error(message) { this.show(message, 'error', 0); },
    info(message) { this.show(message, 'info'); },
};

// Local Storage Module
const LocalStorageModule = {
    saveUnsavedContent(chapterId, content) {
        try {
            localStorage.setItem(`unsaved_${chapterId}`, content);
        } catch (e) {
            console.warn('LocalStorage full or unavailable');
        }
    },

    getUnsavedContent(chapterId) {
        return localStorage.getItem(`unsaved_${chapterId}`);
    },

    clearUnsavedContent(chapterId) {
        localStorage.removeItem(`unsaved_${chapterId}`);
    }
};
