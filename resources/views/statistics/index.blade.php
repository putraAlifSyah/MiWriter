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

<div class="nwp-stats">
    <div class="nwp-stat">
        <div class="nwp-stat__value">{{ number_format($stats['total_word_count']) }}</div>
        <div class="nwp-stat__label">Total Words</div>
    </div>
    <div class="nwp-stat">
        <div class="nwp-stat__value">{{ $stats['current_streak'] }}</div>
        <div class="nwp-stat__label">Current Streak (days)</div>
    </div>
    <div class="nwp-stat">
        <div class="nwp-stat__value">{{ $stats['longest_streak'] }}</div>
        <div class="nwp-stat__label">Longest Streak (days)</div>
    </div>
    <div class="nwp-stat">
        <div class="nwp-stat__value">{{ number_format($stats['average_daily']) }}</div>
        <div class="nwp-stat__label">Avg. Daily Words (30d)</div>
    </div>
</div>

@if($stats['estimated_completion'])
    <div class="nwp-card nwp-mb-3">
        <p><strong>Estimated Completion:</strong> {{ $stats['estimated_completion']->format('M d, Y') }}</p>
    </div>
@elseif($book->target_word_count)
    <div class="nwp-card nwp-mb-3">
        <p class="nwp-text-muted">Cannot estimate completion date. Set a daily target and start writing to see projections.</p>
    </div>
@endif

@if($stats['daily_progress']['has_target'] ?? false)
    <div class="nwp-card nwp-mb-2">
        <div class="nwp-label">Daily Progress</div>
        <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
            <span class="nwp-text-sm">{{ number_format($stats['daily_progress']['current']) }} / {{ number_format($stats['daily_progress']['target']) }} words</span>
            <span class="nwp-text-sm nwp-accent">{{ $stats['daily_progress']['percentage'] }}%</span>
        </div>
        <div class="nwp-progress">
            <div class="nwp-progress__bar {{ $stats['daily_progress']['met'] ? 'nwp-progress__bar--complete' : '' }}" style="width:{{ $stats['daily_progress']['percentage'] }}%"></div>
        </div>
        @if($stats['daily_progress']['met'])
            <span class="nwp-badge nwp-badge--filled nwp-mt-1">🎉 Target Met!</span>
        @endif
    </div>
@endif

@if($stats['weekly_progress']['has_target'] ?? false)
    <div class="nwp-card nwp-mb-2">
        <div class="nwp-label">Weekly Progress</div>
        <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
            <span class="nwp-text-sm">{{ number_format($stats['weekly_progress']['current']) }} / {{ number_format($stats['weekly_progress']['target']) }} words</span>
            <span class="nwp-text-sm nwp-accent">{{ $stats['weekly_progress']['percentage'] }}%</span>
        </div>
        <div class="nwp-progress">
            <div class="nwp-progress__bar {{ $stats['weekly_progress']['met'] ? 'nwp-progress__bar--complete' : '' }}" style="width:{{ $stats['weekly_progress']['percentage'] }}%"></div>
        </div>
    </div>
@endif
@endsection
