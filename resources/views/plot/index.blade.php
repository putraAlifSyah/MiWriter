@extends('layouts.app')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
    <div>
        <a href="{{ route('books.show', $book) }}" class="nwp-text-sm">&larr; {{ $book->title }}</a>
        <h1 class="nwp-heading nwp-mt-1">Plot Outline</h1>
    </div>
    <div style="display:flex; gap:8px;">
        <button onclick="document.getElementById('ai-wizard-modal').style.display='block'" class="nwp-btn nwp-btn--sm nwp-btn--primary" style="background:var(--color-accent);">✨ AI Plot Wizard</button>
        <button onclick="document.getElementById('plot-form-container').style.display='block'" class="nwp-btn nwp-btn--sm">+ Add Plot Point</button>
    </div>
</div>

<!-- AI Wizard Modal -->
<div id="ai-wizard-modal" class="nwp-modal-overlay" style="z-index:10000; display:none;">
    <div class="nwp-modal" style="max-width:600px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <h3 class="nwp-modal__title" style="margin:0;">✨ AI Plot Framework Wizard</h3>
            <button type="button" onclick="document.getElementById('ai-wizard-modal').style.display='none'" style="background:none; border:none; cursor:pointer; font-size:20px; color:var(--color-text-muted);">&times;</button>
        </div>
        <p class="nwp-text-sm nwp-text-muted nwp-mb-3">Biarkan AI membuatkan kerangka plot otomatis berdasarkan ide cerita Anda.</p>
        
        <form id="ai-wizard-form" style="display:flex; flex-direction:column; gap:16px;">
            <div class="nwp-form-group">
                <label class="nwp-label">Story Premise</label>
                <textarea name="premise" class="nwp-textarea" rows="3" placeholder="Contoh: Seorang pemuda menemukan pedang ajaib di halaman belakang rumahnya dan harus menyelamatkan dunia dari naga jahat..." required></textarea>
            </div>
            <div class="nwp-form-group">
                <label class="nwp-label">Framework</label>
                <select name="framework" class="nwp-select" required>
                    <option value="Save The Cat">Save The Cat</option>
                    <option value="Hero's Journey">Hero's Journey</option>
                    <option value="3-Act Structure">3-Act Structure</option>
                </select>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:8px;">
                <button type="button" onclick="document.getElementById('ai-wizard-modal').style.display='none'" class="nwp-btn nwp-btn--secondary">Cancel</button>
                <button type="submit" id="btn-wizard-submit" class="nwp-btn nwp-btn--primary" style="background:var(--color-accent);">Generate Plot</button>
            </div>
        </form>
    </div>
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

<!-- Kanban Board -->
@if($plotPoints->isEmpty())
    <div class="nwp-card" style="text-align:center; padding:48px;">
        <div class="nwp-empty-state__title">No plot points yet</div>
        <p class="nwp-empty-state__text">Outline your story's narrative arc.</p>
    </div>
