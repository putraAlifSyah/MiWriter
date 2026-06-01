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

    /* Focus Mode Styles */
    body.focus-mode-active { background: var(--color-bg-primary); }
    body.focus-mode-active .nwp-sidebar,
    body.focus-mode-active .nwp-editor__footer,
    body.focus-mode-active .ai-widget,
    body.focus-mode-active .nwp-sidebar-toggle { display: none !important; }
    
    body.focus-mode-active .nwp-main { margin-left: 0 !important; max-width: 800px; margin: 0 auto; padding-top: 60px; }
    
    body.focus-mode-active .focus-hidden { opacity: 0; pointer-events: none; transition: opacity 0.3s; }
    body.focus-mode-active:hover .focus-hidden { opacity: 1; pointer-events: auto; }
    
    body.focus-mode-active .ql-editor { font-size: 18px; line-height: 1.8; padding-bottom: 50vh; }
    body.focus-mode-active .ql-editor > * { color: var(--color-text-muted); transition: color 0.3s; }
    body.focus-mode-active .ql-editor > *.focus-active { color: var(--color-text-primary); }

    .editor-layout { display: flex; gap: 16px; align-items: flex-start; position: relative; }
    .editor-main { flex: 1; min-width: 0; }
    .editor-sidebar { width: 300px; display: none; flex-direction: column; gap: 16px; position: sticky; top: 16px; max-height: calc(100vh - 32px); overflow-y: auto; }
    .editor-sidebar.active { display: flex; }
</style>
@endpush

@section('content')
<div class="focus-hidden" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
    <div style="flex:1;">
        <a href="{{ route('books.show', $book) }}" class="nwp-text-sm">&larr; Back to {{ $book->title }}</a>
        <div style="display:flex; align-items:center; gap:8px;" class="nwp-mt-1">
            <input type="text" id="chapter-title-input" value="{{ $chapter->title }}" class="nwp-heading" style="border:none; background:transparent; font-size:var(--font-size-2xl); font-weight:700; color:var(--color-text-primary); flex:1; outline:none;" onblur="EditorModule.updateTitle(this.value)">
            <form method="POST" action="{{ route('chapters.destroy', [$book, $chapter]) }}" onsubmit="return confirm('Hapus bab ini? (Bisa dikembalikan dari Trash)')" style="margin:0;">
                @csrf @method('DELETE')
                <button type="submit" class="nwp-btn nwp-btn--sm nwp-btn--danger" style="padding:4px 8px; font-size:12px;" title="Delete Chapter">🗑️</button>
            </form>
        </div>
    </div>
    <div style="display:flex; gap:8px; align-items:center;">
        <span id="save-status" class="nwp-text-sm nwp-text-muted"></span>
        <button onclick="BetaReaderModule.openModal()" class="nwp-btn nwp-btn--sm nwp-btn--primary" style="background:var(--color-accent);">🤖 Beta Reader</button>
        <button onclick="SnapshotModule.openModal()" class="nwp-btn nwp-btn--sm nwp-btn--secondary">Snapshots</button>
        <button onclick="EditorModule.toggleSidebar()" class="nwp-btn nwp-btn--sm nwp-btn--secondary">Panel</button>
        <button onclick="EditorModule.toggleFocusMode()" class="nwp-btn nwp-btn--sm nwp-btn--secondary">Focus</button>
        <button onclick="EditorModule.toggleFullscreen()" class="nwp-btn nwp-btn--sm nwp-btn--secondary">Fullscreen</button>
    </div>
</div>

