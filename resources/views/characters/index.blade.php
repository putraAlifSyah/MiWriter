@extends('layouts.app')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
    <div>
        <a href="{{ route('books.show', $book) }}" class="nwp-text-sm">&larr; {{ $book->title }}</a>
        <h1 class="nwp-heading nwp-mt-1">Characters</h1>
    </div>
</div>

<div class="nwp-tabs">
    <a href="{{ route('books.show', $book) }}" class="nwp-tab">Chapters</a>
    <a href="{{ route('characters.index', $book) }}" class="nwp-tab nwp-tab--active">Characters</a>
    <a href="{{ route('locations.index', $book) }}" class="nwp-tab">Locations</a>
    <a href="{{ route('plot.index', $book) }}" class="nwp-tab">Plot</a>
    <a href="{{ route('world.index', $book) }}" class="nwp-tab">World</a>
    <a href="{{ route('statistics.show', $book) }}" class="nwp-tab">Statistics</a>
</div>

<!-- Search and Filter -->
<div style="display:flex; gap:12px; margin-bottom:24px; flex-wrap:wrap;">
    <form method="GET" style="display:flex; gap:12px; flex:1; min-width:200px;">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search characters..." class="nwp-input" style="flex:1;">
        <select name="role" class="nwp-select" style="width:auto; min-width:140px;" onchange="this.form.submit()">
            <option value="">All Roles</option>
            @foreach(\App\Enums\CharacterRole::cases() as $role)
                <option value="{{ $role->value }}" {{ request('role') === $role->value ? 'selected' : '' }}>{{ $role->label() }}</option>
            @endforeach
        </select>
    </form>
    <button onclick="GraphModule.openModal()" class="nwp-btn nwp-btn--sm nwp-btn--secondary">Relationship Map</button>
    <button onclick="document.getElementById('create-form').style.display='block'" class="nwp-btn nwp-btn--sm">+ Add Character</button>
</div>

<!-- Graph Modal -->
<div id="graph-modal" class="nwp-modal-overlay" style="z-index:90000;">
    <div class="nwp-modal" style="max-width:90vw; width:1000px; max-height:90vh; display:flex; flex-direction:column; padding:24px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <h3 class="nwp-modal__title" style="margin:0;">Character Relationships</h3>
            <div style="display:flex; gap:8px;">
                <button onclick="GraphModule.generateWithAI()" class="nwp-btn nwp-btn--sm nwp-btn--primary" style="background:var(--color-accent);">✨ AI Auto-Detect</button>
                <button type="button" onclick="GraphModule.closeModal()" style="background:none; border:none; cursor:pointer; font-size:24px; color:var(--color-text-muted);">&times;</button>
            </div>
        </div>
        <div id="network-graph" style="flex:1; border:1px solid var(--color-border); border-radius:var(--radius-lg); background:var(--color-bg-primary); height:500px; position:relative; overflow:hidden;"></div>
    </div>
</div>

