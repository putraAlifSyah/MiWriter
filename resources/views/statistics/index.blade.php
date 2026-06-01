@extends('layouts.app')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
    <div>
        <a href="{{ route('books.show', $book) }}" class="nwp-text-sm">&larr; {{ $book->title }}</a>
        <h1 class="nwp-heading nwp-mt-1">Statistics</h1>
    </div>
</div>

<div class="nwp-tabs">
    <a href="{{ route('books.show', $book) }}" class="nwp-tab">Chapters</a>
    <a href="{{ route('characters.index', $book) }}" class="nwp-tab">Characters</a>
    <a href="{{ route('locations.index', $book) }}" class="nwp-tab">Locations</a>
    <a href="{{ route('plot.index', $book) }}" class="nwp-tab">Plot</a>
    <a href="{{ route('world.index', $book) }}" class="nwp-tab">World</a>
    <a href="{{ route('statistics.show', $book) }}" class="nwp-tab nwp-tab--active">Statistics</a>
</div>

<!-- Stats Overview -->
<div class="nwp-stats">
    <div class="nwp-stat">
        <div class="nwp-stat__value">{{ number_format($stats['total_word_count']) }}</div>
        <div class="nwp-stat__label">Total Words</div>
    </div>
    <div class="nwp-stat">
        <div class="nwp-stat__value">{{ $stats['current_streak'] }}d</div>
        <div class="nwp-stat__label">Current Streak</div>
    </div>
    <div class="nwp-stat">
        <div class="nwp-stat__value">{{ $stats['longest_streak'] }}d</div>
        <div class="nwp-stat__label">Longest Streak</div>
    </div>
    <div class="nwp-stat">
        <div class="nwp-stat__value">{{ number_format($stats['average_daily']) }}</div>
        <div class="nwp-stat__label">Avg Daily (30d)</div>
    </div>
</div>

<!-- Progress + Targets -->
<div class="nwp-dashboard-grid">
    <!-- Progress Bars -->
    <div class="nwp-card">
        <h3 style="font-size:var(--font-size-sm); font-weight:600; color:var(--color-text-muted); margin-bottom:16px;">Progress</h3>

        @if($stats['daily_progress']['has_target'] ?? false)
            <div style="margin-bottom:16px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                    <span class="nwp-text-sm" style="font-weight:500;">Daily Target</span>
                    <span class="nwp-text-sm" style="font-weight:600; color:var(--color-accent);">{{ $stats['daily_progress']['percentage'] }}%</span>
                </div>
                <div class="nwp-progress">
                    <div class="nwp-progress__bar {{ $stats['daily_progress']['met'] ? 'nwp-progress__bar--complete' : '' }}" style="width:{{ $stats['daily_progress']['percentage'] }}%"></div>
                </div>
                <div class="nwp-text-sm nwp-text-muted" style="margin-top:4px;">
                    {{ number_format($stats['daily_progress']['current']) }} / {{ number_format($stats['daily_progress']['target']) }} words
                    @if($stats['daily_progress']['met'])
                        <span class="nwp-badge nwp-badge--success" style="margin-left:6px;">Target met</span>
                    @endif
                </div>
            </div>
        @else
            <p class="nwp-text-sm nwp-text-muted nwp-mb-2">No daily target set.</p>
        @endif

        @if($stats['weekly_progress']['has_target'] ?? false)
            <div>
                <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                    <span class="nwp-text-sm" style="font-weight:500;">Weekly Target</span>
                    <span class="nwp-text-sm" style="font-weight:600; color:var(--color-accent);">{{ $stats['weekly_progress']['percentage'] }}%</span>
                </div>
                <div class="nwp-progress">
                    <div class="nwp-progress__bar {{ $stats['weekly_progress']['met'] ? 'nwp-progress__bar--complete' : '' }}" style="width:{{ $stats['weekly_progress']['percentage'] }}%"></div>
                </div>
                <div class="nwp-text-sm nwp-text-muted" style="margin-top:4px;">
                    {{ number_format($stats['weekly_progress']['current']) }} / {{ number_format($stats['weekly_progress']['target']) }} words
                </div>
            </div>
        @else
            <p class="nwp-text-sm nwp-text-muted">No weekly target set.</p>
        @endif

        @if($stats['estimated_completion'])
            <div style="margin-top:16px; padding-top:14px; border-top:1px solid var(--color-border-light);">
                <span class="nwp-text-sm nwp-text-muted">Estimated completion:</span>
                <span style="font-weight:600; font-size:var(--font-size-sm);">{{ $stats['estimated_completion']->format('M d, Y') }}</span>
            </div>
        @endif
    </div>

    <!-- Set Targets -->
    <div class="nwp-card">
        <h3 style="font-size:var(--font-size-sm); font-weight:600; color:var(--color-text-muted); margin-bottom:16px;">Set Writing Targets</h3>

        <form id="daily-target-form" style="margin-bottom:16px;">
            <label class="nwp-label">Daily Target (words)</label>
            <div style="display:flex; gap:8px;">
                <input type="number" name="word_count" class="nwp-input" min="1" max="100000" placeholder="e.g. 1000" value="{{ $stats['daily_progress']['target'] ?? '' }}" style="flex:1;">
                <button type="submit" class="nwp-btn nwp-btn--sm">Set</button>
            </div>
        </form>

        <form id="weekly-target-form">
            <label class="nwp-label">Weekly Target (words)</label>
            <div style="display:flex; gap:8px;">
                <input type="number" name="word_count" class="nwp-input" min="1" max="500000" placeholder="e.g. 5000" value="{{ $stats['weekly_progress']['target'] ?? '' }}" style="flex:1;">
                <button type="submit" class="nwp-btn nwp-btn--sm">Set</button>
            </div>
        </form>

        <div style="margin-top:16px; padding-top:14px; border-top:1px solid var(--color-border-light);">
            <label class="nwp-label">Book Word Count Target</label>
            <form method="POST" action="{{ route('books.update', $book) }}" style="display:flex; gap:8px;">
                @csrf @method('PUT')
                <input type="hidden" name="title" value="{{ $book->title }}">
                <input type="number" name="target_word_count" class="nwp-input" min="0" placeholder="e.g. 80000" value="{{ $book->target_word_count }}" style="flex:1;">
                <button type="submit" class="nwp-btn nwp-btn--secondary nwp-btn--sm">Save</button>
            </form>
        </div>
    </div>
</div>

<div id="target-status" class="nwp-text-sm nwp-mb-2" style="display:none;"></div>

@push('scripts')
<script>
document.getElementById('daily-target-form').addEventListener('submit', function(e) {
    e.preventDefault();
    setTarget('daily', this.querySelector('[name=word_count]').value);
});

document.getElementById('weekly-target-form').addEventListener('submit', function(e) {
    e.preventDefault();
    setTarget('weekly', this.querySelector('[name=word_count]').value);
});

function setTarget(type, wordCount) {
    if (!wordCount || wordCount < 1) {
        alert('Please enter a valid word count.');
        return;
    }

    fetch('{{ route("targets.store", $book) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ type: type, word_count: parseInt(wordCount) }),
    })
    .then(r => { if (!r.ok) throw r; return r.json(); })
    .then(data => {
        window.location.reload();
    })
    .catch(err => {
        err.json ? err.json().then(d => alert(d.message || 'Failed.')) : alert('Failed to set target.');
    });
}
</script>
@endpush
@endsection
