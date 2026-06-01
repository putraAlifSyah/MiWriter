@extends('layouts.app')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
    <div>
        <a href="{{ route('books.show', $book) }}" class="nwp-text-sm">&larr; {{ $book->title }}</a>
        <h1 class="nwp-heading nwp-mt-1">Locations</h1>
    </div>
    <button onclick="document.getElementById('location-form-container').style.display='block'" class="nwp-btn nwp-btn--sm">+ Add Location</button>
</div>

<div class="nwp-tabs">
    <a href="{{ route('books.show', $book) }}" class="nwp-tab">Chapters</a>
    <a href="{{ route('characters.index', $book) }}" class="nwp-tab">Characters</a>
    <a href="{{ route('locations.index', $book) }}" class="nwp-tab nwp-tab--active">Locations</a>
    <a href="{{ route('plot.index', $book) }}" class="nwp-tab">Plot</a>
    <a href="{{ route('world.index', $book) }}" class="nwp-tab">World</a>
    <a href="{{ route('statistics.show', $book) }}" class="nwp-tab">Statistics</a>
</div>

<!-- Search and Filter -->
<div style="display:flex; gap:12px; margin-bottom:20px; flex-wrap:wrap;">
    <form method="GET" style="display:flex; gap:8px; flex:1; min-width:200px;">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search locations..." class="nwp-input" style="flex:1;">
        <select name="type" class="nwp-select" style="width:auto; min-width:130px;" onchange="this.form.submit()">
            <option value="">All Types</option>
            @foreach(\App\Enums\LocationType::cases() as $type)
                <option value="{{ $type->value }}" {{ request('type') === $type->value ? 'selected' : '' }}>{{ $type->label() }}</option>
            @endforeach
        </select>
    </form>
</div>

<!-- Create Form -->
<div id="location-form-container" class="nwp-card nwp-mb-3" style="display:none;">
    <h3 style="font-weight:600; margin-bottom:16px;">New Location</h3>
    <form id="location-form">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
            <div class="nwp-form-group">
                <label class="nwp-label">Name</label>
                <input type="text" name="name" class="nwp-input" maxlength="200" required>
            </div>
            <div class="nwp-form-group">
                <label class="nwp-label">Type</label>
                <select name="type" class="nwp-select" required>
                    @foreach(\App\Enums\LocationType::cases() as $type)
                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="nwp-form-group">
            <label class="nwp-label">Description</label>
            <textarea name="description" class="nwp-textarea" rows="2"></textarea>
        </div>
        <div class="nwp-form-group">
            <label class="nwp-label">Atmosphere</label>
            <textarea name="atmosphere" class="nwp-textarea" rows="2"></textarea>
        </div>
        <div style="display:flex; gap:8px;">
            <button type="submit" class="nwp-btn nwp-btn--sm">Create</button>
            <button type="button" onclick="document.getElementById('location-form-container').style.display='none'" class="nwp-btn nwp-btn--secondary nwp-btn--sm">Cancel</button>
        </div>
    </form>
</div>

<!-- Location List -->
@if($locations->isEmpty())
    <div class="nwp-card" style="text-align:center; padding:48px;">
        <div class="nwp-empty-state__title">No locations yet</div>
        <p class="nwp-empty-state__text">Add locations to build your story's world.</p>
    </div>
@else
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:12px;">
        @foreach($locations as $location)
            <div class="nwp-card" id="location-{{ $location->id }}" style="padding:16px;">
                <div style="display:flex; justify-content:space-between; align-items:start;">
                    <div>
                        <h3 style="font-weight:600; font-size:var(--font-size-base);">{{ $location->name }}</h3>
                        <span class="nwp-badge nwp-badge--muted" style="margin-top:4px;">{{ $location->type->label() }}</span>
                    </div>
                    <button onclick="deleteLocation({{ $location->id }})" class="nwp-btn nwp-btn--ghost nwp-btn--sm" style="color:var(--color-danger); height:24px; min-height:24px; padding:0 8px;">Delete</button>
                </div>
                @if($location->description)
                    <p class="nwp-text-sm nwp-text-muted" style="margin-top:8px;">{{ Str::limit($location->description, 100) }}</p>
                @endif
                @if($location->atmosphere)
                    <p class="nwp-text-sm" style="margin-top:6px; font-style:italic; color:var(--color-text-secondary);">{{ Str::limit($location->atmosphere, 80) }}</p>
                @endif
            </div>
        @endforeach
    </div>
@endif

@push('scripts')
<script>
document.getElementById('location-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(this));

    fetch('{{ route("locations.store", $book) }}', {
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
    .catch(err => err.json ? err.json().then(d => alert(d.message || 'Failed.')) : alert('Failed.'));
});

function deleteLocation(id) {
    if (!confirm('Delete this location?')) return;
    fetch(`/books/{{ $book->id }}/locations/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(r => { if (!r.ok) throw r; return r.json(); })
    .then(() => document.getElementById('location-' + id).remove())
    .catch(() => alert('Failed to delete.'));
}
</script>
@endpush
@endsection