<!-- Inline AI Toolbar -->
<div id="inline-ai-toolbar" style="display:none; position:absolute; z-index:100; background:var(--color-bg-secondary); border:1px solid var(--color-border); border-radius:var(--radius-md); box-shadow:0 4px 12px rgba(0,0,0,0.1); padding:4px; display:flex; gap:4px;">
    <button onclick="InlineAiModule.edit('rewrite')" class="nwp-btn nwp-btn--sm nwp-btn--ghost" style="font-size:11px; padding:4px 8px;">Rewrite</button>
    <button onclick="InlineAiModule.edit('expand')" class="nwp-btn nwp-btn--sm nwp-btn--ghost" style="font-size:11px; padding:4px 8px;">Expand</button>
    <button onclick="InlineAiModule.edit('grammar')" class="nwp-btn nwp-btn--sm nwp-btn--ghost" style="font-size:11px; padding:4px 8px;">Grammar</button>
    <div style="width:1px; background:var(--color-border); margin:4px 0;"></div>
    <input type="text" id="inline-ai-custom" placeholder="Custom prompt..." class="nwp-input nwp-input--sm" style="width:120px; font-size:11px; padding:2px 6px; border:none; background:var(--color-bg-primary);">
    <button onclick="InlineAiModule.customEdit()" class="nwp-btn nwp-btn--sm nwp-btn--primary" style="font-size:11px; padding:4px 8px;">Go</button>
</div>

<!-- Beta Reader Modal -->
<div id="beta-reader-modal" class="nwp-modal-overlay" style="z-index:90000;">
    <div class="nwp-modal" style="max-width:800px; max-height:90vh; display:flex; flex-direction:column;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <h3 class="nwp-modal__title" style="margin:0;">🤖 AI Beta Reader</h3>
            <button type="button" onclick="BetaReaderModule.closeModal()" style="background:none; border:none; cursor:pointer; font-size:20px; color:var(--color-text-muted);">&times;</button>
        </div>
        
        <div style="margin-bottom:16px;">
            <p class="nwp-text-sm nwp-text-muted">AI akan membaca bab ini dan memberikan kritik mengenai Pacing, aturan "Show Don't Tell", masalah kontinuitas, dan konsistensi karakter.</p>
            <button id="btn-run-beta-reader" onclick="BetaReaderModule.run()" class="nwp-btn nwp-btn--primary nwp-mt-2" style="background:var(--color-accent);">Mulai Analisis</button>
        </div>

        <div id="beta-reader-content" style="flex:1; overflow-y:auto; border:1px solid var(--color-border); border-radius:var(--radius-md); padding:16px; display:none; flex-direction:column; gap:16px; background:var(--color-bg-secondary);">
            <div>
                <h4 style="color:var(--color-accent); margin-bottom:8px;">⏱️ Pacing (Tempo Cerita)</h4>
                <div id="br-pacing" class="nwp-text-sm" style="line-height:1.6;"></div>
            </div>
            <div>
                <h4 style="color:var(--color-accent); margin-bottom:8px;">🎭 Show, Don't Tell</h4>
                <div id="br-showdonttell" class="nwp-text-sm" style="line-height:1.6;"></div>
            </div>
            <div>
                <h4 style="color:var(--color-accent); margin-bottom:8px;">🔗 Continuity & Consistency</h4>
                <div id="br-continuity" class="nwp-text-sm" style="line-height:1.6;"></div>
            </div>
            <div>
                <h4 style="color:var(--color-accent); margin-bottom:8px;">👤 Character Consistency</h4>
                <div id="br-character-consistency" class="nwp-text-sm" style="line-height:1.6;"></div>
            </div>
        </div>
        <div id="beta-reader-loading" class="nwp-text-sm nwp-text-muted" style="display:none; text-align:center; padding:32px;">
            <div style="font-size:24px; margin-bottom:16px;">📚</div>
            AI sedang membaca dan menganalisis bab ini...<br>Ini mungkin memakan waktu hingga 30 detik.
        </div>
    </div>
</div>

<!-- Snapshot Modal -->
<div id="snapshot-modal" class="nwp-modal-overlay" style="z-index:90000;">
    <div class="nwp-modal" style="max-width:500px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <h3 class="nwp-modal__title" style="margin:0;">Version History</h3>
            <button type="button" onclick="SnapshotModule.closeModal()" style="background:none; border:none; cursor:pointer; font-size:20px; color:var(--color-text-muted);">&times;</button>
        </div>
        
        <div style="margin-bottom:16px; display:flex; justify-content:space-between; align-items:center;">
            <span class="nwp-text-sm nwp-text-muted">Save the current state before making major changes.</span>
            <button onclick="SnapshotModule.saveSnapshot()" class="nwp-btn nwp-btn--sm nwp-btn--primary">Save Snapshot</button>
        </div>

        <div id="snapshot-list" style="max-height:300px; overflow-y:auto; border:1px solid var(--color-border); border-radius:var(--radius-md); padding:8px; display:flex; flex-direction:column; gap:8px;">
            <div class="nwp-text-sm nwp-text-muted" style="text-align:center; padding:16px;">Loading snapshots...</div>
        </div>
    </div>