<!-- Create Form (hidden by default) -->
<div id="create-form" class="nwp-card nwp-mb-3" style="display:none;">
    <h3 class="nwp-heading nwp-mb-2">New Character</h3>
    <form id="character-form">
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px;">
            <div class="nwp-form-group">
                <label class="nwp-label">Name</label>
                <input type="text" name="name" class="nwp-input" maxlength="100" required>
            </div>
            <div class="nwp-form-group">
                <label class="nwp-label">Role</label>
                <select name="role" class="nwp-select" required>
                    @foreach(\App\Enums\CharacterRole::cases() as $role)
                        <option value="{{ $role->value }}">{{ $role->label() }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="nwp-form-group">
            <label class="nwp-label">Physical Description</label>
            <textarea name="physical_description" class="nwp-textarea" maxlength="2000" rows="2"></textarea>
        </div>
        <div class="nwp-form-group">
            <label class="nwp-label">Personality Traits</label>
            <textarea name="personality_traits" class="nwp-textarea" maxlength="2000" rows="2"></textarea>
        </div>
        <div class="nwp-form-group">
            <label class="nwp-label">Backstory</label>
            <textarea name="backstory" class="nwp-textarea" maxlength="5000"></textarea>
        </div>
        <div class="nwp-form-group">
            <label class="nwp-label">Notes</label>
            <textarea name="notes" class="nwp-textarea" maxlength="5000"></textarea>
        </div>
        <button type="submit" class="nwp-btn nwp-btn--sm">Create</button>
        <button type="button" onclick="document.getElementById('create-form').style.display='none'" class="nwp-btn nwp-btn--sm nwp-btn--secondary" style="margin-left:8px;">Cancel</button>
    </form>
</div>

<!-- Character List -->
@if($characters->isEmpty())
    <div class="nwp-empty-state">
        <div class="nwp-empty-state__icon">👤</div>
        <div class="nwp-empty-state__title">No characters yet</div>
        <p class="nwp-empty-state__text">Add characters to build your story's cast.</p>
    </div>
@else
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:16px;">
        @foreach($characters as $character)
            <div class="nwp-card" id="character-{{ $character->id }}">
                <div style="display:flex; gap:16px;">
                    <!-- Character Image -->
                    <div style="flex-shrink:0;">
                        <div class="character-image-container" style="width:80px; height:80px; border:2px solid var(--color-border-light); overflow:hidden; display:flex; align-items:center; justify-content:center; background:var(--color-bg-secondary); cursor:pointer; position:relative;"
                             onclick="document.getElementById('image-input-{{ $character->id }}').click()">
                            @if($character->image_path)
                                <img src="{{ Storage::url($character->image_path) }}" alt="{{ $character->name }}" style="width:100%; height:100%; object-fit:cover;">
                            @else
                                <span style="font-size:2rem; color:var(--color-text-muted);">👤</span>
                            @endif
                            <div style="position:absolute; bottom:0; left:0; right:0; background:rgba(0,0,0,0.6); color:#fff; font-size:10px; text-align:center; padding:2px; text-transform:uppercase;">Upload</div>
                        </div>
                        <input type="file" id="image-input-{{ $character->id }}" accept="image/jpeg,image/png,image/webp" style="display:none;" onchange="uploadCharacterImage({{ $character->id }}, this)">
                    </div>

                    <!-- Character Info -->
                    <div style="flex:1; min-width:0;">
                        <h3 style="font-weight:600; margin-bottom:4px;">{{ $character->name }}</h3>
                        <span class="nwp-badge">{{ $character->role->label() }}</span>
                        @if($character->physical_description)
                            <p class="nwp-text-sm nwp-text-muted nwp-mt-1">{{ Str::limit($character->physical_description, 80) }}</p>
                        @elseif($character->backstory)
                            <p class="nwp-text-sm nwp-text-muted nwp-mt-1">{{ Str::limit($character->backstory, 80) }}</p>
                        @endif
                    </div>
                </div>

                <!-- Action buttons -->
                <div style="margin-top:12px; padding-top:8px; border-top:1px solid var(--color-border-light); display:flex; justify-content:space-between; align-items:center;">
                    <a href="{{ route('characters.show', [$book, $character]) }}" class="nwp-btn nwp-btn--sm nwp-btn--secondary" style="height:28px; padding:0 12px; font-size:11px;">View Details</a>
                    <button onclick="deleteCharacter({{ $character->id }})" class="nwp-btn nwp-btn--danger nwp-btn--sm" style="height:28px; padding:0 12px; font-size:11px;">Delete</button>
                </div>
            </div>
        @endforeach
    </div>
@endif

@push('scripts')
<script type="text/javascript" src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
<script>
const GraphModule = {
    modal: null,
    network: null,
    
    openModal() {
        this.modal = document.getElementById('graph-modal');
        this.modal.classList.add('nwp-modal-overlay--active');
        this.loadGraph();
    },

    closeModal() {
        if(this.modal) this.modal.classList.remove('nwp-modal-overlay--active');
    },

    async loadGraph() {
        try {
            const res = await fetch('{{ route("characters.relationships", $book) }}');
            const data = await res.json();
            
            const nodes = new vis.DataSet(data.characters.map(c => ({
                id: c.id,
                label: c.name,
                shape: c.image_url ? 'circularImage' : 'dot',
                image: c.image_url || undefined,
                color: { background: '#1c1c28', border: '#818cf8' },
                font: { color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#fff' : '#000' }
            })));

            const edges = new vis.DataSet(data.relationships.map(r => ({
                from: r.character_one_id,
                to: r.character_two_id,
                label: r.type,
                font: { align: 'middle', color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#b8b8cc' : '#666' },
                color: { color: '#4b5563' }
            })));

            const container = document.getElementById('network-graph');
            const graphData = { nodes, edges };
            const options = {
                width: '100%',
                height: '100%',
                physics: { stabilization: false },
                edges: { smooth: { type: 'continuous' }, length: 200 },
                nodes: {
                    borderWidth: 2,
                    size: 40,
                    shapeProperties: {
                        useBorderWithImage: true,
                        useImageSize: false
                    }
                }
            };

            this.network = new vis.Network(container, graphData, options);
            
            // Force redraw and fit after a short delay to ensure canvas is properly sized
            setTimeout(() => {
                if (this.network) {
                    this.network.redraw();
                    this.network.fit({ animation: true });
                }
            }, 500);
        } catch(e) {
            console.error('Failed to load relationship graph.');
        }
    },
    
    async generateWithAI() {
        if(!confirm('AI akan menganalisis semua karakter dan bab untuk menemukan hubungan antar karakter secara otomatis. Ini mungkin memakan waktu beberapa saat dan menghabiskan token. Lanjutkan?')) return;
        
        NotificationModule.info('AI sedang menganalisis hubungan...');
        try {
            const res = await fetch('/books/{{ $book->id }}/characters/ai-relationships', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            if(!res.ok) throw new Error();
            NotificationModule.success('Hubungan berhasil diperbarui oleh AI!');
            this.loadGraph();
        } catch(e) {
            NotificationModule.error('Gagal memproses AI.');
        }
    }
};

document.getElementById('character-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);

    fetch('{{ route("characters.store", $book) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify(data),
    })
    .then(r => { if (!r.ok) throw r; return r.json(); })
    .then(() => window.location.reload())
    .catch(err => {
        err.json().then(d => alert(d.message || 'Failed to create character.'));
    });
});

function uploadCharacterImage(characterId, input) {
    const file = input.files[0];
    if (!file) return;

    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
        alert('Only JPEG, PNG, or WebP images are allowed.');
        return;
    }
    if (file.size > 5 * 1024 * 1024) {
        alert('Image must be less than 5MB.');
        return;
    }

    const formData = new FormData();
    formData.append('image', file);

    fetch(`/books/{{ $book->id }}/characters/${characterId}/image`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: formData,
    })
    .then(r => { if (!r.ok) throw r; return r.json(); })
    .then(data => {
        const container = document.querySelector(`#character-${characterId} .character-image-container`);
        const img = container.querySelector('img');
        if (img) {
            img.src = '/storage/' + data.path + '?t=' + Date.now();
        } else {
            container.innerHTML = `<img src="/storage/${data.path}?t=${Date.now()}" style="width:100%; height:100%; object-fit:cover;">` +
                '<div style="position:absolute; bottom:0; left:0; right:0; background:rgba(0,0,0,0.6); color:#fff; font-size:10px; text-align:center; padding:2px; text-transform:uppercase;">Upload</div>';
        }
    })
    .catch(err => {
        err.json().then(d => alert(d.message || 'Upload failed.')).catch(() => alert('Upload failed.'));
    });
}

function deleteCharacter(characterId) {
    if (!confirm('Delete this character?')) return;

    fetch(`/books/{{ $book->id }}/characters/${characterId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(r => { if (!r.ok) throw r; return r.json(); })
    .then(() => {
        document.getElementById('character-' + characterId).remove();
    })
    .catch(() => alert('Failed to delete character.'));
}
</script>
@endpush
@endsection
