@extends('layouts.app')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
    <div>
        <a href="{{ route('characters.index', $book) }}" class="nwp-text-sm">&larr; Back to Characters</a>
        <h1 class="nwp-heading nwp-mt-1">Character Details</h1>
    </div>
</div>

<div class="nwp-card" style="margin-bottom:24px;">
    <div style="display:flex; gap:24px; align-items:flex-start; flex-wrap:wrap;">
        <!-- Character Image -->
        <div style="flex-shrink:0;">
            <div class="character-image-container" style="width:150px; height:150px; border:2px solid var(--color-border-light); overflow:hidden; display:flex; align-items:center; justify-content:center; background:var(--color-bg-secondary); cursor:pointer; position:relative;"
                 onclick="document.getElementById('image-input-{{ $character->id }}').click()">
                @if($character->image_path)
                    <img src="{{ Storage::url($character->image_path) }}" alt="{{ $character->name }}" style="width:100%; height:100%; object-fit:cover;">
                @else
                    <span style="font-size:4rem; color:var(--color-text-muted);">👤</span>
                @endif
                <div style="position:absolute; bottom:0; left:0; right:0; background:rgba(0,0,0,0.6); color:#fff; font-size:12px; text-align:center; padding:4px; text-transform:uppercase;">Change Image</div>
            </div>
            <input type="file" id="image-input-{{ $character->id }}" accept="image/jpeg,image/png,image/webp" style="display:none;" onchange="uploadCharacterImage({{ $character->id }}, this)">
        </div>

        <!-- Character Core Info (Edit Form) -->
        <div style="flex:1; min-width:300px;">
            <form id="edit-character-form">
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:16px;">
                    <div class="nwp-form-group" style="margin:0;">
                        <label class="nwp-label">Name</label>
                        <input type="text" name="name" class="nwp-input" value="{{ $character->name }}" maxlength="100" required>
                    </div>
                    <div class="nwp-form-group" style="margin:0;">
                        <label class="nwp-label">Role</label>
                        <select name="role" class="nwp-select" required>
                            @foreach(\App\Enums\CharacterRole::cases() as $role)
                                <option value="{{ $role->value }}" {{ $character->role->value === $role->value ? 'selected' : '' }}>{{ $role->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="nwp-form-group">
                    <label class="nwp-label">Physical Description</label>
                    <textarea name="physical_description" class="nwp-textarea" maxlength="2000" rows="3">{{ $character->physical_description }}</textarea>
                </div>

                <div class="nwp-form-group">
                    <label class="nwp-label">Personality Traits</label>
                    <textarea name="personality_traits" class="nwp-textarea" maxlength="2000" rows="3">{{ $character->personality_traits }}</textarea>
                </div>

                <div class="nwp-form-group">
                    <label class="nwp-label">Motivations</label>
                    <textarea name="motivations" class="nwp-textarea" maxlength="2000" rows="3">{{ $character->motivations }}</textarea>
                </div>

                <div class="nwp-form-group">
                    <label class="nwp-label">Backstory</label>
                    <textarea name="backstory" class="nwp-textarea" maxlength="5000" rows="5">{{ $character->backstory }}</textarea>
                </div>

                <div class="nwp-form-group">
                    <label class="nwp-label">Notes</label>
                    <textarea name="notes" class="nwp-textarea" maxlength="5000" rows="4">{{ $character->notes }}</textarea>
                </div>

                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <button type="submit" class="nwp-btn nwp-btn--sm">Save Changes</button>
                    <button type="button" onclick="deleteCharacter({{ $character->id }})" class="nwp-btn nwp-btn--danger nwp-btn--sm">Delete Character</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('edit-character-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);

    const btn = this.querySelector('button[type="submit"]');
    const originalText = btn.textContent;
    btn.textContent = 'Saving...';
    btn.disabled = true;

    fetch('{{ route("characters.update", [$book, $character]) }}', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify(data),
    })
    .then(r => { if (!r.ok) throw r; return r.json(); })
    .then(data => {
        btn.textContent = 'Saved!';
        setTimeout(() => {
            btn.textContent = originalText;
            btn.disabled = false;
        }, 2000);
    })
    .catch(err => {
        btn.textContent = originalText;
        btn.disabled = false;
        err.json().then(d => alert(d.message || 'Failed to update character.'));
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
        const container = document.querySelector(`.character-image-container`);
        const img = container.querySelector('img');
        if (img) {
            img.src = '/storage/' + data.path + '?t=' + Date.now();
        } else {
            container.innerHTML = `<img src="/storage/${data.path}?t=${Date.now()}" style="width:100%; height:100%; object-fit:cover;">` +
                '<div style="position:absolute; bottom:0; left:0; right:0; background:rgba(0,0,0,0.6); color:#fff; font-size:12px; text-align:center; padding:4px; text-transform:uppercase;">Change Image</div>';
        }
    })
    .catch(err => {
        err.json().then(d => alert(d.message || 'Upload failed.')).catch(() => alert('Upload failed.'));
    });
}

function deleteCharacter(characterId) {
    if (!confirm('Are you sure you want to delete this character? This cannot be undone.')) return;

    fetch(`/books/{{ $book->id }}/characters/${characterId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(r => { if (!r.ok) throw r; return r.json(); })
    .then(() => {
        window.location.href = '{{ route("characters.index", $book) }}';
    })
    .catch(() => alert('Failed to delete character.'));
}
</script>
@endpush
@endsection