</div>

<div class="editor-layout">
    <div class="editor-main">
        <div id="editor-container" class="nwp-editor-container">
            <div id="editor">{!! $chapter->content_html !!}</div>
            <div class="nwp-editor__footer">
                <span id="word-count">{{ number_format($chapter->word_count) }} words</span>
                <span id="last-saved" class="nwp-text-sm nwp-text-muted"></span>
            </div>
        </div>
    </div>
    
    <div id="editor-sidebar" class="editor-sidebar focus-hidden">
        <div class="nwp-card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <h3 class="nwp-heading" style="font-size:var(--font-size-base); margin:0;">🎭 Characters</h3>
                <button onclick="AutoExtractModule.run()" id="btn-auto-extract" class="nwp-btn nwp-btn--sm nwp-btn--primary" style="background:var(--color-accent); font-size:10px; padding:4px 8px; height:auto; min-height:0;" title="Auto-detect new characters in this chapter">✨ Auto-Detect</button>
            </div>
            <div style="font-size:var(--font-size-sm);">
                @if($charactersInChapter->isEmpty() && $otherCharacters->isEmpty())
                    <span class="nwp-text-muted">No characters.</span> 
                @endif
                
                @if($charactersInChapter->isNotEmpty())
                    <div style="font-weight:600; color:var(--color-accent); margin-bottom:8px; font-size:11px; text-transform:uppercase;">In This Chapter</div>
                    @foreach($charactersInChapter as $char)
                        <div style="margin-bottom:12px; padding-bottom:8px; border-bottom:1px solid var(--color-border-light);">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <div>
                                    <strong style="color:var(--color-text-primary);">{{ $char->name }}</strong> 
                                    <span class="nwp-text-muted" style="font-size:10px;">{{ $char->role ? (is_object($char->role) ? $char->role->label() : $char->role) : 'Unknown' }}</span>
                                </div>
                                @if(isset($char->mention_count) && $char->mention_count > 0)
                                    <span class="nwp-badge nwp-badge--muted" style="font-size:9px; padding:2px 6px;">{{ $char->mention_count }}x</span>
                                @endif
                            </div>
                            @if($char->personality_traits) <div style="font-size:11px; color:var(--color-text-muted); margin-top:4px;">{{ \Illuminate\Support\Str::limit($char->personality_traits, 70) }}</div> @endif
                        </div>
                    @endforeach
                @endif

                @if($otherCharacters->isNotEmpty())
                    <div style="font-weight:600; color:var(--color-text-muted); margin-bottom:8px; margin-top:16px; font-size:11px; text-transform:uppercase;">Other Characters</div>
                    @foreach($otherCharacters as $char)
                        <div style="margin-bottom:12px; padding-bottom:8px; border-bottom:1px solid var(--color-border-light); opacity:0.7;">
                            <strong style="color:var(--color-text-primary);">{{ $char->name }}</strong> 
                            <span class="nwp-text-muted" style="font-size:10px;">{{ $char->role ? (is_object($char->role) ? $char->role->label() : $char->role) : 'Unknown' }}</span>
                            @if($char->personality_traits) <div style="font-size:11px; color:var(--color-text-muted); margin-top:4px;">{{ \Illuminate\Support\Str::limit($char->personality_traits, 70) }}</div> @endif
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
        <div class="nwp-card">
            <h3 class="nwp-heading" style="font-size:var(--font-size-base); margin-bottom:12px;">🗺️ Plot Outline</h3>
            <div style="font-size:var(--font-size-sm);">
                @if($plotPoints->isEmpty()) <span class="nwp-text-muted">No plot points.</span> @endif
                @foreach($plotPoints as $plot)
                    <div style="margin-bottom:12px; border-left:3px solid {{ $plot->color_label ?: 'var(--color-accent)' }}; padding-left:8px;">
                        <strong style="color:var(--color-text-primary);">{{ $plot->title }}</strong>
                        <div style="font-size:10px; color:var(--color-text-muted); text-transform:uppercase; margin:2px 0;">{{ $plot->act }} &bull; {{ $plot->status->label() }}</div>
                        @if($plot->description) <div style="font-size:11px; color:var(--color-text-muted);">{{ \Illuminate\Support\Str::limit($plot->description, 100) }}</div> @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
