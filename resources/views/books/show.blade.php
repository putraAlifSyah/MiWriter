@extends('layouts.app')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
    <h1 class="nwp-heading">{{ $book->title }}</h1>
    <div style="display:flex; gap:8px;">
        <div style="position:relative; display:inline-block;" class="export-dropdown">
            <button class="nwp-btn nwp-btn--sm nwp-btn--secondary" onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'block' ? 'none' : 'block'">Export ▼</button>
            <div style="display:none; position:absolute; right:0; top:100%; background:var(--color-bg-primary); border:1px solid var(--color-border); border-radius:var(--radius-md); padding:8px; z-index:100; min-width:150px; box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                <a href="{{ route('export.book', $book) }}?format=txt" class="nwp-btn nwp-btn--sm" style="display:block; text-align:left; background:transparent; width:100%; margin-bottom:4px;">📄 Text (.txt)</a>
                <a href="{{ route('export.book', $book) }}?format=md" class="nwp-btn nwp-btn--sm" style="display:block; text-align:left; background:transparent; width:100%; margin-bottom:4px;">📝 Markdown (.md)</a>
                <a href="{{ route('export.book', $book) }}?format=epub" class="nwp-btn nwp-btn--sm" style="display:block; text-align:left; background:transparent; width:100%; margin-bottom:4px; color:var(--color-accent);">📚 eBook (.epub)</a>
                <a href="{{ route('export.book', $book) }}?format=pdf" class="nwp-btn nwp-btn--sm" style="display:block; text-align:left; background:transparent; width:100%; color:var(--color-danger);">📕 PDF (.pdf)</a>
            </div>
        </div>
        <form method="POST" action="{{ route('books.destroy', $book) }}" onsubmit="return confirm('Delete this book and all its content?')">
            @csrf @method('DELETE')
            <button type="submit" class="nwp-btn nwp-btn--sm nwp-btn--danger">Delete</button>
        </form>
    </div>
</div>

<div class="nwp-tabs">
    <a href="{{ route('books.show', $book) }}" class="nwp-tab nwp-tab--active">Chapters</a>
    <a href="{{ route('characters.index', $book) }}" class="nwp-tab">Characters</a>
    <a href="{{ route('locations.index', $book) }}" class="nwp-tab">Locations</a>
    <a href="{{ route('plot.index', $book) }}" class="nwp-tab">Plot</a>
    <a href="{{ route('world.index', $book) }}" class="nwp-tab">World</a>
    <a href="{{ route('statistics.show', $book) }}" class="nwp-tab">Statistics</a>
</div>

<!-- Book Cover & Metadata -->
<div class="nwp-card nwp-mb-3">
    <div style="display:flex; gap:24px; flex-wrap:wrap;">
        <!-- Cover Image Upload -->
        <div style="flex-shrink:0;">
            <label class="nwp-label">Cover Image</label>
            <div id="cover-container" style="width:180px; height:270px; border:2px dashed var(--color-border-light); display:flex; align-items:center; justify-content:center; cursor:pointer; position:relative; overflow:hidden; background:var(--color-bg-secondary);"
                 onclick="document.getElementById('cover-input').click()">
                @if($book->cover_image_path)
                    <img id="cover-preview" src="{{ Storage::url($book->cover_image_path) }}" alt="Cover" style="width:100%; height:100%; object-fit:cover;">
                @else
                    <div id="cover-placeholder" style="text-align:center; color:var(--color-text-muted);">
                        <div style="font-size:2.5rem;">📖</div>
                        <div style="font-size:12px; margin-top:8px; text-transform:uppercase; font-weight:600;">Click to upload</div>
                        <div style="font-size:11px; margin-top:4px;">JPEG, PNG, WebP<br>Min 600×900px</div>
                    </div>
                @endif
            </div>
            <input type="file" id="cover-input" accept="image/jpeg,image/png,image/webp" style="display:none;" onchange="uploadCover(this)">
            @if($book->cover_image_path)
                <button onclick="removeCover()" class="nwp-btn nwp-btn--danger nwp-btn--sm nwp-mt-1" style="width:180px; height:30px; font-size:11px;">Remove Cover</button>
            @endif
            <div id="cover-status" class="nwp-text-sm nwp-mt-1" style="width:180px;"></div>
        </div>

        <!-- Metadata Form -->
        <div style="flex:1; min-width:280px;">
            <form method="POST" action="{{ route('books.update', $book) }}">
                @csrf @method('PUT')
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="nwp-form-group">
                        <label class="nwp-label" for="title">Title</label>
                        <input type="text" id="title" name="title" value="{{ $book->title }}" class="nwp-input" maxlength="200">
                    </div>
                    <div class="nwp-form-group">
                        <label class="nwp-label" for="genre">Genre</label>
                        <input type="text" id="genre" name="genre" value="{{ $book->genre }}" class="nwp-input" maxlength="100">
                    </div>
                </div>
                <div class="nwp-form-group">
                    <label class="nwp-label" for="synopsis">Synopsis</label>
                    <textarea id="synopsis" name="synopsis" class="nwp-textarea" maxlength="2000">{{ $book->synopsis }}</textarea>
                </div>
                <div class="nwp-form-group">
                    <label class="nwp-label" for="status">Status</label>
                    <select id="status" name="status" class="nwp-select">
                        @foreach(\App\Enums\BookStatus::cases() as $status)
                            <option value="{{ $status->value }}" {{ $book->status === $status ? 'selected' : '' }}>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="nwp-btn nwp-btn--sm">Save Changes</button>
            </form>
        </div>
    </div>
