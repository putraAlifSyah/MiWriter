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

// AI Chat Widget (Enhanced with History + Multi-Chapter Focus)
const AiChat = {
    isOpen: false,
    currentChapters: [],           // chapters of currently selected book
    selectedChapterIds: [],        // currently checked chapter ids
    isLoadingHistory: false,

    toggle() {
        this.isOpen = !this.isOpen;
        const panel = document.getElementById('ai-panel');
        const toggle = document.getElementById('ai-toggle');

        if (panel) {
            panel.style.display = this.isOpen ? 'flex' : 'none';
            toggle.textContent = this.isOpen ? '×' : 'AI';

            if (this.isOpen) {
                // Load history the first time we open
                if (document.getElementById('ai-messages').children.length === 0) {
                    this.loadHistory();
                }
                document.getElementById('ai-input').focus();
            }
        }
    },

    // Called when user changes the book dropdown
    async onBookChange() {
        const bookId = document.getElementById('ai-book-select').value;
        const chapterSelector = document.getElementById('ai-chapter-selector');
        const chapterList = document.getElementById('ai-chapter-list');

        this.selectedChapterIds = [];
        chapterList.innerHTML = '';

        if (!bookId) {
            chapterSelector.style.display = 'none';
            this.updateChapterCount();
            return;
        }

        chapterSelector.style.display = 'block';

        try {
            const res = await fetch(`/books/${bookId}/chapters-list`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                }
            });

            const data = await res.json();
            this.currentChapters = data.chapters || [];

            this.renderChapterList(this.currentChapters);
            this.updateChapterCount();
        } catch (e) {
            console.error('Failed to load chapters', e);
            chapterList.innerHTML = '<div style="padding:6px;font-size:12px;color:#888">Gagal memuat daftar chapter.</div>';
        }
    },

    // Render the checkbox list of chapters
    renderChapterList(chapters) {
        const container = document.getElementById('ai-chapter-list');
        container.innerHTML = '';

        if (chapters.length === 0) {
            container.innerHTML = '<div style="padding:6px;font-size:12px;color:#888">Belum ada chapter.</div>';
            return;
        }

        chapters.forEach(ch => {
            const div = document.createElement('div');
            div.className = 'ai-widget__chapter-item';
            div.innerHTML = `
                <input type="checkbox" id="ch-${ch.id}" value="${ch.id}">
                <label for="ch-${ch.id}" class="ai-widget__chapter-item-label">
                    ${ch.title}
                    <span class="word-count">(${ch.word_count} kata)</span>
                </label>
            `;

            const checkbox = div.querySelector('input');
            checkbox.addEventListener('change', () => {
                this.updateSelectedChapters();
            });

            container.appendChild(div);
        });
    },

    // Filter chapters based on search input
    filterChapters() {
        const search = document.getElementById('ai-chapter-search').value.toLowerCase().trim();
        const container = document.getElementById('ai-chapter-list');
        const items = container.querySelectorAll('.ai-widget__chapter-item');

        items.forEach(item => {
            const label = item.querySelector('.ai-widget__chapter-item-label');
            if (!label) return;

            const text = label.textContent.toLowerCase();
            item.style.display = text.includes(search) ? '' : 'none';
        });
    },

    // Update internal selectedChapterIds from checkboxes
    updateSelectedChapters() {
        const container = document.getElementById('ai-chapter-list');
        const checked = container.querySelectorAll('input[type="checkbox"]:checked');
        
        this.selectedChapterIds = Array.from(checked).map(cb => parseInt(cb.value));
        this.updateChapterCount();
    },

    updateChapterCount() {
        const el = document.getElementById('ai-chapter-count');
        if (!el) return;

        const count = this.selectedChapterIds.length;

        if (count === 0) {
            el.textContent = 'Semua chapter';
        } else {
            el.textContent = `${count} dipilih`;
        }
    },

    // Load chat history from backend (max 20)
    async loadHistory() {
        if (this.isLoadingHistory) return;
        this.isLoadingHistory = true;

        const container = document.getElementById('ai-messages');
        container.innerHTML = '<div class="ai-widget__msg ai-widget__msg--loading">Memuat riwayat chat...</div>';

        try {
            const res = await fetch('/ai/history', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                }
            });

            const data = await res.json();
            container.innerHTML = '';

            if (data.messages && data.messages.length > 0) {
                data.messages.forEach(msg => {
                    if (msg.role === 'user' || msg.role === 'assistant') {
                        this.addMessage(msg.content, msg.role === 'user' ? 'user' : 'ai', true);
                    }
                });

                // Show info about limited history
                this.showHistoryInfo(data.total_shown, data.limit);
            } else {
                const welcome = document.createElement('div');
                welcome.className = 'ai-widget__msg ai-widget__msg--ai';
                welcome.style.opacity = '0.7';
                welcome.textContent = 'Halo! Ada yang bisa saya bantu dengan cerita kamu hari ini?';
                container.appendChild(welcome);
            }
        } catch (e) {
            container.innerHTML = '<div class="ai-widget__msg ai-widget__msg--error">Gagal memuat riwayat chat.</div>';
        } finally {
            this.isLoadingHistory = false;
            container.scrollTop = container.scrollHeight;
        }
    },

    showHistoryInfo(shown, limit) {
        const info = document.getElementById('ai-history-info');
        if (!info) return;

        info.innerHTML = `Menampilkan ${shown} pesan terakhir`;
        if (shown >= limit) {
            info.innerHTML += ` <span style="opacity:0.6">(riwayat dibatasi)</span>`;
        }
    },

    async send(e) {
        e.preventDefault();
        const input = document.getElementById('ai-input');
        const message = input.value.trim();
        if (!message) return;

        const bookId = document.getElementById('ai-book-select').value;
        const sendBtn = document.getElementById('ai-send-btn');

        // Add user message immediately
        this.addMessage(message, 'user');
        input.value = '';
        sendBtn.disabled = true;

        const loadingEl = this.addMessage('Sedang berpikir...', 'loading');

        // Prepare payload
        const payload = {
            message: message,
            book_id: bookId || null,
            chapter_ids: this.selectedChapterIds.length > 0 ? this.selectedChapterIds : null,
        };

        try {
            const response = await fetch('/ai/ask', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            });

            loadingEl.remove();

            const data = await response.json();

            if (!response.ok) {
                this.addMessage(data.error || 'Terjadi kesalahan.', 'error');
            } else {
                this.addMessage(data.response, 'ai');

                if (data.action && data.created) {
                    this.addActionMessage(data.created);
                }
            }
        } catch (err) {
            loadingEl.remove();
            this.addMessage('Koneksi bermasalah. Coba lagi.', 'error');
        } finally {
            sendBtn.disabled = false;
            input.focus();
        }
    },

    addMessage(text, type, skipScroll = false) {
        const container = document.getElementById('ai-messages');
        const msg = document.createElement('div');
        msg.className = `ai-widget__msg ai-widget__msg--${type}`;
        msg.textContent = text;
        container.appendChild(msg);

        if (!skipScroll) {
            container.scrollTop = container.scrollHeight;
        }
        return msg;
    },

    addActionMessage(created) {
        const container = document.getElementById('ai-messages');
        const msg = document.createElement('div');
        msg.className = 'ai-widget__msg ai-widget__msg--action';

        let html = `<strong>✓ Dibuat:</strong> <strong>${created.name}</strong>`;

        if (created.type === 'character' && created.role) {
            html += ` <span style="opacity:0.75">(${created.role})</span>`;
        }

        if (created.preview) {
            html += `<div style="margin-top:4px; font-size:11px; opacity:0.85; line-height:1.3;">${created.preview}...</div>`;
        }

        msg.innerHTML = html;
        container.appendChild(msg);
        container.scrollTop = container.scrollHeight;
    },

    // Clear all chat history
    async clearHistory() {
        if (!confirm('Hapus semua riwayat chat dengan AI?')) return;

        try {
            await fetch('/ai/history', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            });

            // Clear UI
            document.getElementById('ai-messages').innerHTML = '';
            document.getElementById('ai-history-info').innerHTML = '';

            // Show fresh welcome
            const welcome = document.createElement('div');
            welcome.className = 'ai-widget__msg ai-widget__msg--ai';
            welcome.style.opacity = '0.7';
            welcome.textContent = 'Riwayat chat sudah dibersihkan. Ada yang bisa saya bantu?';
            document.getElementById('ai-messages').appendChild(welcome);

        } catch (e) {
            alert('Gagal menghapus riwayat chat.');
        }
    }
};
