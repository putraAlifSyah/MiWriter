@extends('layouts.app')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
    <div>
        <a href="{{ route('books.show', $book) }}" class="nwp-text-sm">&larr; {{ $book->title }}</a>
        <h1 class="nwp-heading nwp-mt-1">World Building</h1>
    </div>
    <button onclick="document.getElementById('world-form-container').style.display='block'" class="nwp-btn nwp-btn--sm">+ Add Element</button>
</div>

<div class="nwp-tabs">
    <a href="{{ route('books.show', $book) }}" class="nwp-tab">Chapters</a>
    <a href="{{ route('characters.index', $book) }}" class="nwp-tab">Characters</a>
    <a href="{{ route('locations.index', $book) }}" class="nwp-tab">Locations</a>
    <a href="{{ route('plot.index', $book) }}" class="nwp-tab">Plot</a>
    <a href="{{ route('world.index', $book) }}" class="nwp-tab nwp-tab--active">World</a>
    <a href="{{ route('statistics.show', $book) }}" class="nwp-tab">Statistics</a>
</div>

<!-- Create Form -->
<div id="world-form-container" class="nwp-card nwp-mb-3" style="display:none;">
    <h3 style="font-weight:600; margin-bottom:16px;">New World Element</h3>
    <form id="world-form">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
            <div class="nwp-form-group">
                <label class="nwp-label">Name</label>
                <input type="text" name="name" class="nwp-input" maxlength="150" required>
            </div>
            <div class="nwp-form-group">
                <label class="nwp-label">Category</label>
                <select name="category" class="nwp-select" required>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}">{{ ucfirst($cat) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="nwp-form-group">
            <label class="nwp-label">Description</label>
            <textarea name="description" class="nwp-textarea" maxlength="10000" rows="3"></textarea>
        </div>
        <div class="nwp-form-group">
            <label class="nwp-label">Rules / Laws</label>
            <textarea name="rules_laws" class="nwp-textarea" maxlength="5000" rows="2"></textarea>
        </div>
        <div class="nwp-form-group">
            <label class="nwp-label">Notes</label>
            <textarea name="notes" class="nwp-textarea" maxlength="5000" rows="2"></textarea>
        </div>
        <div style="display:flex; gap:8px;">
            <button type="submit" class="nwp-btn nwp-btn--sm">Create</button>
            <button type="button" onclick="document.getElementById('world-form-container').style.display='none'" class="nwp-btn nwp-btn--secondary nwp-btn--sm">Cancel</button>
        </div>
    </form>
</div>

<!-- World Elements -->
@if($grouped->isEmpty())
    <div class="nwp-card" style="text-align:center; padding:48px;">
        <div class="nwp-empty-state__title">No world elements yet</div>
        <p class="nwp-empty-state__text">Document your world's rules, cultures, and systems.</p>
    </div>
@else
    @foreach($grouped as $category => $elements)
        <div class="nwp-mb-3">
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:12px;">
                <h2 style="font-size:var(--font-size-base); font-weight:600;">{{ ucfirst($category) }}</h2>
                <span class="nwp-badge nwp-badge--muted">{{ $elements->count() }}</span>
            </div>
            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:12px;">
                @foreach($elements as $element)
                    <div class="nwp-card" id="element-{{ $element->id }}" style="padding:16px;">
                        <div style="display:flex; justify-content:space-between; align-items:start;">
                            <h3 style="font-weight:600; font-size:var(--font-size-base);">{{ $element->name }}</h3>
                            <button onclick="deleteElement({{ $element->id }})" class="nwp-btn nwp-btn--ghost nwp-btn--sm" style="color:var(--color-danger); height:24px; min-height:24px; padding:0 8px;">Delete</button>
                        </div>
                        @if($element->description)
                            <p class="nwp-text-sm nwp-text-muted" style="margin-top:6px;">{{ Str::limit($element->description, 150) }}</p>
                        @endif
                        @if($element->rules_laws)
                            <div class="nwp-text-sm" style="margin-top:8px; padding-top:8px; border-top:1px solid var(--color-border-light);">
                                <strong>Rules:</strong> {{ Str::limit($element->rules_laws, 100) }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
@endif

@push('scripts')
<script>
document.getElementById('world-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(this));

    fetch('{{ route("world.store", $book) }}', {
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

function deleteElement(id) {
    if (!confirm('Delete this element?')) return;
    fetch(`/books/{{ $book->id }}/world/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(r => { if (!r.ok) throw r; return r.json(); })
    .then(() => document.getElementById('element-' + id).remove())
    .catch(() => alert('Failed to delete.'));
}
</script>
@endpush
@endsection