const CharacterHighlightModule = {
    quill: null,
    patterns: [],
    regex: null,
    outerLayer: null,
    innerLayer: null,
    isActive: false,
    renderTimer: null,
    toggleBtn: null,

    init(quill, characters) {
        this.quill = quill;
        
        // Build regex array
        this.patterns = [];
        for (const c of characters) {
            let terms = [c.name];
            if (c.aliases) terms = terms.concat(c.aliases);
            for (const t of terms) {
                if (t && t.trim()) {
                    const escaped = t.trim().replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
                    this.patterns.push(escaped);
                }
            }
        }
        
        this.patterns = [...new Set(this.patterns)].sort((a, b) => b.length - a.length);
        if (this.patterns.length === 0) return;
        
        this.regex = new RegExp(`\\b(${this.patterns.join('|')})\\b`, 'gi');

        // Setup DOM
        this.outerLayer = document.createElement('div');
        this.outerLayer.style.cssText = 'position: absolute; top: 0; left: 0; right: 0; bottom: 0; pointer-events: none; z-index: 10; overflow: hidden;';
        
        this.innerLayer = document.createElement('div');
        this.innerLayer.style.cssText = 'position: absolute; top: 0; left: 0; right: 0; height: 100%; transition: transform 0s;';
        this.outerLayer.appendChild(this.innerLayer);
        
        this.quill.container.appendChild(this.outerLayer);

        // Events
        this.quill.on('text-change', () => {
            if (this.isActive) this.scheduleRender();
        });
        
        this.quill.root.addEventListener('scroll', () => {
            if (this.isActive) {
                this.innerLayer.style.transform = `translateY(-${this.quill.root.scrollTop}px)`;
            }
        });

        // Add toggle button to toolbar
        const toolbar = this.quill.getModule('toolbar').container;
        const btnGroup = document.createElement('span');
        btnGroup.className = 'ql-formats';
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.innerHTML = `<svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>`;
        btn.title = "Highlight Characters";
        btn.style.width = '28px';
        btn.style.height = '24px';
        btn.style.padding = '3px';
        btn.onclick = (e) => {
            e.preventDefault();
            this.toggle();
        };
        btnGroup.appendChild(btn);
        toolbar.appendChild(btnGroup);
        this.toggleBtn = btn;
    },

    toggle() {
        this.isActive = !this.isActive;
        if (this.isActive) {
            this.toggleBtn.style.color = 'var(--color-accent)';
            this.innerLayer.style.display = 'block';
            this.render();
        } else {
            this.toggleBtn.style.color = '';
            this.innerLayer.style.display = 'none';
        }
    },

    scheduleRender() {
        clearTimeout(this.renderTimer);
        this.renderTimer = setTimeout(() => this.render(), 300);
    },

    render() {
        this.innerLayer.innerHTML = '';
        const text = this.quill.getText();
        const scrollTop = this.quill.root.scrollTop;
        
        let match;
        this.regex.lastIndex = 0; // reset
        const frag = document.createDocumentFragment();
        
        while ((match = this.regex.exec(text)) !== null) {
            const index = match.index;
            const length = match[0].length;
            
            try {
                const bounds = this.quill.getBounds(index, length);
                const box = document.createElement('div');
                box.style.position = 'absolute';
                box.style.left = bounds.left + 'px';
                box.style.top = (bounds.top + scrollTop) + 'px';
                box.style.width = bounds.width + 'px';
                box.style.height = bounds.height + 'px';
                box.style.backgroundColor = 'rgba(99, 102, 241, 0.15)'; 
                box.style.borderBottom = '2px solid rgba(99, 102, 241, 0.6)';
                box.style.borderRadius = '2px';
                frag.appendChild(box);
            } catch (e) { }
        }
        
        this.innerLayer.appendChild(frag);
        this.innerLayer.style.transform = `translateY(-${scrollTop}px)`;
    }
};

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

        const allCharacters = [
            @foreach($book->characters as $c)
            @php
                $aliasesArray = $c->aliases ? array_filter(array_map('trim', explode(',', $c->aliases))) : [];
            @endphp
            {
                name: @json($c->name),
                aliases: @json($aliasesArray)
            },
            @endforeach
        ];
        CharacterHighlightModule.init(this.quill, allCharacters);

        // Auto-save 2 detik setelah berhenti mengetik
        this.quill.on('text-change', () => {
            this.isDirty = true;
            this.updateWordCount();
            this.setStatus('Unsaved changes...', '');
            this.updateFocusLine(); // Update focus on type

            // Clear timer sebelumnya, set timer baru
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => {
                if (this.isDirty && !this.isSaving) {
                    this.save();
                }
            }, 2000); // 2 detik setelah berhenti ketik
        });
        
        this.quill.on('selection-change', (range) => {
            this.updateFocusLine(range);
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
    },

    toggleSidebar() {
        document.getElementById('editor-sidebar').classList.toggle('active');
    },

    updateTitle(title) {
        if (!title.trim()) return;
        fetch('{{ route("chapters.update", [$book, $chapter]) }}', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ title: title })
        }).then(res => {
            if (res.ok) this.setStatus('✓ Title saved', 'saved');
        }).catch(err => this.setStatus('✗ Title failed', 'error'));
    },

    toggleFocusMode() {
        document.body.classList.toggle('focus-mode-active');
        if (document.body.classList.contains('focus-mode-active')) {
            NotificationModule.info('Focus Mode Enabled. Move mouse to top to see menu.');
        }
        this.updateFocusLine();
    },

    updateFocusLine(range = null) {
        if (!document.body.classList.contains('focus-mode-active')) {
            document.querySelectorAll('.ql-editor > *').forEach(el => el.classList.remove('focus-active'));
            return;
        }

        if (!range) {
            range = this.quill.getSelection();
        }

        if (range) {
            const [line] = this.quill.getLine(range.index);
            const currentActive = document.querySelector('.ql-editor > *.focus-active');
            
            if (currentActive && (!line || line.domNode !== currentActive)) {
                currentActive.classList.remove('focus-active');
            }
            
            if (line && line.domNode) {
                line.domNode.classList.add('focus-active');
                
                // Optional: Auto-scroll to keep line centered
                const bounds = this.quill.getBounds(range.index);
                if (bounds) {
                    const editorRect = document.querySelector('.ql-editor').getBoundingClientRect();
                    const absoluteTop = window.scrollY + editorRect.top + bounds.top;
                    // Only scroll if it's too far from center to avoid jitter while typing
                    if (Math.abs(absoluteTop - window.scrollY - window.innerHeight / 2) > 100) {
                        window.scrollTo({
                            top: absoluteTop - (window.innerHeight / 2) + 100,
                            behavior: 'smooth'
                        });
                    }
                }
            }
        }
    }
};

