@extends('layouts.app')

@push('styles')
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<style>
    #editor-container .ql-editor { min-height: 60vh; font-family: var(--font-family); font-size: var(--font-size-base); line-height: var(--line-height-base); }
    #editor-container.fullscreen { position: fixed; top: 0; left: 0; width: 100%; height: 100vh; z-index: 10000; background: #fff; display: flex; flex-direction: column; }
    #editor-container.fullscreen .ql-container { flex: 1; overflow-y: auto; }
    #save-status.saving { color: var(--color-accent); }
    #save-status.saved { color: var(--color-success); }
    #save-status.error { color: var(--color-danger); }
</style>
@endpush

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
    <div>
        <a href="{{ route('books.show', $book) }}" class="nwp-text-sm">&larr; Back to {{ $book->title }}</a>
        <h1 class="nwp-heading nwp-mt-1">{{ $chapter->title }}</h1>
    </div>
    <div style="display:flex; gap:8px; align-items:center;">
        <span id="save-status" class="nwp-text-sm nwp-text-muted"></span>
        <button onclick="EditorModule.toggleFullscreen()" class="nwp-btn nwp-btn--sm nwp-btn--secondary">Fullscreen</button>
    </div>
</div>

<div id="editor-container" class="nwp-editor-container">
    <div id="editor">{!! $chapter->content_html !!}</div>
    <div class="nwp-editor__footer">
        <span id="word-count">{{ number_format($chapter->word_count) }} words</span>
        <span id="last-saved" class="nwp-text-sm nwp-text-muted"></span>
    </div>
</div>

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
const EditorModule = {
    quill: null,
    chapterId: {{ $chapter->id }},
    saveEndpoint: '{{ route("chapters.content", [$book, $chapter]) }}',
    isDirty: false,
    isSaving: false,
    debounceTimer: null,
    retryCount: 0,

    init() {
        this.quill = new Quill('#editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ header: [1, 2, 3, false] }],
                    ['blockquote'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    ['clean']
                ],
                history: { maxStack: 50 }
            }
        });

        // Auto-save 2 detik setelah berhenti mengetik
        this.quill.on('text-change', () => {
            this.isDirty = true;
            this.updateWordCount();
            this.setStatus('Unsaved changes...', '');

            // Clear timer sebelumnya, set timer baru
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => {
                if (this.isDirty && !this.isSaving) {
                    this.save();
                }
            }, 2000); // 2 detik setelah berhenti ketik
        });

        // Manual save (Ctrl+S / Cmd+S)
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                clearTimeout(this.debounceTimer);
                this.save();
            }
        });

        // Save sebelum keluar halaman
        window.addEventListener('beforeunload', (e) => {
            if (this.isDirty) {
                localStorage.setItem('unsaved_' + this.chapterId, JSON.stringify(this.quill.getContents()));
                e.returnValue = 'You have unsaved changes.';
            }
        });

        // Cek unsaved content dari localStorage
        const unsaved = localStorage.getItem('unsaved_' + this.chapterId);
        if (unsaved) {
            if (confirm('Ada perubahan yang belum tersimpan. Restore?')) {
                this.quill.setContents(JSON.parse(unsaved));
                this.isDirty = true;
            }
            localStorage.removeItem('unsaved_' + this.chapterId);
        }
    },

    setStatus(text, className) {
        const el = document.getElementById('save-status');
        el.textContent = text;
        el.className = 'nwp-text-sm ' + className;
    },

    async save() {
        if (this.isSaving) return;
        this.isSaving = true;
        this.setStatus('Saving...', 'saving');

        const delta = this.quill.getContents();
        const html = this.quill.root.innerHTML;

        try {
            const response = await fetch(this.saveEndpoint, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ content_delta: delta, content_html: html }),
            });

            if (response.status === 401) {
                localStorage.setItem('unsaved_' + this.chapterId, JSON.stringify(delta));
                window.location.href = '/login?expired=1';
                return;
            }

            if (!response.ok) throw new Error('Save failed');

            const data = await response.json();
            this.isDirty = false;
            this.retryCount = 0;
            localStorage.removeItem('unsaved_' + this.chapterId);

            const time = new Date().toLocaleTimeString();
            this.setStatus('✓ Saved', 'saved');
            document.getElementById('last-saved').textContent = 'Last saved: ' + time;
        } catch (error) {
            this.retryCount++;
            this.setStatus('✗ Save failed', 'error');
            localStorage.setItem('unsaved_' + this.chapterId, JSON.stringify(delta));
        } finally {
            this.isSaving = false;
        }
    },

    updateWordCount() {
        const text = this.quill.getText().trim();
        const count = text === '' ? 0 : text.split(/\s+/).filter(Boolean).length;
        document.getElementById('word-count').textContent = count.toLocaleString() + ' word' + (count !== 1 ? 's' : '');
    },

    toggleFullscreen() {
        document.getElementById('editor-container').classList.toggle('fullscreen');
    }
};

document.addEventListener('DOMContentLoaded', () => EditorModule.init());
</script>
@endpush
@endsection