</div>

<!-- Chapters List -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
    <h2 class="nwp-heading">Chapters</h2>
    <button onclick="createChapter()" class="nwp-btn nwp-btn--sm">+ Add Chapter</button>
</div>

<div class="nwp-card">
    <p class="nwp-text-sm nwp-text-muted nwp-mb-2">
        Total: {{ number_format($book->total_word_count) }} words
    </p>

    @if($book->chapters->isEmpty())
        <p class="nwp-text-muted">No chapters yet. Create your first chapter to start writing.</p>
    @else
        <div id="chapter-list">
            @foreach($book->chapters as $chapter)
                <div class="chapter-item" data-id="{{ $chapter->id }}" style="display:flex; justify-content:space-between; align-items:center; padding:12px 0; border-bottom:1px solid var(--color-border-light);">
                    <a href="{{ route('chapters.show', [$book, $chapter]) }}" style="font-weight:500;">
                        {{ $chapter->title }}
                    </a>
                    <span class="nwp-text-sm nwp-text-muted">{{ number_format($chapter->word_count) }} words</span>
                </div>
            @endforeach
        </div>
    @endif
</div>

@push('scripts')
<script>
function createChapter() {
    fetch('{{ route("chapters.store", $book) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(r => r.json())
    .then(data => {
        if (data.redirect) {
            window.location.href = data.redirect;
        } else {
            window.location.reload();
        }
    })
    .catch(err => alert('Failed to create chapter.'));
}

function uploadCover(input) {
    const file = input.files[0];
    if (!file) return;

    // Client-side validation
    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
        showCoverStatus('Only JPEG, PNG, or WebP allowed.', 'error');
        return;
    }
    if (file.size > 5 * 1024 * 1024) {
        showCoverStatus('File must be less than 5MB.', 'error');
        return;
    }

    showCoverStatus('Uploading...', '');

    const formData = new FormData();
    formData.append('cover', file);

    fetch('{{ route("books.cover.upload", $book) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: formData,
    })
    .then(r => {
        if (!r.ok) return r.json().then(d => { throw d; });
        return r.json();
    })
    .then(data => {
        showCoverStatus('✓ Cover uploaded!', 'success');
        // Show preview
        const container = document.getElementById('cover-container');
        container.innerHTML = `<img id="cover-preview" src="/storage/${data.path}?t=${Date.now()}" style="width:100%; height:100%; object-fit:cover;">`;
        // Reload to show remove button
        setTimeout(() => window.location.reload(), 1000);
    })
    .catch(err => {
        showCoverStatus(err.message || 'Upload failed.', 'error');
    });
}

function removeCover() {
    if (!confirm('Remove the cover image?')) return;

    fetch('{{ route("books.cover.remove", $book) }}', {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(r => { if (!r.ok) throw r; return r.json(); })
    .then(() => window.location.reload())
    .catch(() => alert('Failed to remove cover.'));
}

function showCoverStatus(text, type) {
    const el = document.getElementById('cover-status');
    el.textContent = text;
    el.style.color = type === 'error' ? 'var(--color-danger)' : type === 'success' ? 'var(--color-success)' : 'var(--color-text-muted)';
}
</script>
@endpush
@endsection