const SnapshotModule = {
    modal: null,
    chapterId: {{ $chapter->id }},
    bookId: {{ $book->id }},
    
    openModal() {
        this.modal = document.getElementById('snapshot-modal');
        this.modal.classList.add('nwp-modal-overlay--active');
        this.loadSnapshots();
    },

    closeModal() {
        if(this.modal) this.modal.classList.remove('nwp-modal-overlay--active');
    },

    async loadSnapshots() {
        const container = document.getElementById('snapshot-list');
        container.innerHTML = '<div class="nwp-text-sm nwp-text-muted" style="text-align:center; padding:16px;">Loading...</div>';

        try {
            const res = await fetch(`/books/${this.bookId}/chapters/${this.chapterId}/snapshots`);
            const data = await res.json();
            
            if (data.snapshots.length === 0) {
                container.innerHTML = '<div class="nwp-text-sm nwp-text-muted" style="text-align:center; padding:16px;">No snapshots saved yet.</div>';
                return;
            }

            container.innerHTML = '';
            data.snapshots.forEach(snap => {
                const date = new Date(snap.created_at).toLocaleString();
                const div = document.createElement('div');
                div.style.cssText = 'display:flex; justify-content:space-between; align-items:center; padding:8px 12px; background:var(--color-bg-secondary); border-radius:var(--radius-sm);';
                div.innerHTML = `
                    <span class="nwp-text-sm" style="font-weight:500;">Snapshot: ${date}</span>
                    <button onclick="SnapshotModule.restoreSnapshot(${snap.id})" class="nwp-btn nwp-btn--sm nwp-btn--secondary" style="color:var(--color-accent); border-color:var(--color-accent);">Restore</button>
                `;
                container.appendChild(div);
            });
        } catch(e) {
            container.innerHTML = '<div class="nwp-text-sm nwp-text-danger" style="text-align:center; padding:16px;">Failed to load.</div>';
        }
    },

    async saveSnapshot() {
        if (!confirm('Save a snapshot of the current content?')) return;

        try {
            // make sure content is saved first
            await EditorModule.save();

            const res = await fetch(`/books/${this.bookId}/chapters/${this.chapterId}/snapshots`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            if (!res.ok) throw new Error();
            NotificationModule.success('Snapshot saved successfully!');
            this.loadSnapshots();
        } catch(e) {
            NotificationModule.error('Failed to save snapshot.');
        }
    },

    async restoreSnapshot(id) {
        if (!confirm('Warning: This will overwrite your current unsaved content with the snapshot. Are you sure?')) return;

        try {
            const res = await fetch(`/books/${this.bookId}/chapters/${this.chapterId}/snapshots/${id}/restore`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            if (!res.ok) throw new Error();
            const data = await res.json();
            
            EditorModule.quill.setContents(data.chapter.content_delta || []);
            NotificationModule.success('Snapshot restored!');
            this.closeModal();
            EditorModule.isDirty = false;
        } catch(e) {
            NotificationModule.error('Failed to restore snapshot.');
        }
    }
};

const InlineAiModule = {
    toolbar: document.getElementById('inline-ai-toolbar'),
    currentRange: null,

    init() {
        // Only run after EditorModule is initialized
        setTimeout(() => {
            EditorModule.quill.on('selection-change', (range) => {
                if (range && range.length > 0) {
                    this.currentRange = range;
                    const bounds = EditorModule.quill.getBounds(range.index, range.length);
                    const editorContainer = document.querySelector('#editor-container');
                    const editorRect = editorContainer.getBoundingClientRect();
                    
                    // Position toolbar above the selection
                    this.toolbar.style.display = 'flex';
                    // Note: quill getBounds returns coordinates relative to the editor container
                    this.toolbar.style.left = (bounds.left + editorRect.left) + 'px';
                    this.toolbar.style.top = (bounds.top + editorRect.top - 40) + 'px';
                } else {
                    // We don't hide immediately because the user might be clicking a button inside the toolbar
                    // Hide logic is handled by document click listener
                }
            });

            document.addEventListener('mousedown', (e) => {
                if (this.toolbar.style.display !== 'none' && !this.toolbar.contains(e.target) && !e.target.closest('.ql-editor')) {
                    this.toolbar.style.display = 'none';
                }
            });
        }, 100);
    },

    async edit(instruction) {
        if (!this.currentRange) return;
        const text = EditorModule.quill.getText(this.currentRange.index, this.currentRange.length);
        if (!text.trim()) return;

        this.toolbar.style.display = 'none';
        NotificationModule.info('AI is editing...');

        try {
            const res = await fetch('/ai/inline', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    text: text,
                    instruction: instruction,
                    book_id: {{ $book->id }}
                })
            });

            if (!res.ok) throw new Error();
            const data = await res.json();
            
            // Replace text
            EditorModule.quill.deleteText(this.currentRange.index, this.currentRange.length);
            EditorModule.quill.insertText(this.currentRange.index, data.result);
            NotificationModule.success('Done!');
        } catch(e) {
            NotificationModule.error('Failed to run AI.');
        }
    },

    customEdit() {
        const val = document.getElementById('inline-ai-custom').value;
        if(val) {
            this.edit(val);
            document.getElementById('inline-ai-custom').value = '';
        }
    }
};

