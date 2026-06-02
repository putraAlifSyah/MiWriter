@extends('layouts.app')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
    <div>
        <a href="{{ route('world.index', $book) }}" class="nwp-text-sm">&larr; Back to World Elements</a>
        <h1 class="nwp-heading nwp-mt-1">World Element Details</h1>
    </div>
</div>

<div class="nwp-card" style="margin-bottom:24px;">
    <div style="display:flex; gap:24px; align-items:flex-start; flex-wrap:wrap;">
        <!-- World Element Image -->
        <div style="flex-shrink:0;">
            <div class="world-image-container" style="width:150px; height:150px; border:2px solid var(--color-border-light); overflow:hidden; display:flex; align-items:center; justify-content:center; background:var(--color-bg-secondary); cursor:pointer; position:relative; border-radius:8px;"
                 onclick="document.getElementById('image-input-{{ $element->id }}').click()">
                @if($element->image_path)
                    <img src="{{ Storage::url($element->image_path) }}" alt="{{ $element->name }}" style="width:100%; height:100%; object-fit:cover;">
                @else
                    <span style="font-size:4rem; color:var(--color-text-muted);">🔮</span>
                @endif
                <div style="position:absolute; bottom:0; left:0; right:0; background:rgba(0,0,0,0.6); color:#fff; font-size:12px; text-align:center; padding:4px; text-transform:uppercase;">Change Image</div>
            </div>
            <input type="file" id="image-input-{{ $element->id }}" accept="image/jpeg,image/png,image/webp" style="display:none;" onchange="uploadWorldImage({{ $element->id }}, this)">
        </div>

        <!-- Edit Form -->
        <div style="flex:1; min-width:300px;">
            <form id="edit-world-form">
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:16px;">
                    <div class="nwp-form-group" style="margin:0;">
                        <label class="nwp-label">Name</label>
                        <input type="text" name="name" class="nwp-input" value="{{ $element->name }}" maxlength="150" required>
                    </div>
                    <div class="nwp-form-group" style="margin:0;">
                        <label class="nwp-label">Category</label>
                        <input type="text" name="category" class="nwp-input" value="{{ $element->category }}" maxlength="50" required placeholder="e.g. Magic System, Artifact, Organization">
                    </div>
                </div>

                <div class="nwp-form-group">
                    <label class="nwp-label">Description</label>
                    <textarea name="description" class="nwp-textarea" maxlength="10000" rows="3">{{ $element->description }}</textarea>
                </div>

                <div class="nwp-form-group">
                    <label class="nwp-label">Rules / Laws</label>
                    <textarea name="rules_laws" class="nwp-textarea" maxlength="5000" rows="3">{{ $element->rules_laws }}</textarea>
                </div>

                <div class="nwp-form-group">
                    <label class="nwp-label">Notes</label>
                    <textarea name="notes" class="nwp-textarea" maxlength="5000" rows="4">{{ $element->notes }}</textarea>
                </div>

                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <button type="submit" class="nwp-btn nwp-btn--sm">Save Changes</button>
                    <button type="button" onclick="deleteWorldElement({{ $element->id }})" class="nwp-btn nwp-btn--danger nwp-btn--sm">Delete Element</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('edit-world-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);

    const btn = this.querySelector('button[type="submit"]');
    const originalText = btn.textContent;
    btn.textContent = 'Saving...';
    btn.disabled = true;

    fetch('{{ route("world.update", [$book, $element]) }}', {
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
        err.json ? err.json().then(d => alert(d.message || 'Failed to update element.')) : alert('Failed to update element.');
    });
});

function uploadWorldImage(elementId, input) {
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

    fetch(`/books/{{ $book->id }}/world/${elementId}/image`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: formData,
    })
    .then(r => { if (!r.ok) throw r; return r.json(); })
    .then(data => {
        const container = document.querySelector(`.world-image-container`);
        const img = container.querySelector('img');
        if (img) {
            img.src = '/storage/' + data.path + '?t=' + Date.now();
        } else {
            container.innerHTML = `<img src="/storage/${data.path}?t=${Date.now()}" style="width:100%; height:100%; object-fit:cover;">` +
                '<div style="position:absolute; bottom:0; left:0; right:0; background:rgba(0,0,0,0.6); color:#fff; font-size:12px; text-align:center; padding:4px; text-transform:uppercase;">Change Image</div>';
        }
    })
    .catch(err => {
        err.json ? err.json().then(d => alert(d.message || 'Upload failed.')).catch(() => alert('Upload failed.')) : alert('Upload failed.');
    });
}

function deleteWorldElement(elementId) {
    if (!confirm('Are you sure you want to delete this element? This cannot be undone.')) return;

    fetch(`/books/{{ $book->id }}/world/${elementId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(r => { if (!r.ok) throw r; return r.json(); })
    .then(() => {
        window.location.href = '{{ route("world.index", $book) }}';
    })
    .catch(() => alert('Failed to delete element.'));
}
</script>
@endpush
@endsection
