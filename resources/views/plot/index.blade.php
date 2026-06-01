@extends('layouts.app')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
    <div>
        <a href="{{ route('books.show', $book) }}" class="nwp-text-sm">&larr; {{ $book->title }}</a>
        <h1 class="nwp-heading nwp-mt-1">Plot Outline</h1>
    </div>
    <button onclick="document.getElementById('plot-form-container').style.display='block'" class="nwp-btn nwp-btn--sm">+ Add Plot Point</button>
</div>

<div class="nwp-tabs">
    <a href="{{ route('books.show', $book) }}" class="nwp-tab">Chapters</a>
    <a href="{{ route('characters.index', $book) }}" class="nwp-tab">Characters</a>
    <a href="{{ route('locations.index', $book) }}" class="nwp-tab">Locations</a>
    <a href="{{ route('plot.index', $book) }}" class="nwp-tab nwp-tab--active">Plot</a>
    <a href="{{ route('world.index', $book) }}" class="nwp-tab">World</a>
    <a href="{{ route('statistics.show', $book) }}" class="nwp-tab">Statistics</a>
</div>

<!-- Create Form -->
<div id="plot-form-container" class="nwp-card nwp-mb-3" style="display:none;">
    <h3 style="font-weight:600; margin-bottom:16px;">New Plot Point</h3>
    <form id="plot-form">
        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
            <div class="nwp-form-group">
                <label class="nwp-label">Title</label>
                <input type="text" name="title" class="nwp-input" maxlength="150" required>
            </div>
            <div class="nwp-form-group">
                <label class="nwp-label">Act</label>
                <select name="act" class="nwp-select" required>
                    <option value="beginning">Beginning</option>
                    <option value="middle">Middle</option>
                    <option value="end">End</option>
                </select>
            </div>
            <div class="nwp-form-group">
                <label class="nwp-label">Status</label>
                <select name="status" class="nwp-select" required>
                    <option value="planned">Planned</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
        </div>
        <div class="nwp-form-group">
            <label class="nwp-label">Description</label>
            <textarea name="description" class="nwp-textarea" maxlength="2000" rows="3"></textarea>
        </div>
        <div style="display:flex; gap:8px;">
            <button type="submit" class="nwp-btn nwp-btn--sm">Create</button>
            <button type="button" onclick="document.getElementById('plot-form-container').style.display='none'" class="nwp-btn nwp-btn--secondary nwp-btn--sm">Cancel</button>
        </div>
    </form>
</div>

<!-- Plot Points List -->
@if($plotPoints->isEmpty())
    <div class="nwp-card" style="text-align:center; padding:48px;">
        <div class="nwp-empty-state__title">No plot points yet</div>
        <p class="nwp-empty-state__text">Outline your story's narrative arc.</p>
    </div>
@else
    <div id="plot-list">
        @foreach($plotPoints as $point)
            <div class="nwp-card nwp-mb-2" id="plot-{{ $point->id }}" style="border-left:4px solid {{ $point->color_label ?: 'var(--color-accent)' }};">
                <div style="display:flex; justify-content:space-between; align-items:start;">
                    <div style="flex:1;">
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
                            <span style="font-weight:600;">{{ $point->title }}</span>
                            <span class="nwp-badge">{{ $point->act->label() }}</span>
                            <span class="nwp-badge nwp-badge--muted">{{ $point->status->label() }}</span>
                        </div>
                        @if($point->description)
                            <p class="nwp-text-sm nwp-text-muted">{{ Str::limit($point->description, 200) }}</p>
                        @endif
                        @if($point->characters->isNotEmpty())
                            <div class="nwp-text-sm" style="margin-top:8px;">
                                <strong>Characters:</strong> {{ $point->characters->pluck('name')->join(', ') }}
                            </div>
                        @endif
                        @if($point->locations->isNotEmpty())
                            <div class="nwp-text-sm" style="margin-top:4px;">
                                <strong>Locations:</strong> {{ $point->locations->pluck('name')->join(', ') }}
                            </div>
                        @endif
                    </div>
                    <div style="display:flex; gap:6px; align-items:center;">
                        <span class="nwp-text-sm nwp-text-muted">#{{ $point->position }}</span>
                        <button onclick="deletePlotPoint({{ $point->id }})" class="nwp-btn nwp-btn--ghost nwp-btn--sm" style="color:var(--color-danger);">Delete</button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

@push('scripts')
<script>
document.getElementById('plot-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(this));

    fetch('{{ route("plot.store", $book) }}', {
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

function deletePlotPoint(id) {
    if (!confirm('Delete this plot point?')) return;
    fetch(`/books/{{ $book->id }}/plot/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(r => { if (!r.ok) throw r; return r.json(); })
    .then(() => document.getElementById('plot-' + id).remove())
    .catch(() => alert('Failed to delete.'));
}
</script>
@endpush
@endsection