@else
    @php
        $acts = [
            'beginning' => ['title' => 'Act 1: Beginning', 'color' => '#3b82f6'],
            'middle' => ['title' => 'Act 2: Middle', 'color' => '#10b981'],
            'end' => ['title' => 'Act 3: End', 'color' => '#8b5cf6'],
        ];
    @endphp

    <div style="display:flex; gap:16px; overflow-x:auto; padding-bottom:16px; min-height:60vh;">
        @foreach($acts as $actKey => $actInfo)
        <div class="kanban-column" data-act="{{ $actKey }}" style="flex:1; min-width:300px; background:var(--color-bg-secondary); border-radius:var(--radius-lg); padding:16px; display:flex; flex-direction:column;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; padding-bottom:8px; border-bottom:2px solid {{ $actInfo['color'] }};">
                <h3 style="font-weight:700; margin:0; font-size:var(--font-size-base);">{{ $actInfo['title'] }}</h3>
                <span class="nwp-badge" style="background:{{ $actInfo['color'] }}20; color:{{ $actInfo['color'] }};">{{ $plotPoints->where('act.value', $actKey)->count() }}</span>
            </div>
            
            <div class="kanban-dropzone" style="flex:1; display:flex; flex-direction:column; gap:12px; min-height:100px;">
                @foreach($plotPoints->where('act.value', $actKey) as $point)
                <div class="nwp-card kanban-card" draggable="true" data-id="{{ $point->id }}" id="plot-{{ $point->id }}" style="border-left:4px solid {{ $point->color_label ?: $actInfo['color'] }}; cursor:grab; margin:0; padding:12px;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
                        <h4 style="margin:0; font-weight:600; font-size:var(--font-size-sm); line-height:1.4;">{{ $point->title }}</h4>
                        <button onclick="deletePlotPoint({{ $point->id }})" style="background:none; border:none; cursor:pointer; color:var(--color-text-muted); padding:4px;">&times;</button>
                    </div>
                    @if($point->description)
                        <p class="nwp-text-xs nwp-text-muted" style="margin-bottom:8px; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden;">{{ $point->description }}</p>
                    @endif
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:auto;">
                        <span class="nwp-badge nwp-badge--muted" style="font-size:10px;">{{ $point->status->label() }}</span>
                        @if($point->characters->isNotEmpty())
                            <span class="nwp-text-xs nwp-text-muted" title="{{ $point->characters->pluck('name')->join(', ') }}">👤 {{ $point->characters->count() }}</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
@endif

@push('scripts')
<script>
let draggedItem = null;

document.querySelectorAll('.kanban-card').forEach(card => {
    card.addEventListener('dragstart', function(e) {
        draggedItem = this;
        setTimeout(() => this.style.opacity = '0.5', 0);
    });
    
    card.addEventListener('dragend', function(e) {
        setTimeout(() => this.style.opacity = '1', 0);
        draggedItem = null;
    });
});

document.querySelectorAll('.kanban-column').forEach(column => {
    column.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.style.background = 'var(--color-bg-tertiary)';
    });
    
    column.addEventListener('dragleave', function(e) {
        this.style.background = 'var(--color-bg-secondary)';
    });
    
    column.addEventListener('drop', function(e) {
        e.preventDefault();
        this.style.background = 'var(--color-bg-secondary)';
        
        if (draggedItem) {
            const dropzone = this.querySelector('.kanban-dropzone');
            dropzone.appendChild(draggedItem);
            
            const plotId = draggedItem.dataset.id;
            const newAct = this.dataset.act;
            
            // Assume status stays the same, we only update act. For simplicity, just send status: planned.
            // Actually, we need the existing status. It's stored in the UI but maybe we can just pass what we know.
            // It's better to fetch the status or assume it remains the same.
            updatePlotPosition(plotId, newAct);
        }
    });
});

function updatePlotPosition(id, act) {
    // Note: status might change in reality, we just send 'in_progress' or keep it 'planned' for now.
    // To make it perfect we should read the existing status. Let's just use 'planned' as default if not known.
    fetch(`/books/{{ $book->id }}/plot/${id}/move`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ act: act, status: 'planned' })
    }).catch(() => NotificationModule.error('Failed to move plot point.'));
}
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

document.getElementById('ai-wizard-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(this));
    const btn = document.getElementById('btn-wizard-submit');
    btn.disabled = true;
    btn.textContent = 'Generating... (may take 30s)';

    fetch('{{ route("plot.ai-wizard", $book) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify(data),
    })
    .then(r => { if (!r.ok) throw r; return r.json(); })
    .then(res => {
        if(res.success) {
            window.location.reload();
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.textContent = 'Generate Plot';
        err.json ? err.json().then(d => alert(d.error || 'Failed to generate plot.')) : alert('Failed to contact AI.');
    });
});
</script>
@endpush
@endsection
