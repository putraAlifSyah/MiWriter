@extends('layouts.app')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
    <div>
        <a href="{{ route('locations.index', $book) }}" class="nwp-text-sm">&larr; Back to Locations</a>
        <h1 class="nwp-heading nwp-mt-1">Location Details</h1>
    </div>
</div>

<div class="nwp-card" style="margin-bottom:24px;">
    <div style="display:flex; gap:24px; align-items:flex-start; flex-wrap:wrap;">
        <!-- Location Image -->
        <div style="flex-shrink:0;">
            <div class="location-image-container" style="width:150px; height:150px; border:2px solid var(--color-border-light); overflow:hidden; display:flex; align-items:center; justify-content:center; background:var(--color-bg-secondary); cursor:pointer; position:relative; border-radius:8px;"
                 onclick="document.getElementById('image-input-{{ $location->id }}').click()">
                @if($location->image_path)
                    <img src="{{ Storage::url($location->image_path) }}" alt="{{ $location->name }}" style="width:100%; height:100%; object-fit:cover;">
                @else
                    <span style="font-size:4rem; color:var(--color-text-muted);">🗺️</span>
                @endif
                <div style="position:absolute; bottom:0; left:0; right:0; background:rgba(0,0,0,0.6); color:#fff; font-size:12px; text-align:center; padding:4px; text-transform:uppercase;">Change Image</div>
            </div>
            <input type="file" id="image-input-{{ $location->id }}" accept="image/jpeg,image/png,image/webp" style="display:none;" onchange="uploadLocationImage({{ $location->id }}, this)">
        </div>

        <!-- Edit Form -->
        <div style="flex:1; min-width:300px;">
            <form id="edit-location-form">
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:16px;">
                    <div class="nwp-form-group" style="margin:0;">
                        <label class="nwp-label">Name</label>
                        <input type="text" name="name" class="nwp-input" value="{{ $location->name }}" maxlength="200" required>
                    </div>
                    <div class="nwp-form-group" style="margin:0;">
                        <label class="nwp-label">Type</label>
                        <select name="type" class="nwp-select" required>
                            @foreach(\App\Enums\LocationType::cases() as $type)
                                <option value="{{ $type->value }}" {{ (is_object($location->type) ? $location->type->value : $location->type) === $type->value ? 'selected' : '' }}>{{ $type->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="nwp-form-group">
                    <label class="nwp-label">Description</label>
                    <textarea name="description" class="nwp-textarea" maxlength="2000" rows="3">{{ $location->description }}</textarea>
                </div>

                <div class="nwp-form-group">
                    <label class="nwp-label">Atmosphere</label>
                    <textarea name="atmosphere" class="nwp-textarea" maxlength="2000" rows="3">{{ $location->atmosphere }}</textarea>
                </div>

                <div class="nwp-form-group">
                    <label class="nwp-label">Notable Features</label>
                    <textarea name="notable_features" class="nwp-textarea" maxlength="2000" rows="3">{{ $location->notable_features }}</textarea>
                </div>

                <div class="nwp-form-group">
                    <label class="nwp-label">Notes</label>
                    <textarea name="notes" class="nwp-textarea" maxlength="5000" rows="4">{{ $location->notes }}</textarea>
                </div>

                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <button type="submit" class="nwp-btn nwp-btn--sm">Save Changes</button>
                    <button type="button" onclick="deleteLocation({{ $location->id }})" class="nwp-btn nwp-btn--danger nwp-btn--sm">Delete Location</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('edit-location-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);

    const btn = this.querySelector('button[type="submit"]');
    const originalText = btn.textContent;
    btn.textContent = 'Saving...';
    btn.disabled = true;

    fetch('{{ route("locations.update", [$book, $location]) }}', {
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
        err.json ? err.json().then(d => alert(d.message || 'Failed to update location.')) : alert('Failed to update location.');
    });
});

function uploadLocationImage(locationId, input) {
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

    fetch(`/books/{{ $book->id }}/locations/${locationId}/image`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: formData,
    })
    .then(r => { if (!r.ok) throw r; return r.json(); })
    .then(data => {
        const container = document.querySelector(`.location-image-container`);
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

function deleteLocation(locationId) {
    if (!confirm('Are you sure you want to delete this location? This cannot be undone.')) return;

    fetch(`/books/{{ $book->id }}/locations/${locationId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(r => { if (!r.ok) throw r; return r.json(); })
    .then(() => {
        window.location.href = '{{ route("locations.index", $book) }}';
    })
    .catch(() => alert('Failed to delete location.'));
}
</script>
@endpush
@endsection