const AutoExtractModule = {
    isExtracting: false,

    async run() {
        if (this.isExtracting) return;
        if (!confirm('AI will read this chapter and create any NEW characters that are not in your list yet. This might take a moment and consume AI tokens. Continue?')) return;
        
        const btn = document.getElementById('btn-auto-extract');
        const originalText = btn.textContent;
        btn.textContent = 'Scanning...';
        btn.disabled = true;
        this.isExtracting = true;

        try {
            const data = await this.doExtract();
            if (data.created > 0) {
                alert(`Successfully created ${data.created} new characters! The page will now reload.`);
                window.location.reload();
            } else {
                alert('No new characters were found in this chapter.');
            }
        } catch (e) {
            alert('Error: ' + e.message);
        } finally {
            btn.textContent = originalText;
            btn.disabled = false;
            this.isExtracting = false;
        }
    },

    async runSilent() {
        if (this.isExtracting) return;
        this.isExtracting = true;
        try {
            const data = await this.doExtract(true);
            if (data && data.created > 0) {
                // Show a non-intrusive toast notification and reload page after a brief delay
                const container = document.querySelector('.nwp-toast-container');
                if (container) {
                    const toast = document.createElement('div');
                    toast.className = 'nwp-toast nwp-toast--success';
                    toast.textContent = `AI extracted ${data.created} new character(s)!`;
                    container.appendChild(toast);
                    setTimeout(() => toast.remove(), 5000);
                }
            }
        } catch (e) {
            console.error('Silent auto-extract failed:', e);
        } finally {
            this.isExtracting = false;
        }
    },

    async doExtract(keepalive = false) {
        const res = await fetch('{{ route("chapters.extract-characters", [$book, $chapter]) }}', {
            method: 'POST',
            keepalive: keepalive,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error || 'Failed to extract characters');
        return data;
    }
};

@if($userHasAi)
// Auto-detect every 10 minutes (600000 ms)
setInterval(() => {
    AutoExtractModule.runSilent();
}, 600000);

// Auto-detect when leaving page
window.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'hidden') {
        AutoExtractModule.runSilent();
    }
});
@endif

