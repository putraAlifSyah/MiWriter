// Novel Writing Platform - Main JavaScript

// Language Toggle
function toggleLang() {
    const current = localStorage.getItem('miwriter-lang') || 'en';
    const next = current === 'en' ? 'id' : 'en';
    localStorage.setItem('miwriter-lang', next);
    document.documentElement.setAttribute('data-lang', next);
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

// AI Chat Widget
const AiChat = {
    isOpen: false,

    toggle() {
        this.isOpen = !this.isOpen;
        const panel = document.getElementById('ai-panel');
        const toggle = document.getElementById('ai-toggle');
        if (panel) {
            panel.style.display = this.isOpen ? 'flex' : 'none';
            toggle.textContent = this.isOpen ? '×' : 'AI';
            if (this.isOpen) {
                document.getElementById('ai-input').focus();
            }
        }
    },

    async send(e) {
        e.preventDefault();
        const input = document.getElementById('ai-input');
        const message = input.value.trim();
        if (!message) return;

        const bookId = document.getElementById('ai-book-select').value;
        const sendBtn = document.getElementById('ai-send-btn');

        // Add user message
        this.addMessage(message, 'user');
        input.value = '';
        sendBtn.disabled = true;

        // Show loading
        const loadingEl = this.addMessage('Thinking...', 'loading');

        try {
            const response = await fetch('/ai/ask', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    message: message,
                    book_id: bookId || null,
                }),
            });

            loadingEl.remove();

            const data = await response.json();

            if (!response.ok) {
                this.addMessage(data.error || 'Something went wrong.', 'error');
            } else {
                this.addMessage(data.response, 'ai');
            }
        } catch (err) {
            loadingEl.remove();
            this.addMessage('Network error. Please try again.', 'error');
        } finally {
            sendBtn.disabled = false;
            input.focus();
        }
    },

    addMessage(text, type) {
        const container = document.getElementById('ai-messages');
        const msg = document.createElement('div');
        msg.className = `ai-widget__msg ai-widget__msg--${type}`;
        msg.textContent = text;
        container.appendChild(msg);
        container.scrollTop = container.scrollHeight;
        return msg;
    }
};