const BetaReaderModule = {
    chapterId: {{ $chapter->id }},
    bookId: {{ $book->id }},

    openModal() {
        this.modal = document.getElementById('beta-reader-modal');
        this.modal.classList.add('nwp-modal-overlay--active');
        document.getElementById('beta-reader-content').style.display = 'none';
        document.getElementById('btn-run-beta-reader').style.display = 'inline-flex';
    },

    closeModal() {
        if(this.modal) this.modal.classList.remove('nwp-modal-overlay--active');
    },

    async run() {
        const text = EditorModule.quill.getText().trim();
        if (text.length < 50) {
            alert('Teks bab terlalu pendek untuk dianalisis oleh Beta Reader.');
            return;
        }

        document.getElementById('btn-run-beta-reader').style.display = 'none';
        document.getElementById('beta-reader-loading').style.display = 'block';
        document.getElementById('beta-reader-content').style.display = 'none';

        try {
            const res = await fetch(`/books/${this.bookId}/chapters/${this.chapterId}/beta-read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ text: text })
            });

            if (!res.ok) throw new Error();
            const data = await res.json();

            document.getElementById('br-pacing').innerHTML = data.pacing.replace(/\n/g, '<br>');
            document.getElementById('br-showdonttell').innerHTML = data.show_dont_tell.replace(/\n/g, '<br>');
            document.getElementById('br-continuity').innerHTML = data.continuity.replace(/\n/g, '<br>');
            document.getElementById('br-character-consistency').innerHTML = data.character_consistency ? data.character_consistency.replace(/\n/g, '<br>') : 'N/A';
            
            document.getElementById('beta-reader-loading').style.display = 'none';
            document.getElementById('beta-reader-content').style.display = 'flex';
        } catch(e) {
            alert('Gagal menjalankan Beta Reader. Pastikan konfigurasi AI Anda sudah benar di Settings.');
            document.getElementById('beta-reader-loading').style.display = 'none';
            document.getElementById('btn-run-beta-reader').style.display = 'inline-flex';
        }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    EditorModule.init();
    InlineAiModule.init();
});
</script>
@endpush
@endsection
